<?php

namespace Drupal\harno_pages\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\harno_pages\Controller\ContactsController;
use Drupal\harno_pages\Controller\GalleriesController;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\harno_pages\Controller\NewsController;
use Drupal\media_library\Ajax\UpdateSelectionCommand;

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
  public function buildForm(array $form, FormStateInterface $form_state, $academic_years = NULL,$type=NULL) {
    // devel_dump($academic_years);
    if (!empty($type)){
      $form['#storage']['type'] = $type;
    }
    if($type == 'contacts') {
      if(!empty($academic_years)){
        $form['#attributes']['data-plugin'] = 'filters';
        $form['#attributes']['role'] = 'filter';
        $form['#theme_wrappers'] = ['form-contacts'];
        if (!empty($_REQUEST)) {
          if (!empty($_REQUEST['positions'])) {
            $form['#storage']['positions'] = $_REQUEST['positions'];
          }
          if (!empty($_REQUEST['departments'])) {
            $form['#storage']['departments'] = $_REQUEST['departments'];
          }
        }
        $form['top'] = [
          '#type' => 'fieldset',
          '#id' => 'contacts-topFilter',
        ];
        $form['top']['positions'] = [
          '#title' => t('Choose position'),
          '#id' => 'worker-position',
          '#type' => 'select2',
          '#attributes' => [
            'data-plugin' => 'selectTwo',
            'data-disable-refocus' => true,
          ],
          '#placeholder' => ' ',
          '#ajax' => [
            'wrapper' => 'filter-target',
            'event' => 'change',
            'callback' => '::filterResults',
            'progress' => [
              'type' => 'throbber',
              'message' => $this->t('Verifying entry...'),
            ],
          ],
          '#options' => $academic_years['positions'],
        ];
        $form['top']['departments'] = [
          '#title' => t('Choose department'),
          '#id' => 'worker-department',
          '#type' => 'select2',
          '#attributes' => [
            'data-plugin' => 'selectTwo',
            'data-disable-refocus' => true,
          ],
          '#placeholder' => ' ',
          '#ajax' => [
            'wrapper' => 'filter-target',
            'event' => 'change',
            'callback' => '::filterResults',
            'progress' => [
              'type' => 'throbber',
              'message' => $this->t('Verifying entry...'),
            ],
          ],
          '#options' => $academic_years['departments'],
        ];
        $form['top']['contactsSearch'] = [
          '#type' => 'textfield',
          '#title' => t('Search contacts'),
          '#attributes' => [
            'alt' => t('Type contact name you are looking for'),
          ],
//          '#autocomplete_route_name' => 'harno_pages.contacts.autocomplete',
          '#ajax' => [
            'wrapper' => 'filter-target',
            'keypress' => TRUE,
            'callback' => '::filterResults',
            'event' => 'finishedinput',
            'disable-refocus' => TRUE,
          ],
        ];
        $form['top']['searchbutton'] = [
          '#attributes' => [
            'style' => 'display:none;',
          ],
          '#type' => 'button',
          '#title' => t('Search contacts'),
          '#value' => t('Submit'),
          '#ajax' => [
            'callback' => '::filterResults',
            'wrapper' => 'filter-target',
            'disable-refocus' => true,
            'keypress'=>TRUE,
          ],

        ];
        $form['bottom'] = [
          '#type' => 'fieldset',
          '#id' => 'contacts-bottomFilter',
        ];
        $form['bottom']['contactsSearchMobile'] = [
          '#type' => 'textfield',
          '#title' => t('Search contacts'),
          '#attributes' => [
            'alt' => t('Type contact name you are looking for'),
          ],
          '#ajax' => [
            'wrapper' => 'filter-target',
            'keypress' => TRUE,
            'callback' => '::filterResults',
            'event' => 'finishedinput',
            'disable-refocus' => TRUE,
          ],
        ];


        $filter_values = $form_state->getValues();

        if(($_REQUEST['positions'] and $_REQUEST['positions'] != 'all')) {
          $form['bottom']['positions_checkbox'] = [
            '#type' => 'checkboxes',
            '#id' => 'positions_',
            '#ajax' => [
              'wrapper' => 'filter-target',
              'event' => 'change',
              'callback' => '::filterResults',
              'progress' => [
                'type' => 'throbber',
                'message' => '',
              ],
            ],
            '#attributes' => [
              'checkbox-type' => 'position'
            ],
            '#options' => [$_REQUEST['positions'] => $_REQUEST['positions']],
          ];
        }
        if(($_REQUEST['departments'] and $_REQUEST['departments'] != 'all')) {
          $form['bottom']['departments_checkbox'] = [
            '#type' => 'checkboxes',
            '#id' => 'departments_',
            '#ajax' => [
              'wrapper' => 'filter-target',
              'event' => 'change',
              'callback' => '::filterResults',
              'progress' => [
                'type' => 'throbber',
                'message' => '',
              ],
            ],
            '#attributes' => [
              'checkbox-type' => 'department',
            ],
            '#options' => [$_REQUEST['departments'] => $_REQUEST['departments']],
          ];
        }

        $form['#attached']['library'][] = 'harno_pages/accordion';
      }

      if (!empty($_REQUEST['positions'])) {
        $form['positions']['#default_value'] = $_REQUEST['positions'];
      }
      if (!empty($_REQUEST['departments'])) {
        $form['departments']['#default_value'] = $_REQUEST['departments'];
      }
      return $form;
    }

    $form['#attributes']['data-plugin'] = 'filters';
    $form['#attributes']['role'] = 'filter';
    if (!empty($academic_years)) {
      if($type=='news'){
        $form['top_filters'] = [
          '#type' => 'fieldset',
          '#id' => 'news-topFilter',
        ];

        $articleoptions = $this->getArticleTypes();
        $form['top_filters']['article_type_mobile'] = [
          '#type' => 'checkboxes',
          '#id' => 'article_type_mobile',
          '#attributes' => [
            'checkbox-type' => 'collect'
          ],
          '#ajax' => [
            'wrapper' => 'filter-target',
            'event' => 'change',
            'callback' => '::filterResults',
          ],
          '#default_value' => array('all'),
          '#options' => $articleoptions,
        ];
        $form['top_filters']['years'] = [
          '#title' => t('Choose year'),
          // '#attributes' => ['name' => 'years'],
          '#id' => 'gallery-years',
          '#type' => 'checkboxes',
          '#attributes' => [
            'checkbox-type' => 'collect'
          ],
          '#ajax' => [
            'wrapper' => 'filter-target',
            'event' => 'change',
            'callback' => '::filterResults',
          ],
          '#my-id' => 'news-years',
          '#options' => $academic_years,
        ];
      }
      else {
        $form['years'] = [
          '#title' => t('Choose year'),
          // '#attributes' => ['name' => 'years'],
          '#id' => 'gallery-years',
          '#type' => 'checkboxes',
          '#attributes' => [
            'checkbox-type' => 'collect'
          ],
          '#ajax' => [
            'wrapper' => 'filter-target',
            'event' => 'change',
            'callback' => '::filterResults',
          ],
          '#options' => $academic_years,
        ];
      }
    }
    if ($type=='news'){
      $form['bottom'] = [
        '#type' => 'fieldset',
        '#id' => 'news-bottomFilter',
      ];
    }
    else{

      $form['bottom'] = [
        '#type' => 'fieldset',
        '#id' => 'galleries-bottomFilter',
      ];
    }
    if ($type=='news'){
      $articleoptions = $this->getArticleTypes();
      $form['bottom']['article_type'] = [
        '#type' => 'checkboxes',
        '#id' => 'article_type',
        '#ajax' => [
          'wrapper' => 'filter-target',
          'event' => 'change',
          'callback' => '::filterResults',
        ],
        '#default_value' => array('all'),
        '#options' => $articleoptions,
      ];
    }
    $form['bottom']['date_start'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'alt' => t('Filter starting from')
      ],
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
      '#attributes' => [
        'alt' => t('Filter ending with')
      ],
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
    if($type == 'news'){

      $form['bottom']['searchgroup']['newsSearch'] = [
        '#type' => 'textfield',
        '#title' => t('Search'),
        '#attributes' => [
          'alt' => t('Type news title you are looking for'),
        ],
        '#autocomplete_route_name' => 'harno_pages.news.autocomplete',
        '#ajax' => [
          'wrapper' => 'filter-target',
          'keypress' => TRUE,
          'callback' => '::filterResults',
          'event' => 'finishedinput',
          'disable-refocus' => TRUE,
        ],
      ];
      $form['bottom']['searchgroup']['newsSearchMobile'] = [
        '#type' => 'textfield',
        '#title' => t('Search'),
        '#attributes' => [
          'alt' => t('Type news title you are looking for'),
        ],
        '#ajax' => [
          'wrapper' => 'filter-target',
          'keypress' => TRUE,
          'callback' => '::filterResults',
          'event' => 'finishedinput',
          'disable-refocus' => TRUE,
        ],
      ];
    }
    else {
      $form['bottom']['searchgroup']['gallerySearch'] = [
        '#type' => 'textfield',
        '#title' => t('Search'),
        '#attributes' => [
          'alt' => t('Type gallery title you are looking for'),
        ],
        '#autocomplete_route_name' => 'harno_pages.autocomplete',
        '#ajax' => [
          'wrapper' => 'filter-target',
          'keypress' => TRUE,
          'callback' => '::filterResults',
          'event' => 'finishedinput',
          'disable-refocus' => TRUE,
        ],
      ];
      $form['bottom']['searchgroup']['gallerySearchMobile'] = [
        '#type' => 'textfield',
        '#title' => t('Search'),
        '#attributes' => [
          'alt' => t('Type gallery title you are looking for'),
        ],
        '#ajax' => [
          'wrapper' => 'filter-target',
          'keypress' => TRUE,
          'callback' => '::filterResults',
          'event' => 'finishedinput',
          'disable-refocus' => TRUE,
        ],
      ];
    }
    $form['bottom']['searchgroup']['searchbutton'] = [
      '#attributes' => [
        'style' => 'display:none;',
      ],
      '#type' => 'button',
      '#title' => t('Search'),
      '#value' => t('Submit'),
      '#ajax' => [
        'callback' => '::filterResults',
        'wrapper' => 'filter-target',
        'disable-refocus' => true,
        'keypress'=>TRUE,
      ],

    ];
    $form['bottom']['searchgroup']['searchbuttonmobile'] = [
      '#attributes' => [
        'style' => 'display:none;',
      ],
      '#type' => 'button',
      '#title' => t('Search'),
      '#value' => t('Submit'),
      '#ajax' => [
        'callback' => '::filterResults',
        'wrapper' => 'filter-target',
        'disable-refocus' => true,
        'keypress'=>TRUE,
      ],

    ];
    if (!empty($_REQUEST)) {
      // devel_dump($_REQUEST);
      if (!empty($_REQUEST['years'])) {
        $form['#storage']['active-years'] = $_REQUEST['years'];
        if (is_array($_REQUEST['years'])) {
          // $form['years']['#default_value'] = $_REQUEST['years'];
        }
        else {
          // $form['years']['#default_value'] = explode('|', $_REQUEST['years']);
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
      if (!empty($_REQUEST['gallerySearchMobile'])) {
        $form['bottom']['searchgroup']['gallerySearch']['#default_value'] = $_REQUEST['gallerySearchMobile'];
      }
      if (!empty($_REQUEST['newsSearch'])) {
        $form['bottom']['searchgroup']['newsSearch']['#default_value'] = $_REQUEST['newsSearch'];
      }
      if (!empty($_REQUEST['newsSearchMobile'])) {
        $form['bottom']['searchgroup']['newsSearchMobile']['#default_value'] = $_REQUEST['newsSearchMobile'];
      }
      if (!empty($_REQUEST['article_type'])) {
        $art_def = '';
        foreach ($_REQUEST['article_type'] as $art_type){
          $art_def.=empty($art_def)?$art_type:','.$art_type;
        }
        $form['bottom']['article_type']['#default_value'] = [$art_def];
      }
    }
    // devel_dump($form);
    if ($type == 'news'){
      $form['#theme_wrappers'] = ['form-news'];
    }
    else {
      $form['#theme_wrappers'] = ['form-galleries'];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $article_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($form_state->getValue('article'));

    $form_state->setRebuild(TRUE);
  }
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  /**
   *
   */
  public function filterResults(array &$form, FormStateInterface $form_state) {


    unset($form['pager']);
    if(!empty($_GET)){
      if (!empty($_GET['_wrapper_format'])){
        unset($_GET['page']);
        unset($_REQUEST['page']);
        unset($GLOBALS['_REQUEST']['page']);
        unset($GLOBALS['_REQUEST']['page']);
//        unset($GLOBALS['request']->query->parameters["page"]);
        $existingQuery = \Drupal::service('request_stack')->getCurrentRequest()->query->all();
        $existingQuery = \Drupal::service('request_stack')->getCurrentRequest()->query->remove('page');

        $existingQuery = \Drupal::service('request_stack')->getCurrentRequest()->query->get('page');



      }
    }

    if (!empty($form['#storage']))
    {
      if (!empty($form['#storage']['type'])){
        $type = $form['#storage']['type'];
      }
    }
    if(!isset($type)){
      $type = 'galleries';
    }
    if($type == 'news'){
      $galleries = new NewsController();
      $galleries = $galleries->getNews();
    }
    elseif ($type=='galleries') {
      $galleries = new GalleriesController();
      $galleries = $galleries->getGalleries();
    }
    elseif($type == 'contacts'){
      $galleries = new ContactsController();
      $galleries = $galleries->getContacts();
    }
    $parameters = [];
    $form_values = $form_state->getUserInput();
    if (!empty($form_values)) {
      if (!empty($form_values['years'])) {
        foreach ($form_values['years'] as $year) {
          if (!empty($year)) {
            if (empty($parameters['years'])) {
              $parameters['years'][$year] = $year;
            }
            else {
              $parameters['years'][$year] = $year;
            }
          }
        }
      }
      if (!empty($form_values['article_type'])) {
        foreach ($form_values['article_type'] as $art_type) {
          if (!empty($art_type)) {
            if (empty($parameters['years'])) {
              $parameters['article_type'][$art_type] = $art_type;
            }
            else {
              $parameters['article_type'][$art_type] = $art_type;
            }
          }
        }
      }
      if (!empty($form_values['article_type_mobile'])) {
        foreach ($form_values['article_type_mobile'] as $art_type) {
          if (!empty($art_type)) {
            if (empty($parameters['years'])) {
              $parameters['article_type_mobile'][$art_type] = $art_type;
            }
            else {
              $parameters['article_type_mobile'][$art_type] = $art_type;
            }
          }
        }
      }
      if (isset($form_values['date_start'])) {
        $parameters['date_start'] = $_REQUEST['date_start'];
      }
      if (isset($form_values['date_end'])) {
        $parameters['date_end'] = $_REQUEST['date_end'];
      }
      if (isset($form_values['gallerySearch'])) {
        $parameters['gallerySearch'] = $_REQUEST['gallerySearch'];
      }
      if (isset($form_values['gallerySearchMobile'])) {
        $parameters['gallerySearchMobile'] = $_REQUEST['gallerySearchMobile'];
      }
      if (isset($form_values['newsSearch'])) {
        $parameters['newsSearch'] = $_REQUEST['newsSearch'];
      }
      if (isset($form_values['newsSearchMobile'])) {
        $parameters['newsSearchMobile'] = $_REQUEST['newsSearchMobile'];
      }
      if (isset($form_values['contactsSearch'])) {
        $parameters['contactsSearch'] = $_REQUEST['contactsSearch'];
      }
      if (isset($form_values['contactsSearchMobile'])) {
        $parameters['contactsSearchMobile'] = $_REQUEST['contactsSearchMobile'];
      }
      $filter_values = $form_state->getValues();
      if (isset($form_values['positions'])) {
        $parameters['positions'] = $_REQUEST['positions'];
        $filters['#content']['positions'] = $filter_values['positions'];
      }
      if (isset($form_values['departments'])) {
        $parameters['departments'] = $_REQUEST['departments'];
        $filters['#content']['departments'] = $filter_values['departments'];
      }
      if(!empty($_GET)){
        if (!empty($_GET['_wrapper_format'])){
//          $parameters['page']=0;
        }
      }
    }
    if(!empty($filter_values = $form_state->getValues())){
      $filters = [];
      if(!empty($filter_values['years'])){
        $filters['#theme'] = 'active-filters';
        $filters['#content']['years'] = $filter_values['years'];
      }
      if(!empty($filter_values['positions'])){
        $filters['#theme'] = 'active-filters';
        $filters['#content']['positions'] = $filter_values['positions'];
      }
      if(!empty($filter_values['departments'])){
        $filters['#theme'] = 'active-filters';
        $filters['#content']['departments'] = $filter_values['departments'];
      }
    }
    $build = [];
    $build['#theme'] = $type.'-response';
    $build['#content'] = $galleries;
    $build['#pager'] = [
      '#type' => 'pager',
      '#parameters' => $parameters,
    ];
    $build['#attached']['library'][] = 'harno_pages/accordion';
    $response = new AjaxResponse();
//    $response['#attached']['library'][] = 'harno_pages/js/urlparameters.js';
    $response->addCommand(new HtmlCommand('#mobile-active-filters', $filters));
    $response->addCommand(new ReplaceCommand('#filter-target',$build));
    $response->addCommand(new InvokeCommand('.accordion--contacts', 'accordion'));
    for($x = 0; $x <= $galleries['overall_total']; $x++){
      $response->addCommand(new InvokeCommand('#contact-modal-'.$x, 'contactModal'));
    }
//      $response->addCommand(new InvokeCommand(NULL, 'filterFocus', [$form_state->getTriggeringElement()]));

    //    $response->addCommand(new UpdateSelectionCommand());

    return $response;
  }
  public function getArticleTypes(){
    $entityManager = \Drupal::service('entity_field.manager');
    $fields = $entityManager->getFieldStorageDefinitions('node', 'article');
    $outoptions = [];
    $outoptions['all']= t('All');
    $options = options_allowed_values($fields['field_article_type']);
    foreach ($options as $key => $option){
      $outoptions[strval($key)] = $option;
    }
    return $outoptions;
  }

}
