<?php
namespace Drupal\module_manage_article\Controller;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\Entity\Node;
use \Drupal\file\Entity\File;
class ArticleController {
  public function load(){
    $limit = 5;
    $connection = \Drupal::database();
    $query = $connection->select('node', 'n');
    $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
    $query->leftJoin('node_field_data', 't', 't.nid = n.nid');
    $query->fields('n', array('nid'));
    $query->fields('t', array('title'));
    $query->fields('b', array('body_value','entity_id'));
    $query->condition('n.type', 'article', '=');
    $query->orderBy('nid', 'ASC');
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit($limit);
    $result = $pager->execute()->fetchAll();

  
    $data = [];
    $count=0;
    $params =\Drupal::request()->query->all();
    if(empty($params) || $params['page']==0){
        $count = 1;
    }else if($params['page']==1){
        $count = $params['page']+$limit;
    }else {
        $count = $params['page']*$limit;
        $count++;
    }
    foreach ($result as $row) {
        $data[] = [
            'serial_no' => $count.'.',
            'title' => $row->title,
            'body_value' => $row->body_value,
            'edit'=>t("<a href ='/admin/article/edit/$row->nid'>Edit</a>"),
            'delete'=>t("<a href ='/admin/article/delete/$row->nid'>Delete</a>")
        ];
        $count++;
    }
    
    $header = ['#','Title', 'Body','Edit','Delete'];
    $build['create_link'] = [
      '#markup' => '<a href="../admin/article/add">add article</a>',
    ];
  
    $build['table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $data,
    ];
    $build['pager'] =[
        '#type' => 'pager'
    ];
    
    return [
        $build,
    ];
  }

  public function addArticles() {
    $form = \Drupal::formBuilder()->getForm('Drupal\module_manage_article\Form\ArticleForm');
    return [
        'data' => $form,
    ];
}

  public function editArticles() {
    $form = \Drupal::formBuilder()->getForm('Drupal\module_manage_article\Form\EditArticleForm');
    return [
      'data' => $form,
  ];
  }
  public function deleteArticles() {
    $nid = \Drupal::routeMatch()->getParameter('id');
    $node = \Drupal\node\Entity\Node::load($nid);
    if($node->get('field_image')->target_id){
      $file = File::load($node->get('field_image')->target_id);
      $file->delete();
    }
    if ($node) {
      $node->delete();
    }
    \Drupal::messenger()->addStatus(t('Article delete successfully!'), 'success',TRUE);
    return 
      $response  = new \Symfony\Component\HttpFoundation\RedirectResponse('/admin/articles');
      $response->send();
    ;

  }


}