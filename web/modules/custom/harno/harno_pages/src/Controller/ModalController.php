<?php

/**
 * @file
 * ModalController class.
 */

namespace Drupal\harno_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

class ModalController extends ControllerBase {

  public function index($type = null, $id = null) {

    // TODO:
    $build = [];
    if($type) {
      if($type == 'content') {
        if(is_numeric($id)) {
          $content = Node::load($id);
          $build['#theme'] = 'contact-modal';
          $build['#title'] = t('Contact card');
          $build['#content'] = $content;
          $build['#cache'] = [
            'conttexts' => ['url.query_args'],
            'tags' => ['node_type:worker'],
          ];
        }
      }
      elseif ($type == 'gallery'){
        if(is_numeric($id)) {
          $content = Node::load($id);
          $build['#theme'] = 'picture-modal';
          $build['#content'] = $content;
          $build['#cache'] = [
            'conttexts' => ['url.query_args'],
            'tags' => ['node_type:gallery'],
          ];
        }
      }
      else{
        //for form etc
      }
    }
    return $build;
  }
}
