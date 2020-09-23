<?php
  namespace Drupal\harno_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;

class GalleriesController extends ControllerBase
  {
    public function index(){
      $build = [];
      $build['#theme'] = 'galleries-page';
      $galleries = $this->getGalleries();
//      $academic_years = $this -> getAcademicYears($galleries);
//      devel_dump($academic_years);
//      $build['#academic_years'] = $academic_years;
      $build['#content']=$galleries;
      $build['#pager'] =['#type'=>'pager'];
      $build['#cache'] = [
        'conttexts' => ['url.query_args'],
        'tags' => ['node_type:gallery'],
      ];
      return $build;
    }
    public function getGalleries(){
      $bundle = 'gallery';
      $query = \Drupal::entityQuery('node');
      $query->condition('status', 1);
      $query->condition('type', $bundle);
      $query->sort('field_academic_year.entity.field_date_range','DESC');
      $query->sort('created','DESC');
      if(!empty($_REQUEST)){
        if(!empty($_REQUEST['years'])){
          $years = explode('|',$_REQUEST['years']);
          $year_group = $query->orConditionGroup();
          foreach ($years as $year){
            $year_group-> condition('field_academic_year.entity.name',$year);
          }
          $query->condition($year_group);
        }
      }
      $query->pager(12);
      $entity_ids = $query->execute();
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($entity_ids);
      $nodes_grouped= [];
      foreach ($nodes as $node) {
        if (!empty($node->get('field_academic_year'))) {
          if (!empty($node->get('field_academic_year')->entity)) {
            if (!empty($node->get('field_academic_year')->entity->getName())) {
              $academic_year = $node->get('field_academic_year')->entity->getName();
              $nodes_grouped[strval($academic_year)][] = $node;
            }
          }
        }
      }
      return $nodes_grouped;
    }
    public function getAcademicYears($galleries){
      if (!empty($galleries)){
        $tids = [];
        foreach ($galleries as $gallery) {
         $tids[$gallery->field_academic_year->getValue()[0]['target_id']] = $gallery->field_academic_year->getValue()[0]['target_id'];
        }
        $tid_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
        $terms = $tid_storage->loadMultiple($tids);
        $terms_and_values=[];
        foreach ($terms as $term){
          $terms_and_values[$term->id()]= $term->getName();
        }
        return $terms_and_values;
      }
    }
  }

