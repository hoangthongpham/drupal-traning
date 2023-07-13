<?php

namespace Drupal\module_manage_article\Controller;

use Symfony\Component\BrowserKit\Response;
use Drupal\module_manage_article\Model\ArticleModel;
use \Drupal\file\Entity\File;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Query\Merge;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;



class ArticleController extends ControllerBase {

    public function load(){
        $langCode= \Drupal::languageManager()->getCurrentLanguage()->getId();
        $languages = \Drupal::languageManager()->getLanguages();
        return [
            '#theme' => 'module_manage_article',
            '#data' => [
                $languages,
                $langCode
            ],
            '#attached' => [
               'library' => ['module_manage_article/datatable_asset'],
            ],
        ];
    }
    public function getList(Request $request) {
        $Mdl = new ArticleModel();
        $result = $Mdl->getListArticle($request);
        $data = [];
        foreach ($result[0] as $key => $row) {
            $data[] = [
                'nid'  => $row->nid,
                'title'      => $row->title,
                'body_value' => $row->body_value,
                'status' => $row->status,
                'changed' => $row->changed,
                'langcode' => $row->langcode,
            ];
        }
        $response = new JsonResponse([
            'recordsTotal'    => intval($result[1]),
            'recordsFiltered' => intval($result[1]),
            'data'            => $data,  
        ]);
    
        return 
            $response;
            
    }
   
    public function deleteArticle(){
        $nid = \Drupal::routeMatch()->getParameter('id');
        $node = \Drupal\node\Entity\Node::load($nid);
        if ($node) {
            if ($node->hasField('field_featured')) {
                $field_featured = $node->get('field_featured')->first();
                if ($field_featured) {
                    $field_featured->delete();
                }
            }

       
            if ($node->get('field_image')->target_id) {
                $file = \Drupal\file\Entity\File::load($node->get('field_image')->target_id);
                if ($file) {
                    $file->delete();
                }
            }
            $node->delete();

            \Drupal::messenger()->addStatus($this->t('Article deleted successfully!'), 'success', TRUE);
            return new \Symfony\Component\HttpFoundation\RedirectResponse('/admin/articles');
        }

        \Drupal::messenger()->addError($this->t('Article not found.'), 'error', TRUE);
        return new \Symfony\Component\HttpFoundation\RedirectResponse('/admin/articles');
    }



    public function showArticle(){
        $nid  = \Drupal::routeMatch()->getParameter('id');
        $Mdl  = new ArticleModel();
        $data = $Mdl->getArticleByNid($nid);
        $node = \Drupal\node\Entity\Node::load($nid);
        $url  = null;
        if ($node->get('field_image')->target_id) {
            $file = File::load($node->get('field_image')->target_id);
            $url  = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        };

        return new JsonResponse([
            'data' => $data,
            'url'  => $url
        ]);
    }

    public function updateArticle(Request $request){
        try {
            if(isset($_GET['langcode'])){
                $langCode = $_GET['langcode'];
            }
           
            $nid = $request->get('nid');
            $title = $request->get('title');
            $body = $request->get('body_value');
            $node = \Drupal\node\Entity\Node::load($nid);
            if($node->hasTranslation($langCode)) {
                $node = $node->getTranslation($langCode);
            }
            $node->title =  $title;
            $node->body =  $body;
            $node->save();
            return new Response('success');
        } catch (Exception $e) {
            return new Response('fail');
        }
    }

}


    


