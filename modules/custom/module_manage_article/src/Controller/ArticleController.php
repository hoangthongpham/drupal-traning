<?php

namespace Drupal\module_manage_article\Controller;


use Drupal\module_manage_article\Model\ArticleModel;
use \Drupal\file\Entity\File;

use Drupal\Core\Controller\ControllerBase;

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
        $Mdl = new ArticleModel();
        $result = $Mdl->getListArticle($request);
        $data = [];
        foreach ($result[0] as $key => $row) {
            $data[] = [
                'serial_no'  => $key + 1 . '.',
                'title'      => $row->title,
                'body_value' => $row->body_value,
                'edit'       => "<a href='/admin/article/edit/{$row->nid}'>" . $this->t('Edit') . "</a>",
                'delete'     => "<a class='delete_item' data-id='{$row->nid}' href='/admin/article/delete/{$row->nid}'>" . $this->t('Delete') . "</a>"
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
    // public function addArticles()
    // {
    //     $form = \Drupal::formBuilder()->getForm('Drupal\module_manage_article\Form\ArticleForm');
    //     return [
    //         'data' => $form,
    //     ];
    // }

    // public function editArticles()
    // {
    //     $form = \Drupal::formBuilder()->getForm('Drupal\module_manage_article\Form\EditArticleForm');
    //     return [
    //         'data' => $form,
    //     ];
    // }

    public function deleteArticles()
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
        \Drupal::messenger()->addStatus(t('Article delete successfully!'), 'success', TRUE);
        return
            $response = new \Symfony\Component\HttpFoundation\RedirectResponse('/admin/articles');
        $response->send();;

    }
  
}


    


