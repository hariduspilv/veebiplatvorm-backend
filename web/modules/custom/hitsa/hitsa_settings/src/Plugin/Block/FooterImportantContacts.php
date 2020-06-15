<?php

namespace Drupal\hitsa_settings\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block that creates the social media block to show in the footer area
 * @Block(
 * 	id = "footer_important_contacts",
 * 	admin_label = @Translation("Footer Important Contacts Block"),
 * 	category = @Translation("Footer Block"),
 * )
 */
class FooterImportantContacts extends BlockBase{
  /**
   * Function to build actual block
   *
   */
  public	function build(){
    $information = 'important_contacts';
    $info = $this-> getInfo($information);
    if (!empty($info)) {
      $build = [];
      $build['#theme'] = 'hitsa_important_contacts_block';
      $build['#data'] = $info;
      return $build;
    }
  }
  public function getInfo($information=null){
    $localconf = \Drupal::service('config.factory')->get('hitsa_settings.settings');
    $conf = $localconf->get($information);
    $names = [
      'name_',
      'body_',
      'weight_',
    ];
    $usable_array = [];
    foreach ($conf as $conf_item => $conf_value) {
      $key = str_replace($names,'',$conf_item);
      $conf_name = str_replace('_'.$key,'',$conf_item);
      if(!empty($conf_value) && $conf_name!='weight'){
        if (filter_var($conf_value, FILTER_VALIDATE_EMAIL)) {
          $usable_array[$key]['type'] = 'email';
        }
        elseif (filter_var($conf_value, FILTER_VALIDATE_URL)) {
          $usable_array[$key]['type'] = 'link';
        }
        else{
          $usable_array[$key]['type'] = 'text';
        }
        $usable_array[$key][$conf_name] = $conf_value;
      }
    }
    if(!empty($usable_array)){
      return $usable_array;
    }

  }
}
