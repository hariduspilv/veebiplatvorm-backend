<?php

namespace Drupal\hitsa_settings\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;

/**
 * Class HitsaSettingsForm.
 */
class HitsaSettingsForm extends ConfigFormBase {

  /**
   * HitsaSettingsForm constructor.
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
   * @return ConfigFormBase|HitsaSettingsForm
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
      'system.site',
      'hitsa_settings.settings',
    ];
  }
  public function getFormId() {
    return 'hitsa_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('hitsa_settings.settings');
    $config_site = $this->config('system.site');

    $form['tabs'] = [
      '#type' => 'vertical_tabs',
    ];
    $form['general'] = [
      '#type' => 'details',
      '#title' => 'Haridusasutuse üldinfo',
      '#description' => 'Üldkontaktide sisestamise ja muutmise andmeplokk.',
      '#group' => 'tabs',
    ];
    $form['general']['site_name'] = [
      '#type' => 'textfield',
      '#title' => 'Haridusasutuse nimi',
      '#required' => TRUE,
      '#description' => 'Kuvatakse päises, jaluses ja veebilehitseja vahekaardil',
      '#default_value' =>  $config_site->get('name'),
    ];
    $form['general']['slogan'] = [
      '#type' => 'textfield',
      '#title' => 'Haridusasutuse moto',
      '#description' => 'Kuvatakse päises',
      '#default_value' =>  $config_site->get('slogan'),
    ];
    $form['general']['frontpage_background_default'] = [
      '#type' => 'value',
      '#value' => $config->get('general.frontpage_background'),
    ];
    $form['general']['frontpage_background_upload'] = [
      '#type' => 'managed_file',
      '#title' => 'Esilehe taustapilt',
      '#description' => 'Kuvatakse esilehe päises. Lubatud vormingud on .jpg, .jpeg või .png. Minimaalne vajalik laius on 2800px ja minimaalne kõrgus on 800px.',
      '#default_value' =>  [$config->get('general.frontpage_background')],
      '#upload_location' => 'public://frontpage_background',
      '#upload_validators' => [
        'file_validate_image_resolution' => ['0', '2800x800'],
        'file_validate_extensions' => [
          'jpg jpeg png'
        ],
      ],
    ];
    $form['general']['logo_default'] = [
      '#type' => 'value',
      '#value' => $config->get('general.logo'),
    ];
    $form['general']['logo_upload'] = [
      '#type' => 'managed_file',
      '#title' => 'Haridusasutuse logo',
      '#description' => 'Kuvatakse päises. Lubatud vormingud on .jpg, .jpeg, .png või .svg. Maksimaalne lubatud laius on 416px ja maksimaalne kõrgus on 224px.',
      '#default_value' =>  [$config->get('general.logo')],
      '#upload_location' => 'public://logo',
      '#upload_validators' => [
        'file_validate_image_resolution' => ['416x224', '0'],
        'file_validate_extensions' => [
          'jpg jpeg png svg'
        ],
      ],
    ];
    $form['general']['favicon_default'] = [
      '#type' => 'value',
      '#value' => $config->get('general.favicon'),
    ];
    $form['general']['favicon_upload'] = [
      '#type' => 'managed_file',
      '#title' => 'Haridusasutuse veebilehe tunnusikoon (favicon)',
      '#description' => 'Kuvatakse veebilehitseja vahekaardil. Lubatud vorming on .ico',
      '#default_value' =>  [$config->get('general.favicon')],
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => [
          'ico',
        ],
      ],
    ];
    $form['general']['address'] = [
      '#type' => 'textfield',
      '#title' => 'Haridusasutuse üldkontakti aadress',
      '#description' => 'Kuvatakse jaluses',
      '#default_value' =>  $config->get('general.address'),
    ];
    $form['general']['phone'] = [
      '#type' => 'textfield',
      '#title' => 'Haridusasutuse üldkontakti telefoni number',
      '#description' => 'Kuvatakse jaluses',
      '#default_value' =>  $config->get('general.phone'),
    ];
    $form['general']['email'] = [
      '#type' => 'email',
      '#title' => 'Haridusasutuse üldkontakti e-posti aadress',
      '#description' => 'Kuvatakse jaluses',
      '#default_value' =>  $config->get('general.email'),
    ];
    ##################################################### Olulisemad kontaktid #################################################
    $form['important_contacts'] = [
      '#type' => 'details',
      '#title' => 'Olulisemad kontaktid',
      '#description' => 'Olulisemate kontaktide andmeplokk, kuhu saab lisada kuni 4 olulisemat kontakti, mida kuvatakse jaluses. Kontaktile määratakse nimi ja sisu, kuid kummagi sisestamine ei ole kohustuslik.',
      '#group' => 'tabs',
    ];
    $detail_names = ['Esimene kontakt', 'Teine kontakt', 'Kolmas kontakt', 'Neljas kontakt'];

    for ($i = 0; $i < 4; $i++) {
      $j = $i + 1;
      $form['important_contacts']['group_' . $j] = [
        '#type' => 'details',
        '#title' => $detail_names[$i],
      ];
      if (!empty($config->get('important_contacts.name_' .$j)) OR
        !empty($config->get('important_contacts.body_' .$j))) {
        $form['important_contacts']['group_' . $j]['#open'] = TRUE;
      }
      $form['important_contacts']['group_'. $j]['name_' . $j] = [
        '#type' => 'textfield',
        '#title' => 'Kontakti nimi',
        '#default_value' => $config->get('important_contacts.name_' . $j),
      ];
      $form['important_contacts']['group_' . $j]['body_' . $j] = [
        '#type' => 'textfield',
        '#title' => 'Kontakti sisu',
        '#default_value' => $config->get('important_contacts.body_' . $j),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    ################################################### Frontpage background save ######################################
    if ($form_state->getValue('frontpage_background_upload')) {
      $background_upload = $form_state->getValue('frontpage_background_upload')[0];
    }
    else {
      $background_upload = 0;
    }
    $this->fileUploadHandle($background_upload, $form_state->getValue('frontpage_background_default'), 'general.frontpage_background');
    ################################################### Logo save ######################################
    if ($form_state->getValue('logo_upload')) {
      $logo_upload = $form_state->getValue('logo_upload')[0];
    }
    else {
      $logo_upload = 0;
    }
    $this->fileUploadHandle($logo_upload, $form_state->getValue('logo_default'), 'general.logo');

    ################################################### Favicon save ######################################
    if ($form_state->getValue('favicon_upload')) {
      $favicon_upload = $form_state->getValue('favicon_upload')[0];
    }
    else {
      $favicon_upload = 0;
    }
    $this->fileUploadHandle($favicon_upload, $form_state->getValue('favicon_default'), 'general.favicon');

    ################################################### Other settings save ######################################
    $this->config('hitsa_settings.settings')
      ->set('general.address', $form_state->getValue('address'))
      ->set('general.phone', $form_state->getValue('phone'))
      ->set('general.email', $form_state->getValue('email'))
      ->save();
    for ($i = 0; $i < 4; $i++) {
      $j = $i + 1;
      $this->config('hitsa_settings.settings')
        ->set('important_contacts.name_'. $j, $form_state->getValue('name_'. $j))
        ->set('important_contacts.body_'. $j, $form_state->getValue('body_'. $j))
        ->save();
    }
    $this->config('system.site')
      ->set('name', $form_state->getValue('site_name'))
      ->set('slogan', $form_state->getValue('slogan'))
      ->save();
  }

  /**
   * fileUploadHandle.
   *
   * @param \Drupal\file\Entity\File  $upload_fid
   * @param \Drupal\file\Entity\File  $default_fid
   * @param  hitsa_settings_schema    $config_var_name
   */
  private function fileUploadHandle ($upload_fid, $default_fid, $config_var_name) {
    #Remove file usage and mark it temporary, if new file uploaded.
    if ((!empty($default_fid) AND !$upload_fid) OR $default_fid != $upload_fid) {
      $file = File::load($default_fid);
      // Set the status flag temporary of the file object.
      if (!empty($file) AND $file->isPermanent()) {
        $file_usage = \Drupal::service('file.usage');
        $file_usage->delete($file, 'hitsa_settings', 'user', \Drupal::currentUser()->id());
        $file->setTemporary();
        $file->save();
      }
      $this->config('hitsa_settings.settings')
        ->set($config_var_name, 0);
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
          $file_usage->add($file, 'hitsa_settings', 'user', \Drupal::currentUser()->id());
        }
        $this->config('hitsa_settings.settings')
          ->set($config_var_name, $upload_fid);
      }
    }
  }
}
