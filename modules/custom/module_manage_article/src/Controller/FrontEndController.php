<?php

namespace Drupal\module_manage_article\Controller;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Drupal\module_manage_article\Model\FrontEndModel;
    use Drupal\Core\Controller\ControllerBase;
    use Drupal\file\Entity\File;
    use Drupal\Core\Messenger\MessengerInterface;
    use Symfony\Component\DependencyInjection\ContainerInterface;
class FrontEndController extends ControllerBase {

    public function loadArticle(){
        $langCode= \Drupal::languageManager()->getCurrentLanguage()->getId();
        return [
            '#theme' => 'module_manage_article_page',
            '#attached' => [
                'library' => ['module_manage_article/datatable_asset'],
                'drupalSettings' => [
                    'langCode' => $langCode,
                ],
            ],
        ];
    }

    public function listArticles(Request $request) {
        if(isset($_GET['langcode'])){
            $langCode = $_GET['langcode'];
        }

        $Mdl = new FrontEndModel();
        $result = $Mdl->getListArt($request);
        $content = [];
        foreach ($result[0] as $node) {
            $translatedNode = $node->getTranslation($langCode);
            $image = $node->get('field_image')->entity;
            $imageUrl = '';
            if ($image instanceof File) {
                $imageUrl = file_create_url($image->getFileUri());
            }

            $content[] = [
                'nid' => $node->id(),
                'title' => $translatedNode->getTitle(),
                'body' => $translatedNode->get('body')->value,
                'image_url' => $imageUrl,
            ];
        }

        return new JsonResponse([
            'content' => $content,
            'pages' => $result[1],
        ]);
    }


    public function detailArticle() {
        $langCode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $nid = \Drupal::routeMatch()->getParameter('id');
        $node = \Drupal\node\Entity\Node::load($nid);
        
        if ($node && $node->hasTranslation($langCode)) {
            $translatedNode = $node->getTranslation($langCode);
    
            return [
                '#theme' => 'module_manage_article_detail',
                '#article' => $translatedNode,
            ];
        } else {
            \Drupal::messenger()->addError('Node not found');
            return new RedirectResponse('/');
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('messenger')
        );
    }
    
    

}


