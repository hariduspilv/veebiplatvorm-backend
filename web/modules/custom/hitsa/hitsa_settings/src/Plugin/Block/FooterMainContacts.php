<?php

namespace Drupal\hitsa_settings\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block that creates the social media block to show in the footer area
 * @Block(
 * 	id = "footer_main_contacts_block",
 * 	admin_label = @Translation("Footer General Contacts Block"),
 * 	category = @Translation("Footer Block"),
 * )
 */
class FooterMainContacts extends BlockBase{
  /**
   * Function to build actual block
   *
   */
  public	function build(){
    $build = [];
    $information = 'general';
    $info = $this-> getInfo($information);
    if (!empty($info)) {
      $build['#theme'] = 'hitsa_main_contacts_block';
      $build['#data'] = $info;
    }
    return $build;
  }
  public function getInfo($information=null){
    $localconf = \Drupal::service('config.factory')->get('hitsa_settings.settings');
    $conf = $localconf->get($information);
    $systemconf = \Drupal::service('config.factory')->get('system.site');
    $name = $systemconf->get('name');
    $out = [];
    if (!empty($conf['address'])){
      $out['address'] = $conf['address'];
    }
    if(!empty($conf['phone'])){
      $out['phone'] = $conf['phone'];
    }
    if(!empty($conf['email'])){
      $out['email'] = $conf['email'];
    }
    if(!empty($name)){
      $out['name'] = $name;
    }

    return $out;
  }
}
