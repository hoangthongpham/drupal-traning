<?php
namespace Drupal\module_manage_article\Controller;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
class ArticleController {
  public function load(){
    $limit = 10;
    $connection = \Drupal::database();
    $query = $connection->select('node', 'n');
    $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
    $query->leftJoin('node_field_data', 't', 't.nid = n.nid');
    $query->fields('t', array('title'));
    $query->fields('b', array('body_value','entity_id'));
    $query->condition('n.type', 'article', '=');
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
            'edit'=>t("<a href ='/admin/article/edit/$row->entity_id'>Edit</a>"),
            'delete'=>t("<a href ='/admin/article/delete/$row->entity_id'>Delete</a>")
        ];
        $count++;
    }
    
    $header = ['#','Title', 'Body','Edit','Delete'];
    
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
    return array(
      '#markup' => 'Welcome to articles.'
    );
  }
  public function deleteArticles() {
    return array(
      '#markup' => 'Welcome to articles.'
    );
  }

//   public function deleteArticles($id){
//     $query = \Drupal::database();
//     $query->delete('table')
//         ->condition('id', $id,'=')
//         ->execute();
//     $url = Url::fromRoute('module_manage_article.listArticles');
//     $response  = new \Symfony\Component\HttpFoundation\RedirectResponse($url->toString());
//     $response->send();
//     \Drupal::messenger()->addStatus(t('Article delete successfully!'), 'success',TRUE);
// }
}