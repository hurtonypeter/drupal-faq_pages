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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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
      $rows[$key][] = l($row->url, $row->url);
      $actions = array(
        '#type' => 'dropbutton',
        '#links' => array(
          array(
            'href' => '/faq/' . $key . '/edit',
            'title' => $this->t('Edit'),
          ),
          array(
            'href' => '/admin/config/content/faq/faq-pages/delete/' . $key,
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

  /**
   * Edit the FAQ page with the given id. If no ID specified,
   * create an empty model for a new page.
   * 
   * @param integer $page
   *   Identigier of the page. Use null to create new page.
   * @return array Array for the builder.
   */
  public function editPage($page = NULL) {
    $vocabs = FaqHelper::faqRelatedVocabularies();
    $vids = array();
    foreach ($vocabs as $vocab) {
      $vids[] = $vocab->id();
    }

    $query = db_select('taxonomy_term_data', 'td');
    $query->join('taxonomy_term_field_data', 'ttfd', 'td.tid = ttfd.tid');
    $query->condition('td.vid', $vids, 'IN');
    $query->fields('ttfd', array('tid', 'name'));
    $terms = $query->execute()->fetchAllAssoc('tid');

    if ($page == NULL) {
      $faq_page = array(
        'id' => null,
        'title' => '',
        'url' => '',
        'description' => '',
        'blocks' => array(),
      );
    }
    else {
      $faq_page_model = new FaqPageViewModel($page, FALSE);
      $faq_page = $faq_page_model->getEditModel();
    }

    $build = array();
    $build['#attached']['library'][] = 'faq_pages/faq_page-edit';
    $build['#attached']['js'] = array(
      array(
        'data' => array(
          'term_model' => $terms,
          'edit_model' => $faq_page,
        ),
        'type' => 'setting'
      )
    );

    $content = array(
      '#theme' => 'faq_page_edit',
    );
    $build['#type'] = 'markup';
    $build['#markup'] = drupal_render($content);

    return $build;
  }

  /**
   * Save the given FAQ page asyncronously.
   * 
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The frameworks Request object, containing the json model.
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Respone with json to the angular app.
   */
  public function savePage(Request $request) {
    $content = $request->getContent();

    if (!empty($content)) {
      // 2nd param to get as array
      try {
        $data = json_decode($content, TRUE);

        $model = new FaqPageViewModel($data['id'], FALSE);
        $model->saveEditModel($data);
        $model->reloadRaw();

        return new JsonResponse(array(
          'error' => false,
          'data' => $model->getEditModel(),
        ));
      }
      catch (Exception $exc) {
        return new JsonResponse(array(
          'error' => true,
          'errorMessage' => 'There is something wrong.',
        ));
      }
    }

    return new JsonResponse(array(
      'error' => true,
      'errorMessage' => 'There is something wrong',
    ));
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

  /**
   * Deletes the FAQ page with the given ID.
   * 
   * @param integer $page The FAQ page id.
   */
  public function deletePage($page) {
    
  }

}
