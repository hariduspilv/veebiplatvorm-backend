<?php


namespace Drupal\harno_pages\Controller;


class WorkersController {
  public function index(){
    $build = [];
    $filter_form = \Drupal::formBuilder()->getForm('Drupal\harno_pages\Form\WorkerForm');
    $build['#filters'] = $filter_form;
    $build['#theme'] = 'workers-page';
    $build['#content'] = $this->getWorkers();
    return $build;
  }
  public function getWorkers(){
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('departments');
    $departments = [];
    if (!empty($terms)) {
      foreach ($terms as $term) {
        $paragraph = \Drupal::entityTypeManager()
          ->getStorage('paragraph')
          ->loadByProperties(['field_department' => $term->tid]);
        if (!empty($paragraph)){
          $deparment_workers = [];
          foreach ($paragraph as $paragraph_item){

            $workers = \Drupal::entityTypeManager()
              ->getStorage('node')
              ->loadByProperties(['field_department' => $paragraph_item->id()]);
            if (empty($workers)){

            }
            else{
              foreach ($workers as $worker) {
                $deparment_workers[]=[
                  'Name' => $worker->getTitle(),
                  'position'=> $worker->get('field_position')->entity->getName(),
                  'weight' => $paragraph_item->get('field_weight')->value,
                ];
              }
            }
          }
          if (!empty($deparment_workers)){
            dump($deparment_workers);
            usort($deparment_workers, function ($a, $b) {
              $aweight = $a['weight'];
              $bweight = $b['weight'];
              return $aweight - $bweight;
            });
            $departments[$term->name] = $deparment_workers;
          }
        }

      }
    }
    dump($deparments);
    return $departments;
  }
}

