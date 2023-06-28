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
        return [
            '#theme' => 'module_manage_article',
            '#attached' => [
               'library' => ['module_manage_article/datatable_asset']
           ],
        ];
    }
    public function getList(Request $request) {
        $langcode= \Drupal::languageManager()->getCurrentLanguage()->getId();
        $Mdl = new ArticleModel();
        $result = $Mdl->getListArticle($request);
        $data = [];
        foreach ($result[0] as $key => $row) {
            $data[] = [
                'nid'  => $row->nid,
                'title'      => $row->title,
                'body_value' => $row->body_value,
                'status' => $row->status,
                'langcode'=>$langcode,
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
   
    public function deleteArticle()
    {
        $nid  = \Drupal::routeMatch()->getParameter('id');
        $node = \Drupal\node\Entity\Node::load($nid);
        if ($node->get('field_image')->target_id) {
            $file = File::load($node->get('field_image')->target_id);
            $file->delete();
        }
        if ($node) {
            $node->delete();
        }
        $termId = $node->get('field_tags')->target_id;
        if ($term = \Drupal\taxonomy\Entity\Term::load($termId)) {
            $term->delete();
        }
        \Drupal::messenger()->addStatus($this->t('Article delete successfully!'), 'success', TRUE);
        return
            $response = new \Symfony\Component\HttpFoundation\RedirectResponse('/admin/articles');
        $response->send();

    }

    public function showArticle(){
        $nid = \Drupal::routeMatch()->getParameter('id');
        $Mdl = new ArticleModel();
        $data = $Mdl->getArticleByNid($nid);
        $node = \Drupal\node\Entity\Node::load($nid);
        if($node->get('field_image')->target_id){
            $file = File::load($node->get('field_image')->target_id);
            // $url =  $file->uri->value;
            $url =  $file->filename->value;
        }
        $response = new JsonResponse([     
            'data'            => $data,
            'url' => $url
        ]);
        return 
            $response;
    }

    public function updateArticle(Request $request){
        $nid = $request->get('nid');
        $title = $request->get('title');
        $body = $request->get('body_value');
        $node = \Drupal\node\Entity\Node::load($nid);
        $node->title =  $title;
        $node->body =  $body;
        $node->save();
        if($node){
            $response = new Response('success');
            return $response;
        }else {
            $response = new Response('fail');
            return  $response;
        }
        
    }

}


    


