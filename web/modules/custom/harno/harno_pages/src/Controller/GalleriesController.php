<?php
  namespace Drupal\harno_pages\Controller;

use Drupal\Core\Controller\ControllerBase;

class GalleriesController extends ControllerBase
  {
    public function index(){
      $build = [];
      $build['#theme'] = 'galleries-page';

      return $build;
    }
  }

