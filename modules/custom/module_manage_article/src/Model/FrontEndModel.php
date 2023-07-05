<?php
namespace Drupal\module_manage_article\Model;
use Drupal\Core\Database\Driver\pgsql\Select;
use Drupal\Code\Database\Database;
use Symfony\Component\HttpFoundation\Request;


class FrontEndModel{
    public function getListArt(Request $request) {
        // $langCode= \Drupal::languageManager()->getCurrentLanguage();
        if(isset($_GET['langcode'])){
            $langCode = $_GET['langcode'];
        }
        $page = $request->query->get('page', 0);
        $limit = 2;
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
}