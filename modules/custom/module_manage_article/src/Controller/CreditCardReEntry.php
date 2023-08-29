<?php

namespace Drupal\module_manage_article\Controller;

use Symfony\Component\BrowserKit\Response;
use Drupal\module_manage_article\Model\ArticleModel;
use \Drupal\file\Entity\File;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Query\Merge;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;



class CreditCardReEntry extends ControllerBase {
    public function index(){
        $data =[];
        $data['title'] ='credit_card';
        $data['form'] = \Drupal::formBuilder()->getForm('Drupal\module_manage_article\Form\CreditCardForm');
        return [
            '#theme' => 'credit_cart_re_entry',
            '#data' => 
                $data
           ,
            '#attached' => [
               'library' => ['module_manage_article/datatable_asset'],
            ],
        ];
    }


    public function loadOrderDataAjax(Request $request) {
        $entity_id = $request->get('entity_id');
    
        $query = \Drupal::entityQuery('node')
          ->condition('nid', $entity_id);
        
        $entity_ids = $query->execute();
        $entity_id = reset($entity_ids);
    
        $entity = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->load($entity_id);
    
        if ($entity) {
            $data = [
                'langcode' => $entity->get('langcode')->value,
                'body_value' => $entity->get('body')->value,
                'field_tags' => $entity->get('field_tags')->value,
                'status' => $entity->get('status')->value,
            ];
    
            return new JsonResponse($data);
        }
    
        return new JsonResponse([]);
    }
    

}