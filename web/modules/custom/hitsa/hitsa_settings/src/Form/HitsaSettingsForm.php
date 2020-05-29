<?php

namespace Drupal\hitsa_settings\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;

/**
 * Class HitsaSettingsForm.
 */
class HitsaSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  private $configuration;

  /**
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $date_formatter;
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * PriaSettingsForm constructor.
   *
   * @param ConfigFactoryInterface     $config_factory
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param  DateFormatter             $date_formatter
   */
  public function __construct (
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entityTypeManager,
    DateFormatter $date_formatter
  ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
    $this->date_formatter = $date_formatter;
  }
  /**
   * @param ContainerInterface $container
   *
   * @return ConfigFormBase|HitsaSettingsForm
   */
  public static function create (ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('date.formatter')
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
    $form['general']['favicon_default'] = [
      '#type' => 'value',
      '#value' => $config->get('general.favicon'),
    ];
    $form['general']['favicon_upload'] = [
      '#type' => 'managed_file',
      '#title' => 'Haridusasutuse veebilehe tunnusikoon (favicon)',
      '#description' => 'Kuvatakse veebilehitseja vahekaardil. Lubatud vorming .ico',
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

    if ((!empty($form_state->getValue('favicon_default')) AND !$favicon[0]) OR
      $form_state->getValue('favicon_default') != $favicon[0]) {
      $file = File::load($form_state->getValue('favicon_default'));
      // Set the status flag temporary of the file object.
      if (!empty($file) AND $file->isPermanent()) {
        $file_usage = \Drupal::service('file.usage');
        $file_usage->delete($file, 'hitsa_settings', 'user', \Drupal::currentUser()->id());
        $file->setTemporary();
      }
      $this->config('hitsa_settings.settings')
        ->set('general.favicon', 0);
    }
    $favicon = $form_state->getValue('favicon_upload');
    if ($favicon[0]) {
      // Load the object of the file by its fid.
      $file = File::load($favicon[0]);
      // Set the status flag permanent of the file object.
      if (!empty($file) AND $file->isTemporary()) {
        $file->setPermanent();
        // Save the file in the database.
        $file->save();
        $file_usage = \Drupal::service('file.usage');
        $file_usage->add($file, 'hitsa_settings', 'user', \Drupal::currentUser()->id());
        $this->config('hitsa_settings.settings')
          ->set('general.favicon', $favicon[0]);
      }
    }
    $this->config('hitsa_settings.settings')
      ->set('general.address', $form_state->getValue('address'))
      ->set('general.phone', $form_state->getValue('phone'))
      ->set('general.email', $form_state->getValue('email'))
      ->save();
    $this->config('system.site')
      ->set('name', $form_state->getValue('site_name'))
      ->set('slogan', $form_state->getValue('slogan'))
      ->save();
  }
}
