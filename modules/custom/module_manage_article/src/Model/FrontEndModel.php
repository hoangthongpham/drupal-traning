<?php
namespace Drupal\module_manage_article\Model;
use Drupal\Core\Database\Driver\pgsql\Select;
use Drupal\Code\Database\Database;
use Symfony\Component\HttpFoundation\Request;


class FrontEndModel{
    public function getDataHome(Request $request) {
        if(isset($_GET['langcode'])){
            $langCode = $_GET['langcode'];
        }
        $page = $request->query->get('page', 0);
        $limit =5;
        $query = \Drupal::entityQuery('node')
            ->condition('type', 'article')
            ->condition('langcode', $langCode)
            ->notExists('field_featured')
            ->pager($limit)
            ->sort('changed', 'DESC');
        $offset = $page * $limit;
        $query->range($offset, $limit);
        $nids = $query->execute();
        
        $nodes = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadMultiple($nids);
        $totalCount = \Drupal::entityQuery('node')
        ->condition('type', 'article')
        ->condition('langcode', $langCode)
        ->notExists('field_featured')
        ->count()
        ->execute();
        $totalPages = ceil($totalCount / $limit);
        return [
            $nodes,
            $totalPages
        ];
    }
    
    public function getListArt(Request $request) {
        if(isset($_GET['langcode'])){
            $langCode = $_GET['langcode'];
        }
        $page = $request->query->get('page', 0);
        $limit = 4;
        $query = \Drupal::entityQuery('node')
            ->condition('type', 'article')
            ->condition('langcode', $langCode)
            ->pager($limit)
            ->sort('changed', 'DESC');
        $offset = $page * $limit;
        $query->range($offset, $limit);
        $nids = $query->execute();
        
        $nodes = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadMultiple($nids);
        $totalCount = \Drupal::entityQuery('node')
        ->condition('type', 'article')
        ->condition('langcode', $langCode)
        ->count()
        ->execute();
        $totalPages = ceil($totalCount / $limit);
        return [
            $nodes,
            $totalPages
        ];
    }

    public function getArticleSlide($langCode){
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'article');
        $query->condition('langcode', $langCode);
        $query->condition('field_featured.value', 1);
        $query->sort('created', 'DESC');
        $query->range(0, 5);
        $result = $query->execute();
        return $result;
    }

    public function getArticleDetail($id,$langCode,$name_tag){
        $query = \Drupal::entityQuery('node')
        ->condition('nid', $id,'<>')
        ->condition('type', 'article') 
        ->condition('status', 1)
        ->condition('langcode', $langCode)
        ->condition('field_tags.entity.name', $name_tag);
        $nids = $query->execute();
        return $nids;
    }

    public function getArticleSearch($langCode){
        if(isset($_GET['keyword'])){
            $keyword = $_GET['keyword'];
        }
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'article');
        $query->condition('langcode', $langCode);
        $query->condition('title', '%' . $keyword . '%', 'LIKE');
        $result = $query->execute();
        return $result;
    }

    public function getArticleByTag($langCode){
        $tag = \Drupal::routeMatch()->getParameter('tag');
        $query = \Drupal::entityQuery('node')
            ->condition('type', 'article') 
            ->condition('status', 1)
            ->condition('langcode', $langCode)
            ->condition('field_tags.entity.name', $tag);
        $nids = $query->execute();
        return  $nids;
    }
}