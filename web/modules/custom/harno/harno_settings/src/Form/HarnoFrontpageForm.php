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
      'harno_settings.settings',
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

    $config = $this->config('harno_settings.settings');
    $config_site = $this->config('system.site');

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

    $form['general']['frontpage_background_type'] = [
      '#type' => 'select',
      '#title' => 'Kas avalehel kuvatakse: ',
      '#options' => [
        1 => 'Avalehe taustapilti',
        2 => 'Bännerit piltidega',
        3 => 'Bännerit kastidega',
      ],
      '#default_value' =>  $config->get('general.frontpage_background_type') ? $config->get('general.frontpage_background_type') : 1,
      '#required' => TRUE,
    ];

    $form['general']['frontpage_background_default'] = [
      '#type' => 'value',
      '#value' => $config->get('general.frontpage_background'),
    ];
    $form['general']['frontpage_background_upload'] = [
      '#type' => 'managed_file',
      '#title' => 'Avalehe taustapilt',
      '#description' => 'Kuvatakse avalehe päises. Lubatud vormingud on .jpg, .jpeg või .png. Minimaalne vajalik laius on 2800px ja minimaalne kõrgus on 800px.',
      '#default_value' =>  [$config->get('general.frontpage_background')],
      '#upload_location' => 'public://frontpage_background',
      '#upload_validators' => [
        'file_validate_image_resolution' => ['0', '2800x800'],
        'file_validate_extensions' => [
          'jpg jpeg png'
        ],
      ],
    ];



    ##################################################### Avalehe kiirlingid #################################################
    $form['frontpage_quick_links'] = [
      '#type' => 'details',
      '#title' => 'Avalehe kiirlingid',
      '#description' => 'Avalehe kiirlinkide sisestamise ja muutmise andmeplokk, kuhu saab lisada kuni 8 veebilehe sisemist või
       veebilehelt välja suunavat linki, mis märgistatakse vastava ikooniga. Lingi lisamiseks tuleb sisestada nii selle väljakuvatav nimi kui ka link.
       Lingi nimetus peaks olema võimalikult lühike ja konkreetne, võimalusel ainult 1 sõna. Ei soovita üle 2 sõna.
       Kui on soov lisada veebilehe sisemist linki, siis alustage soovitud lehekülje pealkirja trükkimist
       "Sisemine link" väljale ning süsteem pakub sobivaid linke. Lingi valimiseks klõpsake sellel. Välise lingi puhul kopeerige
       kogu veebilehe aadress algusega https:// või http:// ja lisage see "Väline veebilink" väljale. Linkide järjekorra muutmiseks minge rea alguses olevale ikoonile ja
       lohistage rida soovitud kohta. Muudatuste salvestamiseks tuleb vajutada "Salvesta seadistus" nuppu.',
      '#group' => 'tabs',
    ];
    $form['frontpage_quick_links']['fp_quick_links_table'] = [
      '#type' => 'table',
      '#header' => [
        'Veebilingi väljakuvatav nimi',
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
    for ($i = 0; $i < 8; $i++) {
      $j = $i + 1;
      // TableDrag: Mark the table row as draggable.
      $form['frontpage_quick_links']['fp_quick_links_table'][$i]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured
      // weight.
      $form['frontpage_quick_links']['fp_quick_links_table'][$i]['#weight'] = $config->get('frontpage_quick_links.link_weight_' . $j);

      $form['frontpage_quick_links']['fp_quick_links_table'][$i]['link_name'] = [
        '#type' => 'textfield',
        '#title' => 'Veebilingi väljakuvatav nimi ' . $j,
        '#title_display' => 'invisible',
        '#size' => 35,
        '#default_value' => $config->get('frontpage_quick_links.link_name_' . $j),
      ];
      $form['frontpage_quick_links']['fp_quick_links_table'][$i]['link_entity'] = [
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
      if (!empty($config->get('frontpage_quick_links.link_entity_' . $j))) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($config->get('frontpage_quick_links.link_entity_' . $j));
        $form['frontpage_quick_links']['fp_quick_links_table'][$i]['link_entity']['#default_value'] = $node;
      }
      $form['frontpage_quick_links']['fp_quick_links_table'][$i]['link_url'] = [
        '#type' => 'url',
        '#title' => 'Väline veebilink ' . $j,
        '#title_display' => 'invisible',
        '#size' => 30,
        #'#autocomplete_route_name' => 'linkit.autocomplete',
        #'#autocomplete_route_parameters' => [
        #  'linkit_profile_id' => 'default',
        #],
        '#default_value' => $config->get('frontpage_quick_links.link_url_' . $j),
      ];
      // TableDrag: Weight column element.
      $form['frontpage_quick_links']['fp_quick_links_table'][$i]['link_weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => 'link ' . $j]),
        '#title_display' => 'invisible',
        '#default_value' => $config->get('frontpage_quick_links.link_weight_' . $j),
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['table-sort-weight']],
      ];
    }

    ##################################################### Olulisemad kontaktid #################################################
    $form['important_contacts'] = [
      '#type' => 'details',
      '#title' => 'Jaluse olulisemad kontaktid',
      '#description' => 'Jaluse olulisemate kontaktide sisestamise ja muutmise andmeplokk,
      kuhu saab lisada kuni 4 kontakti. Kontaktile määratakse nimi ja sisu, kuid kummagi
      sisestamine ei ole kohustuslik. Linkide järjekorra muutmiseks minge rea alguses
      olevale ikoonile ja lohistage rida soovitud kohta. Muudatuste salvestamiseks tuleb
      vajutada "Salvesta seadistus" nuppu.',
      '#group' => 'tabs',
    ];

    $form['important_contacts']['contacts_table'] = [
      '#type' => 'table',
      '#header' => [
        'Kontakti nimi',
        'Kontakti sisu',
        'Kaal'
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
    ];
    for ($i = 0; $i < 4; $i++) {
      $j = $i + 1;

      $form['important_contacts']['contacts_table'][$i]['#attributes']['class'][] = 'draggable';
      $form['important_contacts']['contacts_table'][$i]['#weight'] = $config->get('important_contacts.weight_' . $j);

      $form['important_contacts']['contacts_table'][$i]['name'] = [
        '#type' => 'textfield',
        '#title' => 'Kontakti nimi '. $j,
        '#title_display' => 'invisible',
        '#size' => 35,
        '#default_value' => $config->get('important_contacts.name_' . $j),
      ];
      $form['important_contacts']['contacts_table'][$i]['body'] = [
        '#type' => 'textfield',
        '#title' => 'Kontakti sisu '. $j,
        '#title_display' => 'invisible',
        '#size' => 35,
        '#default_value' => $config->get('important_contacts.body_' . $j),
      ];
      // TableDrag: Weight column element.
      $form['important_contacts']['contacts_table'][$i]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => 'Link ' . $j]),
        '#title_display' => 'invisible',
        '#default_value' => $config->get('important_contacts.weight_' . $j),
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['table-sort-weight']],
      ];
    }
    ##################################################### Sotsiaalmeedia lingid #################################################
    $form['footer_socialmedia_links'] = [
      '#type' => 'details',
      '#title' => 'Jaluse sotsiaalmeedia lingid',
      '#description' => 'Jaluse sotsiaalmeedia linkide sisestamise ja muutmise andmeplokk, kuhu saab lisada kuni 9
       sotsiaalmeedia konto veebilink. Lingi lisamiseks tuleb sisestada selle ikoon, väljakuvatav nimi ja veebilink.
       Välise lingi puhul kopeerige kogu veebilehe aadress algusega https:// või http://. Linkide järjekorra muutmiseks minge rea alguses olevale ikoonile ja
       lohistage rida soovitud kohta. Muudatuste salvestamiseks tuleb vajutada "Salvesta seadistus" nuppu.',
      '#group' => 'tabs',
    ];
    $form['footer_socialmedia_links']['socialmedia_table'] = [
      '#type' => 'table',
      '#header' => [
        'Sotsiaalmeedia lingi ikoon',
        'Sotsiaalmeedia lingi nimetus',
        'Sotsiaalmeedia konto veebilink',
        'Kaal'
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
    ];
    for ($i = 0; $i < 9; $i++) {
      $j = $i + 1;
      $form['footer_socialmedia_links']['socialmedia_table'][$i]['#attributes']['class'][] = 'draggable';
      $form['footer_socialmedia_links']['socialmedia_table'][$i]['#weight'] = $config->get('footer_socialmedia_links.link_weight_' . $j);

      $form['footer_socialmedia_links']['socialmedia_table'][$i]['link_icon'] = [
        '#type' => 'select',
        '#title' => 'Sotsiaalmeedia lingi ikoon ' . $j,
        '#title_display' => 'invisible',
        '#options' => [
          'mdi-blogger' => 'Blogger',
          'mdi-facebook' => 'Facebook',
          'mdi-instagram' => 'Instagram',
          'mdi-linkedin' => 'LinkedIn',
          'mdi-twitter' => 'Twitter',
          'mdi-vimeo' => 'Vimeo',
          'mdi-vk' => 'VKontakte',
          'mdi-youtube' => 'Youtube',
          'mdi-widgets' => $this->t('Other'),
        ],
        '#empty_option' => $this->t('- Select -'),
        '#default_value' => $config->get('footer_socialmedia_links.link_icon_' . $j),
      ];
      $form['footer_socialmedia_links']['socialmedia_table'][$i]['link_name'] = [
        '#type' => 'textfield',
        '#title' => 'Sotsiaalmeedia lingi nimetus ' . $j,
        '#title_display' => 'invisible',
        '#size' => 35,
        '#default_value' => $config->get('footer_socialmedia_links.link_name_' . $j),
      ];
      $form['footer_socialmedia_links']['socialmedia_table'][$i]['link_url'] = [
        '#type' => 'url',
        '#title' => 'Sotsiaalmeedia konto veebilink ' . $j,
        '#title_display' => 'invisible',
        '#size' => 35,
        '#default_value' => $config->get('footer_socialmedia_links.link_url_' . $j),
      ];
      $form['footer_socialmedia_links']['socialmedia_table'][$i]['link_weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => 'link ' . $j]),
        '#title_display' => 'invisible',
        '#default_value' => $config->get('footer_socialmedia_links.link_weight_' . $j),
        '#attributes' => ['class' => ['table-sort-weight']],
      ];
    }
    ##################################################### Jaluse kiirlingid #################################################
    $form['footer_quick_links'] = [
      '#type' => 'details',
      '#title' => 'Jaluse kiirlingid',
      '#description' => 'Jaluse kiirlinkide sisestamise ja muutmise andmeplokk, kuhu saab lisada kuni 8 veebilehe sisemist või
       veebilehelt välja suunavat linki, mis märgistatakse vastava ikooniga. Lingi lisamiseks tuleb sisestada nii selle väljakuvatav nimi kui ka link.
       Kui on soov lisada veebilehe sisemist linki, siis alustage soovitud lehekülje pealkirja trükkimist
       "Sisemine link" väljale ning süsteem pakub sobivaid linke. Lingi valimiseks klõpsake sellel. Välise lingi puhul kopeerige
       kogu veebilehe aadress algusega https:// või http:// ja lisage see "Väline veebilink" väljale. Linkide järjekorra muutmiseks minge rea alguses olevale ikoonile ja
       lohistage rida soovitud kohta. Muudatuste salvestamiseks tuleb vajutada "Salvesta seadistus" nuppu.',
      '#group' => 'tabs',
    ];
    $form['footer_quick_links']['quick_links_table'] = [
      '#type' => 'table',
      '#header' => [
        'Veebilingi väljakuvatav nimi',
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
    for ($i = 0; $i < 8; $i++) {
      $j = $i + 1;
      // TableDrag: Mark the table row as draggable.
      $form['footer_quick_links']['quick_links_table'][$i]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured
      // weight.
      $form['footer_quick_links']['quick_links_table'][$i]['#weight'] = $config->get('footer_quick_links.link_weight_' . $j);

      $form['footer_quick_links']['quick_links_table'][$i]['link_name'] = [
        '#type' => 'textfield',
        '#title' => 'Veebilingi väljakuvatav nimi ' . $j,
        '#title_display' => 'invisible',
        '#size' => 35,
        '#default_value' => $config->get('footer_quick_links.link_name_' . $j),
      ];
      $form['footer_quick_links']['quick_links_table'][$i]['link_entity'] = [
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
      if (!empty($config->get('footer_quick_links.link_entity_' . $j))) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($config->get('footer_quick_links.link_entity_' . $j));
        $form['footer_quick_links']['quick_links_table'][$i]['link_entity']['#default_value'] = $node;
      }
      $form['footer_quick_links']['quick_links_table'][$i]['link_url'] = [
        '#type' => 'url',
        '#title' => 'Väline veebilink ' . $j,
        '#title_display' => 'invisible',
        '#size' => 30,
        #'#autocomplete_route_name' => 'linkit.autocomplete',
        #'#autocomplete_route_parameters' => [
        #  'linkit_profile_id' => 'default',
        #],
        '#default_value' => $config->get('footer_quick_links.link_url_' . $j),
      ];
      // TableDrag: Weight column element.
      $form['footer_quick_links']['quick_links_table'][$i]['link_weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => 'link ' . $j]),
        '#title_display' => 'invisible',
        '#default_value' => $config->get('footer_quick_links.link_weight_' . $j),
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['table-sort-weight']],
      ];
    }

    ##################################################### Jaluse vabatekstiala #################################################
    $form['footer_free_text_area'] = [
      '#type' => 'details',
      '#title' => 'Jaluse vabatekstiala',
      '#description' => 'Jaluse vabateksti sisestamise ja muutmise andmeplokk, kuhu on võimalik lisada pealkiri ja sisu.
      Pealkiri on kohustuslik, kui vabateksti sisu on sisestatud. Sisu on kohustuslik, kui vabateksti pealkiri on sisestatud.',
      '#group' => 'tabs',
    ];
    $form['footer_free_text_area']['free_text_name'] = [
      '#type' => 'textfield',
      '#title' => 'Vabatekstiala pealkiri',
      '#default_value' =>  $config->get('footer_free_text_area.name'),
    ];
    $form['footer_free_text_area']['free_text_body'] = [
      '#type' => 'textarea',
      '#title' => 'Vabatekstiala sisu',
      '#default_value' =>  $config->get('footer_free_text_area.body'),
    ];
    ##################################################### Jaluse vabatekstiala #################################################
    $form['footer_copyright'] = [
      '#type' => 'details',
      '#title' => 'Jaluse kasutusõiguste märkus',
      '#description' => 'Jaluse kasutusõiguste märkuse sisestamise ja muutmise andmeplokk, kuhu on võimalik lisada lehe kasutusõiguste tekst.',
      '#group' => 'tabs',
    ];
    $form['footer_copyright']['footer_copyright_name'] = [
      '#type' => 'textfield',
      '#title' => 'Kasutusõiguste märkus',
      '#default_value' =>  $config->get('footer_copyright.name'),
    ];
    ##################################################### Suunamised ja muutujad #################################################
    $form['variables'] = [
      '#type' => 'details',
      '#title' => 'Suunamised ja muutujad',
      '#description' => 'Siin andmeplokis on muutujad, mida administraator saab lehte seadistades täpsustada. Näiteks saab anda uudise tüübile "Meie lugu" uue kuvatava nimetuse nt "Kooli blogi". Samuti saab siin plokis seadistada lehte, kuhu suunatakse avalehel tunniplaani nuppu vajutades.',
      '#group' => 'tabs',
    ];
    $form['variables']['news_our_story_name'] = [
      '#type' => 'textfield',
      '#title' => 'Uudise tüübi "Meie lugu" nimetus',
      '#default_value' =>  $config->get('news_our_story.name'),
      '#required' => TRUE,
    ];
    $form['variables']['automatic_generation_academic_year_on'] = [
      '#type' => 'select',
      '#title' => 'Õppeaasta automaatne genereerimine on sisselülitatud',
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#default_value' =>  $config->get('automatic_generation_academic_year.on') ? 1 : 0,
      '#required' => TRUE,
    ];
    $form['variables']['automatic_generation_academic_year_date'] = [
      '#type' => 'textfield',
      '#title' => 'Õppeaasta automaatse genereerimise kuupäev',
      '#default_value' =>  $config->get('automatic_generation_academic_year.date'),
      '#size' => 5,
      '#description' => 'Kuupäeva formaat on "dd.mm.".',
      '#required' => TRUE,
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
    ################################################### Frontpage background save ######################################
    if ($form_state->getValue('frontpage_background_upload')) {
      $background_upload = $form_state->getValue('frontpage_background_upload')[0];
    }
    else {
      $background_upload = 0;
    }
    $this->fileUploadHandle($background_upload, $form_state->getValue('frontpage_background_default'), 'general.frontpage_background');
    ################################################### Other general settings save ######################################
    $this->config('harno_settings.settings')
      ->set('general.frontpage_background_type', $form_state->getValue('frontpage_background_type'))
      ->save();
  }

  /**
   * fileUploadHandle.
   *
   * @param \Drupal\file\Entity\File  $upload_fid
   * @param \Drupal\file\Entity\File  $default_fid
   * @param  harno_settings_schema    $config_var_name
   */
  private function fileUploadHandle ($upload_fid, $default_fid, $config_var_name) {
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
      $this->config('harno_settings.settings')
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
          $file_usage->add($file, 'harno_settings', 'node', 1);
        }
        $this->config('harno_settings.settings')
          ->set($config_var_name, $upload_fid);
      }
    }
  }
}
