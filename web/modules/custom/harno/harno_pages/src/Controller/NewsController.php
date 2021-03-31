<?php

namespace Drupal\harno_pages\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 *
 */
class NewsController extends ControllerBase {

  /**
   *
   */
  public function index() {
    $build = [];
    $build['#theme'] = 'news-page';
    $galleries = $this->getNews();
    $academic_years = $this->getAcademicYears();
    $filter_form = \Drupal::formBuilder()->getForm('Drupal\harno_pages\Form\FilterForm', $academic_years,'news');
    // devel_dump($filter_form);
    //      $build['#academic_years'] = $academic_years;.
    $build['#academic_years'] = $filter_form;
    $build['#content'] = $galleries;
    $build['#pager'] = ['#type' => 'pager'];
    $build['#attached']['library'][] = 'harno_pages/harno_pages';
    $build['#cache'] = [
      'conttexts' => ['url.query_args'],
      'tags' => ['node_type:gallery'],
    ];
    return $build;
  }

  /**
   *
   */
  public function getNews() {
    $bundle = 'article';
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', $bundle);
    $query->sort('field_academic_year.entity.field_date_range', 'DESC');
    $query->sort('created', 'DESC');
    if (!empty($_REQUEST)) {
      if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $parameters = $_GET;
      }
      else{
        $parameters = $_POST;
      }
      if(!empty($_REQUEST['_wrapper_format'])){
        if(isset($_REQUEST['page'])){
          $_REQUEST['page'] = 0;
          if(isset($_REQUEST['page'])){
            $_REQUEST['page']=0;
            $existingQuery = \Drupal::service('request_stack')->getCurrentRequest()->query->all();
            $existingQuery = \Drupal::service('request_stack')->getCurrentRequest()->query->remove('page');
          }
        }

      }
      if (!empty($parameters['newsSearch'])) {
        $textsearchGroup = $query->orConditionGroup();
        $textsearchGroup->condition('title',$parameters['newsSearch'], 'CONTAINS');
        $textsearchGroup->condition('body',$parameters['newsSearch'],'CONTAINS');
        $textsearchGroup->condition('body.summary',$parameters['newsSearchMobile'],'CONTAINS');
        $query->condition($textsearchGroup);
      }
      if (!empty($parameters['newsSearchMobile'])) {
        $textsearchGroup = $query->orConditionGroup();
        $textsearchGroup->condition('title',$parameters['newsSearchMobile'], 'CONTAINS');
        $textsearchGroup->condition('body.value',$parameters['newsSearchMobile'],'CONTAINS');
        $textsearchGroup->condition('body.summary',$parameters['newsSearchMobile'],'CONTAINS');
        $query->condition($textsearchGroup);

      }
      if(!empty($parameters['article_type'])){
        $art_group = $query->orConditionGroup();
        $ignnore_type = false;
        foreach ($parameters['article_type'] as $key => $art_type){
          if($key=='all'){
            $ignnore_type = true;
            break;
          }
          $art_group->condition('field_article_type',$key,'=');
        }
        if (!$ignnore_type) {
          $query->condition($art_group);
        }
      }
      if(!empty($parameters['article_type_mobile'])){
        $art_group = $query->orConditionGroup();
        $ignnore_type = false;
        foreach ($parameters['article_type_mobile'] as $key => $art_type){
          if($key=='all'){
            $ignnore_type = true;
            break;
          }
          $art_group->condition('field_article_type',$key,'=');
        }
        if (!$ignnore_type) {
          $query->condition($art_group);
        }
      }
      if(empty($parameters['newsSearch']) or empty($parameters['newsSearchMobile'])){
        $query->condition('title','','CONTAINS');
      }
      if (!empty($parameters['date_start'])) {
        $startDate = strtotime('midnight' . $parameters['date_start']);
      }
      if (!empty($parameters['date_end'])) {
        $endDate = strtotime('midnight' . $parameters['date_end'] . '+1 day');
      }
      if (!empty($startDate)) {
        $query->condition('created', $startDate, '>=');
      }
      if (!empty($endDate)) {
        $query->condition('created', $endDate, '<=');
      }
      if (!empty($parameters['years'])) {
        $years = $parameters['years'];
        if (is_array($years)) {

        }
        else {
          $years = explode(',', $parameters['years']);
        }
        // devel_dump($years);
        $year_group = $query->orConditionGroup();
        foreach ($years as $year) {
          if ($year == 'older') {
            $neweryears = $this->getAcademicYears();
            if (!empty($neweryears['older'])) {
              unset($neweryears['older']);
            }
            $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('academic_year');
            foreach ($terms as $term) {
              $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
              if (isset($neweryears[$term->getName()])) {
                continue;
              }
              else {
                $year_group->condition('field_academic_year.entity.name', $term->getName());
              }
            }
          }
          $year_group->condition('field_academic_year.entity.name', $year);
        }
        $query->condition($year_group);
      }
    }
    $query->pager(12);
    $entity_ids = $query->execute();
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $node_storage->loadMultiple($entity_ids);
    usort($nodes, function ($a, $b) {
      $asticky = $a->isSticky();
      if($asticky){
        $asticky = 1;
      }
      else{
        $asticky=0;
      }
      $bsticky = $b->isSticky();
      if($bsticky){
        $bsticky = 1;
      }
      else{
        $bsticky=0;
      }
        return $bsticky - $asticky;
    });

    $nodes_grouped = [];
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

  /**
   *
   */
  public function getAcademicYears() {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('academic_year');

    if (!empty($terms)) {
      $active_terms = [];
      foreach ($terms as $academic_year) {
        $term_query = \Drupal::database()->select('node__field_academic_year', 'nfy');
        $term_query->fields('nfy');
        $term_query->condition('nfy.field_academic_year_target_id', $academic_year->tid);
        $term_query->condition('nfy.bundle', 'article');
        $term_query->range(0, 1);
        $results = $term_query->execute();
        while ($row = $results->fetchAllAssoc('field_academic_year_target_id')) {
          if (!empty($row)) {
            $active_terms[$academic_year->tid] = $academic_year->name;
            break;
          }
        }
      }
    }
    foreach ($active_terms as $key => $term) {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($key);
      $start = $term->get('field_date_range')->getValue()[0]['value'];
      $end = $term->get('field_date_range')->getValue()[0]['end_value'];
      $term->{'#start_year'} = $start;
      $terms_by_year[$key] = $term;
    }
    usort($terms_by_year, function ($a, $b) {
      if (!empty($a->{'#start_year'}) && !empty($b->{'#start_year'})) {
        $aweight = strtotime($a->{'#start_year'});
        $bweight = strtotime($b->{'#start_year'});
        return $bweight - $aweight;
      }
    });
    $active_terms = [];
    $count = 0;
    foreach ($terms_by_year as $term) {

      if ($count > 4) {
        $active_terms['older'] = t('Older news');
        break;
      }
      $active_terms[$term->getName()] = $term->getName();
      $count++;
    }
    if (!empty($active_terms)) {
      return $active_terms;
    }
  }

}
