<?php

namespace Drupal\harno_settings\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;

/**
 * Class HarnoFrontpageForm.
 */
class HarnoFrontpageForm extends ConfigFormBase {

  /**
   * HarnoSettingsForm constructor.
   *
   * @param ConfigFactoryInterface     $config_factory
   */
  public function __construct (
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($config_factory);
  }
  /**
   * @param ContainerInterface $container
   *
   * @return ConfigFormBase|HarnoSettingsForm
   */
  public static function create (ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'harno_settings.frontpage',
    ];
  }
  public function getFormId() {
    return 'harno_frontpage_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('harno_settings.frontpage');

    $form['tabs'] = [
      '#type' => 'vertical_tabs',
    ];

    ##################################################### Bänneri/tausatapildi seadistus #################################################
    $form['general'] = [
      '#type' => 'details',
      '#title' => 'Bänneri/tausatapildi seadistus',
      '#description' => 'Siin saab valida, kas avalehel kuvatakse: avalehe taustapilti, bännerit piltidega või bännerit kastidega.',
      '#group' => 'tabs',
    ];

    $form['general']['background_type'] = [
      '#type' => 'select',
      '#title' => 'Avalehel kuvatakse:',
      '#options' => [
        1 => 'Avalehe taustapilti',
        2 => 'Bännerit piltidega',
        3 => 'Bännerit kastidega',
      ],
      '#default_value' =>  $config->get('general.background_type') ? $config->get('general.background_type') : 1,
      '#required' => TRUE,
    ];

    $form['general']['background_default'] = [
      '#type' => 'value',
      '#value' => $config->get('general.background_image'),
    ];
    $form['general']['background_upload'] = [
      '#type' => 'managed_file',
      '#title' => 'Avalehe taustapilt',
      '#description' => 'Kuvatakse avalehe päises. Lubatud vormingud on .jpg, .jpeg või .png. Minimaalne vajalik laius on 2800px ja minimaalne kõrgus on 800px.',
      '#default_value' =>  [$config->get('general.background_image')],
      '#upload_location' => 'public://frontpage_background',
      '#upload_validators' => [
        'file_validate_image_resolution' => ['0', '2800x800'],
        'file_validate_extensions' => [
          'jpg jpeg png'
        ],
      ],
    ];



    ##################################################### Bänner piltidega #################################################
    $form['banner_images'] = [
      '#type' => 'details',
      '#title' => 'Bänner piltidega',
      '#description' => 'Kuvatakse avalehe päises. Bänneri faili lubatud vormingud on .jpg, .jpeg või .png, minimaalne vajalik laius on 2800px ja minimaalne kõrgus on 800px.',
      '#group' => 'tabs',
    ];
    $form['banner_images']['fp_banner_images_table'] = [
      '#type' => 'table',
      '#header' => [
        '',
        'Bänneri taustapilt',
        'Bänneri tekst',
        'Lingi kuvatav tekst',
        'Sisemine link',
        'Väline veebilink',
        'Kaal'
      ],
      // TableDrag: Each array value is a list of callback arguments for
      // drupal_add_tabledrag(). The #id of the table is automatically
      // prepended; if there is none, an HTML ID is auto-generated.
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
    ];
    for ($i = 0; $i < 5; $i++) {
      $j = $i + 1;
      // TableDrag: Mark the table row as draggable.
      $form['banner_images']['fp_banner_images_table'][$i]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured
      // weight.
      $form['banner_images']['fp_banner_images_table'][$i]['#weight'] = $config->get('banner_images.link_weight_' . $j);

      $form['banner_images']['fp_banner_images_table'][$i]['banner_image_default'] = [
        '#type' => 'value',
        '#value' => $config->get('banner_images.image_'. $j),
      ];
      $form['banner_images']['fp_banner_images_table'][$i]['banner_image_upload'] = [
        '#type' => 'managed_file',
        '#title' => 'Bänneri taustapilt ' . $j,
        '#title_display' => 'invisible',
        '#default_value' =>  [$config->get('banner_images.image_'. $j)],
        '#upload_location' => 'public://frontpage_banner_images',
        '#upload_validators' => [
          'file_validate_image_resolution' => ['0', '2800x800'],
          'file_validate_extensions' => [
            'jpg jpeg png'
          ],
        ],
      ];

      $form['banner_images']['fp_banner_images_table'][$i]['banner_text'] = [
        '#type' => 'textfield',
        '#title' => 'Bänneri tekst ' . $j,
        '#title_display' => 'invisible',
        '#size' => 60,
        '#maxlength' => 170,
        '#default_value' => $config->get('banner_images.text_' . $j),
      ];
      $form['banner_images']['fp_banner_images_table'][$i]['link_name'] = [
        '#type' => 'textfield',
        '#title' => 'Lingi kuvatav tekst ' . $j,
        '#title_display' => 'invisible',
        '#size' => 30,
        '#maxlength' => 20,
        '#default_value' => $config->get('banner_images.link_name_' . $j),
      ];
      $form['banner_images']['fp_banner_images_table'][$i]['link_entity'] = [
        '#type' => 'entity_autocomplete',
        '#size' => 20,
        '#title' => 'Sisemine link ' . $j,
        '#title_display' => 'invisible',
        '#target_type' => 'node',
        '#selection_handler' => 'default', // Optional. The default selection handler is pre-populated to 'default'.
        #'#selection_settings' => [
        #  'target_bundles' => ['article', 'page'],
        #],
      ];
      if (!empty($config->get('banner_images.link_entity_' . $j))) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($config->get('banner_images.link_entity_' . $j));
        $form['banner_images']['fp_banner_images_table'][$i]['link_entity']['#default_value'] = $node;
      }
      $form['banner_images']['fp_banner_images_table'][$i]['link_url'] = [
        '#type' => 'url',
        '#title' => 'Väline veebilink ' . $j,
        '#title_display' => 'invisible',
        '#size' => 30,
        #'#autocomplete_route_name' => 'linkit.autocomplete',
        #'#autocomplete_route_parameters' => [
        #  'linkit_profile_id' => 'default',
        #],
        '#default_value' => $config->get('banner_images.link_url_' . $j),
      ];
      // TableDrag: Weight column element.
      $form['banner_images']['fp_banner_images_table'][$i]['link_weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => 'link ' . $j]),
        '#title_display' => 'invisible',
        '#default_value' => $config->get('banner_images.link_weight_' . $j),
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['table-sort-weight']],
      ];
    }


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $background_type = $form_state->getValue('background_type');
    if ($background_type == 1 AND empty($form_state->getValue('background_upload'))) {
      $message = '"Avalehe taustapilt" on kohustuslik, kui "Avalehel kuvatakse" väljas on valitud "Avalehe taustapilti".';
      $form_state->setErrorByName('background_upload', $message);
    }
    $banner_images = $form_state->getValue('fp_banner_images_table');
    foreach ($banner_images as $id => $item) {
      $j = $id + 1;
      if (!empty($item['banner_image_upload']) AND empty($item['banner_text'])) {
        $message = 'Palun täida sakis "Bänner piltidega" real '.$j. ' väli "Bänneri tekst".';
        $form_state->setErrorByName('fp_banner_images_table][' . $id . '][banner_text', $message);
      }
      if (!empty($item['banner_image_upload']) AND empty($item['link_name'])) {
        $message = 'Palun täida sakis "Bänner piltidega" real '.$j. ' väli "Lingi kuvatav tekst".';
        $form_state->setErrorByName('fp_banner_images_table][' . $id . '][link_name', $message);
      }
      if (!empty($item['banner_image_upload']) AND empty($item['link_entity']) AND empty($item['link_url'])) {
        $message = 'Palun täida sakis "Bänner piltidega" real '.$j. ' kas väli "Sisemine link" või "Väline veebilink".';
        $form_state->setErrorByName('fp_banner_images_table][' . $id . '][link_entity', $message);
        $form_state->setErrorByName('fp_banner_images_table][' . $id . '][link_url', $message);
      }
      if (!empty($item['banner_text']) AND empty($item['banner_image_upload'])) {
        $message = 'Palun täida sakis "Bänner piltidega" real '.$j. ' väli "Bänneri taustapilt".';
        $form_state->setErrorByName('fp_banner_images_table][' . $id . '][banner_image_upload', $message);
      }
      if (!empty($item['banner_text']) AND empty($item['link_name'])) {
        $message = 'Palun täida sakis "Bänner piltidega" real '.$j. ' väli "Lingi kuvatav tekst".';
        $form_state->setErrorByName('fp_banner_images_table][' . $id . '][link_name', $message);
      }
      if (!empty($item['banner_text']) AND empty($item['link_entity']) AND empty($item['link_url'])) {
        $message = 'Palun täida sakis "Bänner piltidega" real '.$j. ' väli "Lingi kuvatav tekst".';
        $message = 'Palun täida sakis "Bänner piltidega" real '.$j. ' kas väli "Sisemine link" või "Väline veebilink".';
        $form_state->setErrorByName('fp_banner_images_table][' . $id . '][link_entity', $message);
        $form_state->setErrorByName('fp_banner_images_table][' . $id . '][link_url', $message);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    ################################################### Frontpage background save ######################################
    if ($form_state->getValue('background_upload')) {
      $background_upload = $form_state->getValue('background_upload')[0];
    }
    else {
      $background_upload = 0;
    }
    $this->fileUploadHandle($background_upload, $form_state->getValue('background_default'), 'general.background_image');
    ################################################### Other general settings save ######################################
    $this->config('harno_settings.frontpage')
      ->set('general.background_type', $form_state->getValue('background_type'))
      ->save();
    ################################################### Frontpage quick links settings save ######################################

    $banner_images = $form_state->getValue('fp_banner_images_table');

    $keys = array_column($banner_images, 'link_weight');
    array_multisort($keys, SORT_ASC, $banner_images);

    foreach ($banner_images as $id => $item) {
      $j = $id + 1;
      if ($item['banner_image_upload']) {
        $background_upload = $item['banner_image_upload'][0];
      }
      else {
        $background_upload = 0;
      }
      $this->fileUploadHandle($background_upload, $item['banner_image_default'], 'banner_images.image_'. $j);

      $this->config('harno_settings.frontpage')
        ->set('banner_images.text_'. $j, $item['banner_text'])
        ->set('banner_images.link_name_'. $j, $item['link_name'])
        ->set('banner_images.link_entity_'. $j, $item['link_entity'])
        ->set('banner_images.link_url_'. $j, $item['link_url'])
        ->set('banner_images.link_weight_'. $j, $item['link_weight'])
        ->save();
    }
  }

  /**
   * fileUploadHandle.
   *
   * @param \Drupal\file\Entity\File  $upload_fid
   * @param \Drupal\file\Entity\File  $default_fid
   * @param  harno_settings_schema    $config_var_name
   */
  private function fileUploadHandle ($upload_fid, $default_fid, $config_var_name) {
    #$messenger = \Drupal::messenger();
    #$messenger->addMessage('$upload_fid ' . print_r($upload_fid,1) . ' $default_fid ' . print_r($default_fid,1) . ' $config_var_name ' . print_r($config_var_name,1), 'warning');
    #Remove file usage and mark it temporary, if new file uploaded.
    if ((!empty($default_fid) AND !$upload_fid) OR $default_fid != $upload_fid) {
      $file = File::load($default_fid);
      // Set the status flag temporary of the file object.
      if (!empty($file) AND $file->isPermanent()) {
        $file_usage = \Drupal::service('file.usage');
        $file_usage->delete($file, 'harno_settings', 'node', 1);
        $file->setTemporary();
        $file->save();
      }
      $this->config('harno_settings.frontpage')->set($config_var_name, 0);
    }

    if ($upload_fid) {
      // Load the object of the file by its fid.
      $file = File::load($upload_fid);
      // Set the status flag permanent of the file object.
      if (!empty($file)) {
        if ($file->isTemporary()) {
          $file->setPermanent();
          // Save the file in the database.
          $file->save();
          $file_usage = \Drupal::service('file.usage');
          $file_usage->add($file, 'harno_settings', 'node', 1);
        }
        $this->config('harno_settings.frontpage')->set($config_var_name, $upload_fid);
      }
    }
  }
}
