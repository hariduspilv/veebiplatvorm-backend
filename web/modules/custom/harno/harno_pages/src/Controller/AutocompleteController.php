<?php
namespace Drupal\harno_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
class AutocompleteController extends ControllerBase {
  public function handleAutocomplete($request){
    $matches['kakaa']='kakaaa';
    return new JsonResponse($matches);;
  }
}
