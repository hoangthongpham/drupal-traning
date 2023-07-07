<?php
namespace Drupal\module_manage_article\Model;
use Drupal\Core\Database\Driver\pgsql\Select;
use Drupal\Code\Database\Database;
use Symfony\Component\HttpFoundation\Request;


class ArticleModel{
    public function getArticleByNid($nid){
        if(isset($_GET['langcode'])){
            $langCode = $_GET['langcode'];
        }
        $connection = \Drupal::database();
        $query = $connection->select('node', 'n');
        $query->leftJoin('node_field_data', 'f', 'f.nid = n.nid');
        $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
        $query->fields('f', array('nid','title','langcode'));
        $query->fields('b', array('body_value'));
        $query->condition('n.nid',$nid, '=');
        $query->condition('f.langcode',$langCode, '=');
        $query->condition('b.langcode',$langCode, '=');
        $result = $query->execute()->fetch();
        return $result;
    }


    public function getListArticle(Request $request) {
        $connection = \Drupal::database();
        $dateFrom = isset($_GET['changed'][0])?($_GET['changed'][0]):"";
        $dateTo = isset($_GET['changed'][1])?($_GET['changed'][1]):"";
        $start = $request->get('start');
        $length = $request->get('length');
        $searchValue = isset($request->get('search')['value']) ? $request->get('search')['value'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $langCode = isset($_GET['langcode']) ? $_GET['langcode'] : '';
        
        $query = $connection->select('node', 'n');
        $query->leftJoin('node_field_data', 't', 't.nid = n.nid');
        $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
        $query->fields('n', ['nid']);
        $query->fields('t', ['title', 'changed', 'status', 'langcode']);
        $query->fields('b', ['entity_id']);
        $query->condition('n.type', 'article', '=');
        $query->orderBy('t.changed', 'desc');
        
        if ($langCode ) {
            if ($dateFrom && $dateTo) {
                $query->condition('t.changed', array(strtotime($dateFrom), strtotime($dateTo)), 'BETWEEN');
            } elseif ($dateFrom) {
                $query->condition('t.changed', strtotime($dateFrom), '>=');
            } elseif ($dateTo) {
                $query->condition('t.changed', strtotime($dateTo), '<=');
            }
            $query->condition('t.langcode', $langCode, '=');
            if($status && $status=='Active'||$status=='Active'&& $searchValue){
                $query->condition('t.status',1, '=');
                $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
            }elseif($status && $status=='InActive'|| $status=='InActive'&& $searchValue){
                $query->condition('t.status',0, '=');
                $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
            }else{
                $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
            }
              
        }
        $result = $query->range($start, $length)->distinct()->execute();
        
        $totalItemsQuery = $connection->select('node_field_data', 't')
            ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
        if ($langCode) {
            if ($dateFrom && $dateTo) {
                $totalItemsQuery->condition('t.changed', array(strtotime($dateFrom), strtotime($dateTo)), 'BETWEEN');
            } elseif ($dateFrom) {
                $totalItemsQuery->condition('t.changed', strtotime($dateFrom), '>=');
            } elseif ($dateTo) {
                $totalItemsQuery->condition('t.changed', strtotime($dateTo), '<=');
            }
            $totalItemsQuery
            ->condition('t.langcode', $langCode,'=')
            ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
            
            if($status && $status=='Active'||$status=='Active' && $searchValue){
                $totalItemsQuery
                ->condition('t.status',1, '=')
                ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
            }elseif($status && $status=='InActive'||$status=='InActive'&& $searchValue){
                $totalItemsQuery
                ->condition('t.status',0, '=')
                ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
            }
        }
        
        $totalItems = $totalItemsQuery->countQuery()->execute()->fetchField();
        return [$result, $totalItems];
    }
    
}