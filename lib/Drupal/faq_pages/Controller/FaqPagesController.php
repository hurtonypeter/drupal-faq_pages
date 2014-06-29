<?php

/**
 * @file
 * Contains \Drupal\faq_pages\Controller\FaqPagesController.
 */

namespace Drupal\faq_pages\Controller;

use Drupal\Core\Controller\ControllerBase;

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
    $build = array();
    
    $query = db_select('faq_sites', 'fs');
    $query->fields('fs', array('sid', 'title', 'url', 'description'));
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
      '#markup' => l($this->t('Add new FAQ page'), '/admin/config/content/faq/new-page', array('attributes' => array('class' => array('button button--primary button--small button-action')))).'<br /> <br />',
    );
    
    $build['table'] = array(
      '#type' => 'table',
      '#header' => array($this->t('Title'), $this->t('Path'), $this->t('Description'), $this->t('Operations')),
      '#rows' => $rows,
    );
    
    return $build;
  }

  public function editPage() {
    
  }

  public function viewPage() {
    
  }

}
