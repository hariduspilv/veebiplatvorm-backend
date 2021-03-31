<?php


namespace Drupal\harno_pages\Form;


use Drupal\Core\Form\FormStateInterface;

class WorkerForm extends \Drupal\Core\Form\FormBase {

  /**
   * @inheritDoc
   */
  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'worker_filter_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // TODO: Implement buildForm() method.
    $form = [];
    $positions = $this->getPositions('positions');
    if (!empty($positions)){
      $form['positions'] = [
        '#type'=>'select',
        '#options'=>$positions,
        '#title' => t('Positions'),
      ];
    }
    $departments = $this->getDepartments('departments');
    if (!empty($departments)){
      $form['departments'] = [
        '#type'=>'select',
        '#options'=>$departments,
        '#title' => t('Departments'),
      ];
    }
    $form['search'] = [
      '#type' => 'textfield',
      '#title' => t('Search'),
      '#attributes' => [
        'alt' => t('Type worker name you are looking for'),
        ],
    ];
    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }
  public function getDepartments($tax_name=null){
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($tax_name);
    $active_terms = [];
    if (!empty($terms)) {
      foreach ($terms as $term) {
        $paragraph = \Drupal::entityTypeManager()
          ->getStorage('paragraph')
          ->loadByProperties(['field_department' => $term->tid]);
        if (!empty($paragraph)){
          $active_terms[$term->tid] = $term->name;
        }

      }
    }
    return $active_terms;
  }
  public function getPositions($tax_name=null){
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($tax_name);
    $active_terms = [];
    $lists_mapped = [
      'positions' => 'field_position',
      'departments' => 'field_department'
    ];
    if (!empty($terms)){
      foreach ($terms as $term){
        $term_query = \Drupal::database()->select('node__'.$lists_mapped[$tax_name], 'nfy');
        $term_query->fields('nfy');
        $term_query->condition('nfy.'.$lists_mapped[$tax_name].'_target_id', $term->tid);
        $term_query->condition('nfy.bundle', 'worker');
        $term_query->range(0, 1);
        $results = $term_query->execute();
        while ($row = $results->fetchAllAssoc($lists_mapped[$tax_name].'_target_id')) {
          if (!empty($row)) {
            $active_terms[$term->tid] = $term->name;
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
