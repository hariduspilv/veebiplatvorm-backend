<?php


namespace Drupal\harno_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "hanro_block_more_news",
 *   admin_label = @Translation("More News Block"),
 *   category = @Translation("harno_blocks")
 * )
 */
class MoreNews  extends  BlockBase{
  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_page = \Drupal::request()->attributes->get('node');
    $field_definition = $current_page->get('field_article_type')->getFieldDefinition()->getSetting('allowed_values');
    $article_type = $current_page->get('field_article_type')->first()->value;
    $current_id = $current_page->id();
    $bundle = 'article';
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('nid', $current_id,'!=');
    $query->condition('type', $bundle);
    $query->condition('field_article_type',$article_type);
    $query->sort('created', 'DESC');
    $query->range(0,4);
    $nids = $query->execute();
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $articles = $node_storage->loadMultiple($nids);
    $articles_data = [];
    $articles_data['title'] = t("More news");
    $articles_data['link_name'] = t("Look more");
    $url =  Url::fromRoute('harno_pages.news_page',['article_type['.$article_type.']'=>$article_type,'article_type_mobile['.$article_type.']'=>$article_type])->toString();
    $url = urldecode($url);
    if(!empty($url)){
      $articles_data['link'] = $url;
    }
    if (!empty($articles)){
      $articles_data['items'] = [];
      $i = 0;
      foreach ($articles as $article){
        $title = $article->get('title')->value;
        $created = date('d.m.Y',$article->get('created')->value);
        $author = $article->get('field_author_name')->value;
        $article_link = $article->toLink()->getUrl()->toString();

        $articles_data['items'][$i] = [
          'article_link' => $article_link,
          'title' => $title,
          'tag' => $article_type==1? '': $field_definition[$article_type],
          'created' => $created,
          'author' => $author,
        ];
        if(!empty($article->get('field_one_image'))){
          $media_image = $article->get('field_one_image')->entity->get('field_media_image')->getValue();
          $articles_data['items'][$i]['image'] = $media_image[0];
        }
        $i++;
      }
    }
    $build['#data'] = $articles_data;
    $build['#theme'] = 'harno-news-block';
    return $build;
  }
}
