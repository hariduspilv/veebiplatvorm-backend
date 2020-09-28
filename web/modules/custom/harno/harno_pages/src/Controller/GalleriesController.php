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
      $academic_years = $this -> getAcademicYears();
      $filter_form = \Drupal::formBuilder()->getForm('Drupal\harno_pages\Form\FilterForm',$academic_years);
//      devel_dump($filter_form);
//      $build['#academic_years'] = $academic_years;
      $build['#academic_years'] = $filter_form;
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
        if (!empty($_REQUEST['gallerySearch'])){
          $query->condition('title',$_REQUEST['gallerySearch'],'CONTAINS');
        }
        if (!empty($_REQUEST['date_start'])){
          $startDate = strtotime('midnight'.$_REQUEST['date_start']);
        }
        if (!empty($_REQUEST['date_end'])){
          $endDate = strtotime('midnight'.$_REQUEST['date_end']. '+1 day');
        }
        if (!empty($startDate)) {
          $query->condition('created', $startDate, '>=');
        }
        if( !empty($endDate))   {
            $query->condition('created',$endDate,'<=');
        }
        if(!empty($_REQUEST['years'])){
          $years = $_REQUEST['years'];
          if(is_array($years)){

          }
          else {
            $years = explode('|', $_REQUEST['years']);
          }
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
    public function getAcademicYears(){
      $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('academic_year');

      if (!empty($terms)){
        $active_terms = [];
        foreach ($terms as $academic_year){
          $term_query = \Drupal::database()->select('node__field_academic_year','nfy');
          $term_query->fields('nfy');
          $term_query->condition('nfy.field_academic_year_target_id',$academic_year->tid);
          $term_query->condition('nfy.bundle','gallery');
          $term_query->range(0,1);
          $results = $term_query->execute();
          while($row = $results->fetchAllAssoc('field_academic_year_target_id')){
            if (!empty($row)){
              $active_terms[$academic_year->name] = $academic_year->name;
              break;
            }
          }
        }
      }
      if (!empty($active_terms)){
        return $active_terms;
      }
    }
  }

