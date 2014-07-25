<?php

/**
 * @file
 * Contains \Drupal\faq_pages\Controller\FaqPagesController.
 */

namespace Drupal\faq_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\faq\FaqHelper;
use Drupal\faq_pages\FaqPageViewModel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller routines for FAQ Pages routes.
 */
class FaqPagesController extends ControllerBase {

  /**
   * Renders listing table from the existing FAQ pages.
   * 
   * @return array Render array with the FAQ pages.
   */
  public function listPages() {
    $vocabs = FaqHelper::faqRelatedVocabularies();
    if (empty($vocabs)) {
      drupal_set_message($this->t('You need to link at least one vocabulary to the FAQ content type.'), 'error');
      return;
    }

    $build = array();

    $query = db_select('faq_pages', 'fp');
    $query->fields('fp', array('sid', 'title', 'url', 'description'));
    $result = $query->execute()->fetchAllAssoc('sid');

    $rows = array();
    foreach ($result as $key => $row) {
      $rows[$key] = array($row->title, $row->description);
      $rows[$key][] = l('faq/' . $row->url, 'faq/' . $row->url);
      $actions = array(
        '#type' => 'dropbutton',
        '#links' => array(
          array(
            'href' => '/faq/' . $key . '/edit',
            'title' => $this->t('Edit'),
          ),
          array(
            'href' => '/faq/' . $key . '/delete',
            'title' => $this->t('Delete'),
          ),
        ),
      );
      $rows[$key][] = drupal_render($actions);
    }

    $build['new'] = array(
      '#type' => 'markup',
      '#markup' => l($this->t('Add new FAQ page'), '/admin/config/content/faq/new-page', array('attributes' => array('class' => array('button button--primary button--small button-action')))) . '<br /> <br />',
    );

    $build['table'] = array(
      '#type' => 'table',
      '#header' => array($this->t('Title'), $this->t('Description'), $this->t('Path'), $this->t('Operations')),
      '#rows' => $rows,
    );

    return $build;
  }

  public function editPage() {
    $vocabs = FaqHelper::faqRelatedVocabularies();
    $query = db_select('taxonomy_term_data', 'ttd');
    $query->fields('ttd', array('tid', 'name', 'vid'));
    $query->condition('vid', array_keys($vocabs));
    $terms = $query->execute()->fetchAllAssoc('tid');
    
    $build = array();
    $build['#title'] = 'haha szerkesztünk';
    $build['#attached']['library'][] = 'faq_pages/faq_page-edit';
    
    
    return $build;
  }

  /**
   * Shows the given FAQ-page.
   * 
   * @param int $page
   *   Identifier of the custom FAQ page.
   * @return array Render array with the content.
   */
  public function viewPage($page) {

    if (!FaqPageViewModel::isExists($page)) {
      throw new NotFoundHttpException();
    }
    $model = new FaqPageViewModel($page);

    $build = array();
    $build['#title'] = $model->getTitle();
    
    $build['#attached']['library'][] = 'faq_pages/faq_page-scripts';

    $blocks_render = array(
      '#theme' => 'faq_pages_blocks',
      '#blocks' => $model->getBlocks(),
    );
    $blocks = drupal_render($blocks_render);

    $questions_render = array(
      '#theme' => 'faq_pages_questions',
      '#topics' => $model->getTopics(),
    );
    $questions = drupal_render($questions_render);

    $content = array(
      '#theme' => 'faq_pages_page',
      '#description' => $model->getDescription(),
      '#blocks' => $blocks,
      '#questions' => $questions,
    );
    $build['#type'] = 'markup';
    $build['#markup'] = drupal_render($content);
    return $build;
  }

}
