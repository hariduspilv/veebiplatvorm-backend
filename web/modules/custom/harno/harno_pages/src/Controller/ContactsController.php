<?php

namespace Drupal\harno_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;

/**
 *
 */
class ContactsController extends ControllerBase {

  /**
   *
   */
  public function index() {
    $build = [];
    $build['#theme'] = 'contacts-page';
    $contacts = $this->getContacts();
    $time = $this->getContacts(true);
    $filters = $this->getFilters();
    $filter_form = \Drupal::formBuilder()->getForm('Drupal\harno_pages\Form\FilterForm', $filters,'contacts');
    $build['#contact_filters'] = $filter_form;
    $build['#content'] = $contacts;
    $build['#time'] = $time;
    $build['#attached']['library'][] = 'harno_pages/harno_pages';
    $build['#attached']['library'][] = 'harno_pages/select2fix';
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('webform')) {
      $build['#attached']['library'][] = 'webform/libraries.jquery.select2';
    }
    $build['#cache'] = [
      'conttexts' => ['url.query_args'],
      'tags' => ['node_type:worker'],
    ];
    return $build;
  }

  /**
   *
   */
  public function getContacts($time = false) {
    $bundle = 'worker';

    $t = [];
    if($time){
      $query = \Drupal::entityQuery('node');
      $query->condition('status', 1);
      $query->condition('type', $bundle);

      $clone = clone $query;
      $clone->sort('changed', 'DESC');
      $clone->range(0,1);
      $entity_id = $clone->execute();
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($entity_id);
      foreach ($nodes as $node) {
        $t['changed'] = $node->get('changed')->getValue()[0]['value'];
      }

      $query->sort('created', 'ASC');
      $query->range(0,1);
      $entity_id = $query->execute();
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($entity_id);
      foreach ($nodes as $node) {
        $t['created'] = $node->get('created')->getValue()[0]['value'];
      }



      return $t;
    }

    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', $bundle);
    $query->sort('created', 'DESC');
    if (!empty($_REQUEST)) {
      if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $parameters = $_GET;
      }
      else{
        $parameters = $_POST;
      }
      if(!empty($parameters['positions']) or !empty($parameters['positions_checkbox'])){
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('positions');
        foreach ($terms as $term) {
          $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
          if($term->getName() == $parameters['positions'] or $term->getName() == key($parameters['positions_checkbox'])) {
            $query->condition('field_position.entity.name', $term->getName());
          }
        }
      }
      if(!empty($parameters['departments']) or !empty($parameters['departments_checkbox'])){
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('departments');
        foreach ($terms as $term) {
          $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
          if($term->getName() == $parameters['departments'] or $term->getName() == key($parameters['departments_checkbox'])) {
            $query->condition('field_department.entity.field_department.entity.name', $term->getName());
          }
        }
      }

      if (!empty($parameters['contactsSearch'])) {
        $textsearchGroup = $query->orConditionGroup();
        $textsearchGroup->condition('title',$parameters['contactsSearch'], 'CONTAINS');
        $textsearchGroup->condition('field_phone',$parameters['contactsSearch'],'CONTAINS');
        $query->condition($textsearchGroup);
      }
      if (!empty($parameters['contactsSearchMobile'])) {
        $textsearchGroup = $query->orConditionGroup();
        $textsearchGroup->condition('title',$parameters['contactsSearchMobile'], 'CONTAINS');
        $textsearchGroup->condition('field_phone',$parameters['contactsSearchMobile'],'CONTAINS');
        $query->condition($textsearchGroup);

      }
      if(empty($parameters['contactsSearch']) or empty($parameters['contactsSearchMobile'])){
        $query->condition('title','','CONTAINS');
      }
    }

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
    $i = 0;
    foreach ($nodes as $node) {
      if (!empty($node->get('field_department'))) {
        foreach ($node->get('field_department') as $department){
          if (!empty($department->entity)) {
            $department_id = $department->entity->get('field_department')->getValue()[0]['target_id'];
            $worker_weight = $department->entity->get('field_weight')->getValue()['0']['value'];
            $taxonomy = Term::load($department_id);
            $nodes_grouped[$taxonomy->getWeight()][strval($taxonomy->getName())][$worker_weight][] = $node;
            if($taxonomy->get('field_introduction') and !empty($taxonomy->get('field_introduction')->getValue()[0]['value'])){
              $nodes_grouped[$taxonomy->getWeight()][strval($taxonomy->getName())]['description'] = $taxonomy->get('field_introduction')->getValue()[0]['value'];
            }
            if(!isset($nodes_grouped[$taxonomy->getWeight()][strval($taxonomy->getName())]['total'])){
              $nodes_grouped[$taxonomy->getWeight()][strval($taxonomy->getName())]['total'] = 1;
            }
            else {
              $nodes_grouped[$taxonomy->getWeight()][strval($taxonomy->getName())]['total'] = $nodes_grouped[$taxonomy->getWeight()][strval($taxonomy->getName())]['total'] + 1;
            }
            ksort($nodes_grouped[$taxonomy->getWeight()][strval($taxonomy->getName())]);
          }
        }
        $i++;
      }
    }
    if($i > 0) {
      $nodes_grouped['overall_total'] = $i;
    }
    ksort($nodes_grouped);
    return $nodes_grouped;
  }

  /**
   *
   */
  public function getFilters() {

    $positions = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('positions');
    $departments = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('departments');

    $active_terms = [];
    if (!empty($positions)) {
      $active_terms['positions'][''] = t('All');
      foreach ($positions as $position) {
        $term_query = \Drupal::database()->select('node__field_position', 'nfy');
        $term_query->fields('nfy');
        $term_query->condition('nfy.field_position_target_id', $position->tid);
        $term_query->condition('nfy.bundle', 'worker');
        $term_query->range(0, 1);
        $results = $term_query->execute();
        while ($row = $results->fetchAllAssoc('field_position_target_id')) {
          if (!empty($row)) {
            $active_terms['positions'][$position->name] = $position->name;
            break;
          }
        }
      }
    }
    if (!empty($departments)) {
      $active_terms['departments'][''] = t('All');
      foreach ($departments as $department) {
        $term_query = \Drupal::database()->select('paragraph__field_department', 'nfy');
        $term_query->fields('nfy');
        $term_query->condition('nfy.field_department_target_id', $department->tid);
        $term_query->condition('nfy.bundle', 'department');
        $term_query->range(0, 1);
        $results = $term_query->execute();
        while ($row = $results->fetchAllAssoc('field_department_target_id')) {
          if (!empty($row)) {
            $active_terms['departments'][$department->name] = $department->name;
            break;
          }
        }
      }
    }
    if (!empty($active_terms)) {
      return $active_terms;
    }
  }

}
