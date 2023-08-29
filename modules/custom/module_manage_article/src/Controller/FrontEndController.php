<?php

namespace Drupal\module_manage_article\Controller;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Drupal\module_manage_article\Model\FrontEndModel;
    use Drupal\Core\Controller\ControllerBase;
    use Drupal\file\Entity\File;
    use Drupal\taxonomy\Entity\Term;
    use Symfony\Component\HttpFoundation\Response;
    use Drupal\Component\Utility\Html;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
class FrontEndController extends ControllerBase {
    public function langCode() {
        $langCode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        return $langCode;
    }
    public function loadHome(){
        $langCode= \Drupal::languageManager()->getCurrentLanguage()->getId();
        return [
            '#theme' => 'module_manage_article_home',
            '#attached' => [
                'library' => ['module_manage_article/datatable_asset']
            ],
            '#articles' => [
                'langCode' => $langCode,
            ],
        ];
    }

    public function homeData(Request $request) {
        if(isset($_GET['langcode'])){
            $langCode = $_GET['langcode'];
        }
        $Mdl = new FrontEndModel();
        $result = $Mdl->getDataHome($request);
        $content = [];
        foreach ($result[0] as $node) {
            $name_tag='';
            if($node->get('field_tags')->target_id){
                $termId = $node->get('field_tags')->target_id;
                $term = Term::load($termId);
                if($term){
                    $name_tag = $term->name->value;
                }else{
                    $name_tag='';
                }
                
            }
            $translatedNode = $node->getTranslation($langCode);
            $image = $node->get('field_image')->entity;
            $imageUrl = '';
            if ($image instanceof File) {
                $imageUrl = file_create_url($image->getFileUri());
            }
            $author = $node->getOwner()->getDisplayName();
            $changed = $node->getChangedTime();

            $content[] = [
                'nid' => $node->id(),
                'title' => $translatedNode->getTitle(),
                'body' => $translatedNode->get('body')->value,
                'image_url' => $imageUrl,
                'tag'=>$name_tag,
                'author' => $author,
                'changed' => $changed,
            ];
        }

        return new JsonResponse([
            'content' => $content,
            'pages' => $result[1],
        ]);
    }

    public function loadArticle(){
        $langCode= \Drupal::languageManager()->getCurrentLanguage()->getId();
        return [
            '#theme' => 'module_manage_article_list',
            '#attached' => [
                'library' => ['module_manage_article/datatable_asset'],
                'drupalSettings' => [
                    'langCode' => $langCode,
                ],
            ],
            '#articles' => [
                'langCode' => $langCode,
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
            $name_tag='';
            if($node->get('field_tags')->target_id){
                $termId = $node->get('field_tags')->target_id;
                $term = Term::load($termId);
                if($term){
                    $name_tag = $term->name->value;
                }else{
                    $name_tag='';
                }
                
            }
            $translatedNode = $node->getTranslation($langCode);
            $image = $node->get('field_image')->entity;
            $imageUrl = '';
            if ($image instanceof File) {
                $imageUrl = file_create_url($image->getFileUri());
            }
            $author = $node->getOwner()->getDisplayName();
            $changed = $node->getChangedTime();

            $content[] = [
                'nid' => $node->id(),
                'title' => $translatedNode->getTitle(),
                'body' => $translatedNode->get('body')->value,
                'image_url' => $imageUrl,
                'tag'=>$name_tag,
                'author' => $author,
                'changed' => $changed,
            ];
        }

        return new JsonResponse([
            'content' => $content,
            'pages' => $result[1],
        ]);
    }

    public function detailArticle() {
        $langCode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $id = \Drupal::routeMatch()->getParameter('id');
        $node = \Drupal\node\Entity\Node::load($id);
        $name_tag='';
        if($node->get('field_tags')->target_id){
            $termId = $node->get('field_tags')->target_id;
            $term = Term::load($termId);
            if($term){
                $name_tag = $term->name->value;
            }else{
                $name_tag='';
            }
            
        }
        if($node && $node->hasTranslation($langCode)) {
            $transNode = $node->getTranslation($langCode);
            $author = $node->getOwner()->getDisplayName();
            $changed = $node->getChangedTime();
            $data[]=[
                $transNode,
                $name_tag,
                $author,
                $changed,
            ];
        }else {
            return [
              '#type' => 'markup',
              '#markup' => $this->t('No results found'),
            ];
        }
        $Mdl = new FrontEndModel();
        $result = $Mdl->getArticleDetail($id,$langCode,$name_tag);
        $list = [];
        foreach ($result as $nid) {
            $art = \Drupal\node\Entity\Node::load($nid);
            $author = $art->getOwner()->getDisplayName();
            $changed = $art->getChangedTime();
            if ($art && $art->hasTranslation($langCode)) {
                $translatedNode = $art->getTranslation($langCode);
            }
            if ($translatedNode) {
                $list[] = [
                    $translatedNode,
                    $author,
                    $changed
                ];
            }
        }
        return [
            '#theme' => 'module_manage_article_detail',
            '#article' =>[
                $data,
                $list,
                $langCode
            ],
            '#attached' => [
                'drupalSettings' => [
                    'langCode' => $langCode,
                ],
            ],
        ]; 
    }

    function searchPage() {
        $langCode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $Mdl = new FrontEndModel();
        $result = $Mdl-> getArticleSearch($langCode);
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($result);
        $data = [];
        foreach ($nodes as $node) {
            $image = $node->get('field_image')->entity;
            $imageUrl = '';
            if ($image instanceof File) {
                $imageUrl = file_create_url($image->getFileUri());
            }
            if($node && $node->hasTranslation($langCode)) {
                $transNode = $node->getTranslation($langCode);
                $author = $node->getOwner()->getDisplayName();
                $changed = $node->getChangedTime();
                $data[] = [
                    'nid' => $node->id(),
                    'title' => $transNode->getTitle(),
                    'body' => $transNode->get('body')->value,
                    'image_url' => $imageUrl,
                    'author' => $author,
                    'changed' => $changed,
                    'langcode' => $langCode,
                    
                ];
            }else{
                return [
                    '#type' => 'markup',
                    '#markup' => $this->t('No results found'),
                ];
            } 
        }
        \Drupal::service('page_cache_kill_switch')->trigger();
        return [
            '#theme' => 'module_manage_article_search',
            '#articles' => $data,
        ];
    }

    public function articleTag() {
        $vocabularyName = 'tags';
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabularyName);
    
        $tagList = [];
        $tagNames = [];
    
        foreach ($terms as $term) {
            $tid = $term->tid;
            $name = $term->name;
            
            if (!in_array($name, $tagNames)) {
                $tagNames[] = $name;
                $tagList[$tid] = $name;
            }
        }
        return $tagList;
    }

