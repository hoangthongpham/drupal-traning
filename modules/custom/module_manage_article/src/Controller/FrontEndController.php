<?php

namespace Drupal\module_manage_article\Controller;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Drupal\module_manage_article\Model\FrontEndModel;
    use Drupal\Core\Controller\ControllerBase;
    use Drupal\file\Entity\File;
    use Drupal\taxonomy\Entity\Term;
    use Drupal\Core\Database\Database;
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
                'library' => ['module_manage_article/datatable_asset'],
                'drupalSettings' => [
                    'langCode' => $langCode,
                ],
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
        $transNode = $node->getTranslation($langCode);
        $author = $node->getOwner()->getDisplayName();
        $changed = $node->getChangedTime();
        if($node && $node->hasTranslation($langCode)) {
            $transNode = $node->getTranslation($langCode);
        }else {
            \Drupal::messenger()->addStatus(t('Bài viết này không có tiếng việt!'), 'status',TRUE);
            exit();
        }
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
        if($transNode){
            $data[]=[
                $transNode,
                $name_tag,
                $author,
                $changed,
            ];
        }
        $query = \Drupal::entityQuery('node')
        ->condition('nid', $id,'<>')
        ->condition('type', 'article') 
        ->condition('status', 1)
        ->condition('langcode', $langCode)
        ->condition('field_tags.entity.name', $name_tag);
        $nids = $query->execute();
        $list = [];
        foreach ($nids as $nid) {
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
                $list
            ]
        ]; 
    }

    // function loadSearch(){
    //     $langCode= \Drupal::languageManager()->getCurrentLanguage()->getId();
    //     return [
    //         '#theme' => 'module_manage_article_search',
    //         '#attached' => [
    //             'library' => ['module_manage_article/datatable_asset'],
    //             'drupalSettings' => [
    //                 'langCode' => $langCode,
    //             ],
    //         ],
    //     ];
    // }
    function searchPage() {
        if(isset($_GET['keyword'])){
            $keyword = $_GET['keyword'];
        }
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'article');
        $query->condition('title', '%' . $keyword . '%', 'LIKE');
        $result = $query->execute();
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($result);
        $data = [];
        foreach ($nodes as $node) {
            $image = $node->get('field_image')->entity;
            $imageUrl = '';
            if ($image instanceof File) {
                $imageUrl = file_create_url($image->getFileUri());
            }

            $data[] = [
                'nid' => $node->id(),
                'title' => $node->getTitle(),
                'body' => $node->get('body')->value,
                'image_url' => $imageUrl,
            ];
        }
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
        $tag = \Drupal::routeMatch()->getParameter('tag');
        $query = \Drupal::entityQuery('node')
            ->condition('type', 'article') 
            ->condition('status', 1)
            ->condition('langcode', $langCode)
            ->condition('field_tags.entity.name', $tag);
        $nids = $query->execute();
        
        $articles = [];
        foreach ($nids as $nid) {
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
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'article');
        $query->condition('field_featured.value', 1);
        $query->sort('created', 'DESC');
        $query->range(0, 5);
        $result = $query->execute();
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($result);
        $data = [];
        foreach ($nodes as $node) {
            $image = $node->get('field_image')->entity;
            $imageUrl = '';
            if ($image instanceof File) {
                $imageUrl = file_create_url($image->getFileUri());
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
                'title' => $node->getTitle(),
                'image_url' => $imageUrl,
                'author' => $author,
                'changed' => $changed,
                'field_featured'=> $fieldFeatured
            ];
        }
        return $data;
    }  
}


