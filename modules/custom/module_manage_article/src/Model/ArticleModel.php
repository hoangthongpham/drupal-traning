<?php
namespace Drupal\module_manage_article\Model;
use Drupal\Core\Database\Driver\pgsql\Select;
use Drupal\Code\Database\Database;
use Symfony\Component\HttpFoundation\Request;


class ArticleModel{
    function getListArticle(Request $request) {
        $connection = \Drupal::database();
        $start = $request->get('start');
        $length = $request->get('length');
        if(isset($request->get('search')['value'])){
            $searchValue = $request->get('search')['value'];
        }
        if(isset($_GET['status'])){
            $status = $_GET['status'];
        }
        if(isset($_GET['langcode'])){
            $langCode = $_GET['langcode'];
        }
      
        $query = $connection->select('node', 'n');
        $query->leftJoin('node_field_data', 't', 't.nid = n.nid');
        $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
        $query->fields('n', array('nid'));
        $query->fields('t', array('title','changed','status','langcode'));
        $query->fields('b', array('entity_id'));
        $query->condition('n.type', 'article', '=');
        $query->orderBy('t.changed', 'desc');
  
        if($langCode && $langCode =='English'){
            $query->condition('t.langcode', 'en', '=');
            if($status && $status=='Active'||$status=='Active'&& $searchValue){
                $query->condition('t.status',1, '=');
                $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
            }elseif($status && $status=='InActive'|| $status=='InActive'&& $searchValue){
                $query->condition('t.status',0, '=');
                $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
            }else{
                $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
            }
        }elseif($langCode && $langCode =='Vietnamese') {
            $query->condition('t.langcode', 'vi', '=');
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
        $result= $query->range($start, $length)->distinct()->execute();
        if($langCode && $langCode =='Vietnamese'){
            $totalItems= $connection->select('node_field_data', 't')
            ->condition('t.langcode','vi', '=')
            ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE')
            ->countQuery()
            ->execute()
            ->fetchField();
            if($status && $status=='Active'||$status=='Active' && $searchValue){
                $totalItems= $connection->select('node_field_data', 't')
                ->condition('t.langcode','vi', '=')
                ->condition('t.status',1, '=')
                ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE')
                ->countQuery()
                ->execute()
                ->fetchField();
            }elseif($status && $status=='InActive'||$status=='InActive'&& $searchValue){
                $totalItems= $connection->select('node_field_data', 't')
                ->condition('t.langcode','vi', '=')
                ->condition('t.status',0, '=')
                ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE')
                ->countQuery()
                ->execute()
                ->fetchField();
            }
        }elseif($langCode && $langCode =='English'){
            $totalItems= $connection->select('node_field_data', 't')
            ->condition('t.langcode','en', '=')
            ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE')
            ->countQuery()
            ->execute()
            ->fetchField();
            if($status && $status=='Active'||$status=='Active' && $searchValue){
                $totalItems= $connection->select('node_field_data', 't')
                ->condition('t.langcode','en', '=')
                ->condition('t.status',1, '=')
                ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE')
                ->countQuery()
                ->execute()
                ->fetchField();
            }elseif($status && $status=='InActive'||$status=='InActive'&& $searchValue){
                $totalItems= $connection->select('node_field_data', 't')
                ->condition('t.langcode','en', '=')
                ->condition('t.status',0, '=')
                ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE')
                ->countQuery()
                ->execute()
                ->fetchField();
            }
        }
        
        return[
            $result,$totalItems
        ];
    }
    // public function getArticleByNid($nid){
    //         $connection = \Drupal::database();
    //         $query = $connection->select('node', 'n');
    //         $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
    //         $query->leftJoin('node_field_data', 't', 't.nid = n.nid');
    //         $query->fields('t', array('nid','title','langcode'));
    //         $query->fields('b', array('body_value'));
    //         $query->condition('n.nid',$nid, '=');
    //         $result = $query->execute()->fetch();
    //     return $result;
    // }

     public function getArticleByNid($nid){
            if(isset($_GET['langcode'])){
                $langCode = $_GET['langcode'];
            }
            if($langCode=='English'){
                $lang='en';
            }else{
                $lang='vi';
            }
            $connection = \Drupal::database();
            $query = $connection->select('node', 'n');
            $query->leftJoin('node_field_data', 'f', 'f.nid = n.nid');
            $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
            $query->fields('f', array('nid','title','langcode'));
            $query->fields('b', array('body_value'));
            $query->condition('n.nid',$nid, '=');
            $query->condition('f.langcode',$lang, '=');
            $query->condition('b.langcode',$lang, '=');
            $result = $query->execute()->fetch();
        return $result;
    }


    // public function getListArticle(Request $request) {
    //     $connection = \Drupal::database();
    //     $start = $request->get('start');
    //     $length = $request->get('length');
    //     $searchValue = isset($request->get('search')['value']) ? $request->get('search')['value'] : '';
    //     $status = isset($_GET['status']) ? $_GET['status'] : '';
    //     $langCode = isset($_GET['langcode']) ? $_GET['langcode'] : '';
        
    //     $query = $connection->select('node', 'n');
    //     $query->leftJoin('node_field_data', 't', 't.nid = n.nid');
    //     $query->leftJoin('node__body', 'b', 'b.entity_id = n.nid');
    //     $query->fields('n', ['nid']);
    //     $query->fields('t', ['title', 'changed', 'status', 'langcode']);
    //     $query->fields('b', ['entity_id']);
    //     $query->condition('n.type', 'article', '=');
    //     $query->orderBy('t.changed', 'desc');
    
    //     if ($langCode && ($langCode == 'English' || $langCode == 'Vietnamese')) {
    //         $query->condition('t.langcode', $langCode == 'English' ? 'en' : 'vi', '=');
    //         if($status && $status=='Active'||$status=='Active'&& $searchValue){
    //             $query->condition('t.status',1, '=');
    //             $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
    //         }elseif($status && $status=='InActive'|| $status=='InActive'&& $searchValue){
    //             $query->condition('t.status',0, '=');
    //             $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
    //         }else{
    //             $query->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
    //         }
              
    //     }
        
    //     $result = $query->range($start, $length)->distinct()->execute();
        
    //     $totalItemsQuery = $connection->select('node_field_data', 't')
    //         ->condition('t.title', '%' . $connection->escapeLike($searchValue) . '%', 'LIKE');
        
    //     if ($langCode && ($langCode == 'English' || $langCode == 'Vietnamese')) {
    //         $totalItemsQuery->condition('t.langcode', $langCode == 'English' ? 'en' : 'vi', '=');
            
    //         if (($status == 'Active' || $status == 'InActive') && $searchValue) {
    //             $totalItemsQuery->condition('t.status', $status == 'Active' ? 1 : 0, '=');
    //         }
    //     }
        
    //     $totalItems = $totalItemsQuery->countQuery()->execute()->fetchField();
        
    //     return [$result, $totalItems];
    // }
    
}