    public function articleByTag() {
        $langCode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $Mdl = new FrontEndModel();
        $result = $Mdl->getArticleByTag($langCode);
        $articles = [];
        foreach ($result as $nid) {
            $node = \Drupal\node\Entity\Node::load($nid);
            $author = $node->getOwner()->getDisplayName();
            $changed = $node->getChangedTime();
            if ($node && $node->hasTranslation($langCode)) {
                $translatedNode = $node->getTranslation($langCode);
            }
            if ($translatedNode) {
                $articles[] = [
                    $translatedNode,
                    $author,
                    $changed
                ];
            }
        }
        return [
            '#theme' => 'module_manage_article_tag',
            '#articles' => $articles,
        ];
    }
    

    function slide() {
        $langCode= \Drupal::languageManager()->getCurrentLanguage()->getId();
        $Mdl = new FrontEndModel();
        $result = $Mdl->getArticleSlide($langCode);
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($result);
        $data = [];
        foreach ($nodes as $node) {
            $image = $node->get('field_image')->entity;
            $imageUrl = '';
            if ($image instanceof File) {
                $imageUrl = file_create_url($image->getFileUri());
            }
            if ($node && $node->hasTranslation($langCode)) {
                $translatedNode = $node->getTranslation($langCode);
            }
            $author = $node->getOwner()->getDisplayName();
            $changed = $node->getChangedTime();
            $fieldFeatured = '';
            if ($node->hasField('field_featured')) {
                $fieldFeaturedItems = $node->get('field_featured')->getValue();
                if (!empty($fieldFeaturedItems)) {
                    $fieldFeatured = $fieldFeaturedItems[0]['value'];
                }
            }
            $data[] = [
                'nid' => $node->id(),
                'title' => $translatedNode->getTitle(),
                'image_url' => $imageUrl,
                'author' => $author,
                'changed' => $changed,
                'field_featured'=> $fieldFeatured
            ];
        }
        return $data;
    }  

    public function contactForm(){
        return [
            '#theme' => 'module_manage_article_contact',
        ];
    }

    public function submitContactForm(){
        if (isset($_POST['first_name'])) {
            $firstName = $_POST['first_name'];
        }
        if (isset($_POST['last_name'])) {
            $lastName = $_POST['last_name'];
        }
        if (isset($_POST['subject'])) {
            $subject = $_POST['subject'];
        }
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nguongthientieu8196@gmail.com';
        $mail->Password = 'ppcqsqcwpgdtregu';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('nguongthientieu8196@gmail.com');
        $mail->addAddress('nguongthientieu8196@gmail.com');

        $mail->Subject = 'hello';
        $mail->Body = "First Name: " . Html::escape($firstName) . "\nLast Name: " . Html::escape($lastName) . "\nSubject: " . Html::escape($subject);
        try {
            $mail->send();
            $this->messenger()->addMessage($this->t('Form submitted successfully.'));
        } catch (Exception $e) {
            $this->messenger()->addError($this->t('An error occurred while sending the form.'));
        }
        return $this->redirect('module_manage_article.contact');
    }

}


