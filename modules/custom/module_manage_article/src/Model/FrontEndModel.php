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

    public function getArticleByTag(){

    }

    // public function search(Request $request) {
    //     if(isset($_GET['langcode'])){
    //         $langCode = $_GET['langcode'];
    //     }
    //     if(isset($_GET['keyword'])){
    //         $keyword = $_GET['keyword'];
    //     }
    //     $page = $request->query->get('page', 0);
    //     $limit = 1;
    //     $query = \Drupal::entityQuery('node')
    //         ->condition('type', 'article')
    //         ->condition('title', '%' . $keyword . '%', 'LIKE')
    //         ->condition('langcode', $langCode)
    //         ->pager($limit)
    //         ->sort('changed', 'DESC');
    //     $offset = $page * $limit;
    //     $query->range($offset, $limit);
    //     $nids = $query->execute();
        
    //     $nodes = \Drupal::entityTypeManager()
    //         ->getStorage('node')
    //         ->loadMultiple($nids);
    //     $totalCount = \Drupal::entityQuery('node')
    //     ->condition('type', 'article')
    //     ->condition('title', '%' . $keyword . '%', 'LIKE')
    //     ->condition('langcode', $langCode)
    //     ->count()
    //     ->execute();
    //     $totalPages = ceil($totalCount / $limit);
    //     return [
    //         $nodes,
    //         $totalPages
    //     ];
    // }
}