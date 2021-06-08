<?php

namespace Drupal\harno_pages\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 *
 */
class ClassController extends ControllerBase {

  /**
   *
   */
  public function index() {
    $build = [];
    $build['#theme'] = 'class-page';
    $contacts = $this->getAlumniList();
    $time = $this->getAlumniList(true);
    $build['#content'] = $contacts;
    $build['#time'] = $time;
    $build['#attached']['library'][] = 'harno_pages/harno_pages';
    $build['#cache'] = [
      'conttexts' => ['url.query_args'],
      'tags' => ['node_type:class'],
    ];
    return $build;
  }

  /**
   *
   */
  public function getAlumniList($time = false) {
    $bundle = 'class';

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
    $query->sort('field_weight', 'DESC');

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
    $node_count = count($nodes);
    foreach ($nodes as $node) {
      $nodes_grouped[] = [
        'title' => $node->label(),
        'url' => $node->toUrl()->toString(),
      ];
    }
    $splitter = 4;
    if($node_count <= 9){
      $splitter = 3;
    }
    if($node_count <= 6){
      $splitter = 2;
    }
    if($node_count <= 3){
      $splitter = 1;
    }
    $split = round(($node_count / $splitter),0,PHP_ROUND_HALF_UP);
    $nodes_grouped = array_chunk($nodes_grouped, $split);

    return $nodes_grouped;
  }

}
