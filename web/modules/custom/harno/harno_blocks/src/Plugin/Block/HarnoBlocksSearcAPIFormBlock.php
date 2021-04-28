<?php

namespace Drupal\harno_blocks\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Search API search form' block.
 *
 * @Block(
 *   id = "harno_blocks_search_api_form_block",
 *   admin_label = @Translation("Search API form"),
 *   category = @Translation("harno_blocks")
 * )
 */
class HarnoBlocksSearcAPIFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'search content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\harno_blocks\Form\HarnoBlocksSearchAPIForm');
  }

}
