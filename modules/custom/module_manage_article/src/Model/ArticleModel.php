<?php
namespace Drupal\module_manage_article\Model;
use Drupal\Core\Database\Driver\pgsql\Select;
use Drupal\Code\Database\Database;
use Symfony\Component\HttpFoundation\Request;


class ArticleModel{
    function getListArticle(Request $request) {
        $connection = \Drupal::database();
        $totalItems = $connection->select('node_field_data', 'n')
        ->condition('n.type', 'article', '=','t.title')
        ->countQuery()
        ->execute()
        ->fetchField();
        $start = $request->get('start');
        $length = $request->get('length');
        if(isset($request->get('search')['value'])){
            $searchValue = $request->get('search')['value'];
        }
        if(isset($request->get('order')[0]['dir'])){
            $orderDir = $request->get('order')[0]['dir'];
        }
        $query = $connection->select('node_field_data', 'n');
        $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
        $query->leftJoin('node_field_data', 't', 't.nid = n.nid');
        $query->fields('n', array('nid','status'));
        $query->fields('t', array('title'));
        $query->fields('b', array('body_value','entity_id'));
        $query->condition('n.type', 'article', '=');
        if(!empty($orderDir)){
            $query->orderBy('t.title', $orderDir);
        }
        if (!empty($searchValue)) {
            $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
        }
        $result= $query->range($start, $length)->execute();
        if(isset($request->get('search')['value'])){
            $searchValue = $request->get('search')['value'];
            $totalItems= $connection->select('node_field_data', 'n')
            ->condition('n.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE')
            ->countQuery()
            ->execute()
            ->fetchField();
        }
        
        return[
            $result,$totalItems
        ];
    }
    public function getArticleByNid($nid){
        $connection = \Drupal::database();
        $query = $connection->select('node_field_data', 'n');
        $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
        $query->leftJoin('node_field_data', 't', 't.nid = n.nid');
        $query->fields('t', array('nid','title'));
        $query->fields('b', array('body_value'));
        $query->condition('n.nid',$nid, '=');
        $result = $query->execute()->fetch();
        return $result;

    }

}