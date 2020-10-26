<?php

namespace Drupal\harno_pages\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\harno_pages\Controller\GalleriesController;
use Drupal\Core\Entity\Element\EntityAutocomplete;

/**
 *
 */
class FilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'gallery_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $academic_years = NULL) {
    // devel_dump($academic_years);
    $form['#attributes']['data-plugin'] = 'filters';
    $form['#attributes']['role'] = 'filter';
    if (!empty($academic_years)) {
      $form['years'] = [
        '#title' => t('Choose year'),
        // '#attributes' => ['name' => 'years'],
        '#id' => 'gallery-years',
        '#type' => 'checkboxes',
        '#ajax' => [
          'wrapper' => 'filter-target',
          'event' => 'change',
          'callback' => '::filterResults',
        ],
        '#options' => $academic_years,
      ];
    }
    $form['bottom'] = [
      '#type' => 'fieldset',
      '#id' => 'galleries-bottomFilter',
    ];
    $form['bottom']['date_start'] = [
      '#type' => 'textfield',
      '#title' => t('Show from'),
      '#ajax' => [
        'wrapper' => 'filter-target',
        'event' => 'change',
        'keypress' => TRUE,
        'callback' => '::filterResults',
        'disable-refocus' => TRUE,
      ],
    ];
    $form['bottom']['date_end'] = [
      '#type' => 'textfield',
      '#title' => t('Show to'),
      '#ajax' => [
        'wrapper' => 'filter-target',
        'event' => 'change',
        'keypress' => TRUE,
        'callback' => '::filterResults',
        'disable-refocus' => TRUE,
      ],
    ];
    $form['bottom']['searchgroup'] = [
      '#type' => 'fieldset',
      '#id' => 'galleriesSearchGroup',
    ];
    $form['bottom']['searchgroup']['gallerySearch'] = [
      '#type' => 'textfield',
      '#title' => t('Search'),
      '#ajax' => [
        'wrapper' => 'filter-target',
        'event' => 'change',
        'keypress' => TRUE,
        'callback' => '::filterResults',
        'disable-refocus' => TRUE,
      ],
    ];
    $form['bottom']['searchgroup']['searchButton'] = [
      '#attributes' => [
        'style' => 'display:none;',
      ],
      '#type' => 'button',
      '#title' => t('Search'),
      '#value' => t('Search'),
      '#ajax' => [
        'callback' => '::filterResults',
      ],

    ];
    if (!empty($_REQUEST)) {
      if (!empty($_REQUEST['years'])) {
        if (is_array($_REQUEST['years'])) {
          $form['top_row']['years']['#default_value'] = $_REQUEST['years'];
        }
        else {
          $form['top_row']['years']['#default_value'] = explode('|', $_REQUEST['years']);
        }
      }
      if (!empty($_REQUEST['date_start'])) {
        $form['bottom']['date_start']['#default_value'] = $_REQUEST['date_start'];
      }
      if (!empty($_REQUEST['date_end'])) {
        $form['bottom']['date_end']['#default_value'] = $_REQUEST['date_end'];
      }
      if (!empty($_REQUEST['gallerySearch'])) {
        $form['bottom']['searchgroup']['gallerySearch']['#default_value'] = $_REQUEST['gallerySearch'];
      }
    }
    $form['#theme_wrappers'] = ['form-galleries'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $article_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($form_state->getValue('article'));

    $form_state->setRebuild(TRUE);
  }

  /**
   *
   */
  public function filterResults(array &$form, FormStateInterface $form_state) {
    $galleries = new GalleriesController();
    $galleries = $galleries->getGalleries();

    $parameters = [];
    $form_values = $form_state->getValues();
    if (!empty($form_values)) {
      if (!empty($form_values['years'])) {
        foreach ($form_values['years'] as $year) {
          if (empty($parameters['years'])) {
            $parameters['years'] = $year;
          }
          else {
            $parameters['years'] .= '|' . $year;
          }
        }
      }
      if (!empty($form_values['date_start'])) {
        $parameters['date_start'] = $_REQUEST['date_start'];
      }
      if (!empty($form_values['date_end'])) {
        $parameters['date_end'] = $_REQUEST['date_end'];
      }
      if (!empty($form_values['gallerySearch'])) {
        $parameters['gallerySearch'] = $_REQUEST['gallerySearch'];
      }
    }
    $build = [];
    $build['#theme'] = 'galleries-response';

    $build['#content'] = $galleries;
    $build['#pager'] = [
      '#type' => 'pager',
      '#parameters' => $parameters,
    ];
    return $build;
  }

}
