<?php

namespace Drupal\hitsa_settings\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block that creates the social media block to show in the footer area
 * @Block(
 * 	id = "footer_free_text_area",
 * 	admin_label = @Translation("Footer Free Form Text Area"),
 * 	category = @Translation("Footer Block"),
 * )
 */
class FooterFreeTextArea extends BlockBase{
  /**
   * Function to build actual block
   *
   */
  public	function build(){
    $information = 'footer_free_text_area';
    $info = $this-> getInfo($information);
    if (!empty($info)) {
      $build = [];
      $build['#theme'] = 'hitsa_footer_free_text_block';
      $build['#data'] = $info;
      return $build;
    }
  }
  public function getInfo($information=null){
    $localconf = \Drupal::service('config.factory')->get('hitsa_settings.settings');
    $conf = $localconf->get($information);
    $out = [];
    if (!empty($conf['name'])){
      $out['name'] = $conf['name'];
    }
    if(!empty($conf['body'])){
      $out['body'] = $conf['body'];
    }
    return $out;
  }
}
