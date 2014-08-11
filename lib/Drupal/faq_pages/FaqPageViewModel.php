<?php

/**
 * @file
 * Contains \Drupal\faq_pages\FaqPageViewModel.
 */

namespace Drupal\faq_pages;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Viewmodel containing all data to a view page.
 */
class FaqPageViewModel {

  /**
   * Identifier of the this FAQ page.
   * 
   * @var int
   */
  protected $sid;

  /**
   * An array of nodes relating to this FAQ page.
   * 
   * @var array 
   */
  protected $nodes;

  /**
   * An array of terms relating to this FAQ page.
   * 
   * @var array
   */
  protected $terms;

  /**
   * Raw array of the database query's result.
   * 
   * @var array
   */
  protected $data;

  /**
   * Constructs the FaqPageViewModel object. Don't forget to check
   * the $sid with FaqPageViewModel::isExists($sid) method first!
   * 
   * @param int $sid Identifier of the FAQ page.
   * @param boolean $loadNodes Wether load the nodes or not. This is useful to
   *   edit the faq pages - we don't need to load nodes for this purpose.
   */
  public function __construct($sid, $loadNodes = TRUE) {
    $this->sid = $sid;
    $this->data = $this->queryDatabase();
    $this->terms = $this->loadTerms();
    if ($loadNodes) {
      $this->nodes = $this->loadNodes();
    }
  }

  /**
   * Tells that the given idenfifier is valid or not. We don't want to join
   * five table if the given sid doesn't exists! Always check first with this
   * function before create new instance of this class!
   * 
   * @param int $sid
   *   Identifier of a FAQ page.
   * @return boolean TRUE if a FAQ page exists with this id, else FALSE.
   */
  public static function isExists($sid) {
    $query = db_select('faq_pages', 'fs');
    $query->fields('fs', array('sid'));
    $query->condition('fs.sid', $sid);
    $result = $query->execute()->fetchCol('sid');

    if (empty($result)) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Query the necessary data from the database. This is a private method
   * called from the constructor to initialize the object.
   * 
   * @return array Raw data of the FAQ page from the database.
   */
  private function queryDatabase() {
    //TODO: cache this query
    $query = db_select('faq_pages', 's');
    $query->leftJoin('faq_pages_blocks', 'b', 'b.sid = s.sid');
    $query->leftJoin('faq_pages_topics', 'topic', 'topic.bid = b.bid');
    $query->leftJoin('faq_pages_terms', 't', 't.toid = topic.toid');
    $query->leftJoin('taxonomy_index', 'ti', 'ti.tid = t.tid');
    $query->fields('s', array('sid', 'url', 'title', 'description'));
    $query->fields('b', array('bid', 'name'));
    $query->fields('topic', array('toid', 'name', 'description'));
    $query->fields('t', array('tid'));
    $query->fields('ti', array('nid'));
    $query->condition('s.sid', $this->sid);

    return $query->execute()->fetchAll();
  }

  /**
   * Process the node id-s from the raw data and loads the related nodes.
   * This is a private method called from the constructor 
   * to initialize the object.
   * 
   * @return array Array of the related node objects.
   */
  private function loadNodes() {
    $nids = array();
    foreach ($this->data as $row) {
      if (!empty($row->nid) && !in_array($row->nid, $nids)) {
        $nids[] = $row->nid;
      }
    }
    return Node::loadMultiple($nids);
  }

  /**
   * Process the term id-s from the raw data and loads the related terms.
   * This is a private method called from the constructor 
   * to initialize the object.
   * 
   * @return array 
   */
  private function loadTerms() {
    $tids = array();
    foreach ($this->data as $row) {
      if (!empty($row->tid) && !in_array($row->tid, $tids)) {
        $tids[] = $row->tid;
      }
    }
    return Term::loadMultiple($tids);
  }

  /**
   * Gives back the node with the given id if exists.
   * 
   * @param int $nid
   *   Identifier of the node.
   * @return Node/bool
   */
  private function getNode($nid) {
    foreach ($this->nodes as $node) {
      if ($node->id() == $nid) {
        return $node;
      }
    }
    return FALSE;
  }

  /**
   * Gives back the term with the given id if exists.
   * 
   * @param int $tid
   *   Identifier of the term.
   * @return Term/bool
   */
  private function getTerm($tid) {
    foreach ($this->terms as $term) {
      if ($term->id() == $tid) {
        return $term;
      }
    }
    return FALSE;
  }

  /**
   * Returns the array of nodes relating to this FAQ page.
   * 
   * @return array
   */
  public function getNodes() {
    return $this->nodes;
  }

  /**
   * Returns the array of terms relating to this FAQ page.
   * 
   * @return array
   */
  public function getTerms() {
    return $this->terms;
  }

  /**
   * Returns the raq data from the database.
   * 
   * @return array
   */
  public function getRawData() {
    return $this->data;
  }

  /**
   * Returns the title of the FAQ page.
   * 
   * @return string
   */
  public function getTitle() {
    return array_values($this->data)[0]->title;
  }

  /**
   * Returns the description of the FAQ page.
   * 
   * @return string
   */
  public function getDescription() {
    return array_values($this->data)[0]->description;
  }

  /**
   * Returns data from the topics, this can be passed to rendering.
   * 
   * @return array Array of the current topics
   */
  public function getTopics() {
    $topics = array();

    foreach ($this->data as $row) {
      $topics[$row->toid]['toid'] = $row->toid;
      $topics[$row->toid]['title'] = $row->topic_name;
      $topics[$row->toid]['description'] = $row->topic_description;
      if (!isset($topics[$row->toid]['terms'][$row->tid])) {
        $topics[$row->toid]['terms'][$row->tid]['term'] = $this->getTerm($row->tid);
      }
      $topics[$row->toid]['terms'][$row->tid]['nodes'][]['node'] = $this->getNode($row->nid);
    }

    return $topics;
  }

  /**
   * Returns the blocks of the FAQ page as a structured array.
   * 
   * @return array Array of the blocks.
   */
  public function getBlocks() {
    $blocks = array();

    foreach ($this->data as $row) {
      $blocks[$row->bid]['id'] = $row->bid;
      $blocks[$row->bid]['title'] = $row->name;
      $blocks[$row->bid]['topics'][$row->toid]['id'] = $row->toid;
      $blocks[$row->bid]['topics'][$row->toid]['name'] = $row->topic_name;
      $blocks[$row->bid]['topics'][$row->toid]['description'] = $row->topic_description;
    }

    return $blocks;
  }

  public function getEditModel() {
    $model = array();

    $model['id'] = $this->data[0]->sid;
    $model['title'] = $this->data[0]->title;
    $model['url'] = $this->data[0]->url;
    $model['description'] = $this->data[0]->description;
    foreach ($this->data as $row) {
      if (!is_null($row->bid)) {
        $model['blocks'][$row->bid]['id'] = $row->bid;
        $model['blocks'][$row->bid]['name'] = $row->name;
        if (!is_null($row->toid)) {
          $model['blocks'][$row->bid]['topics'][$row->toid]['id'] = $row->toid;
          $model['blocks'][$row->bid]['topics'][$row->toid]['name'] = $row->topic_name;
          $model['blocks'][$row->bid]['topics'][$row->toid]['description'] = $row->topic_description;
          if (!is_null($row->tid)) {
            $term = $this->getTerm($row->tid);
            $model['blocks'][$row->bid]['topics'][$row->toid]['terms'][$row->tid] = array('id' => $term->id(), 'name' => $term->getName());
          }
        }
      }
    }

    return $model;
  }

  public function saveEditModel(array $model) {
    $saved = $this->getEditModel();

    if (is_null($model['id'])) {
      $pageId = db_insert('faq_pages')
          ->fields(array(
            'title' => $model['title'],
            'url' => $model['url'],
            'description' => $model['description'],
          ))->execute();
    }
    else {
      $pageId = $model['id'];
      db_update('faq_pages')
        ->fields(array(
          'title' => $model['title'],
          'url' => $model['url'],
          'description' => $model['description'],
        ))
        ->condition('sid', $pageId, '=')
        ->execute();
    }
    foreach ($model['blocks'] as $block) {
      if (is_null($block['id'])) {
        //insert
        $blockId = db_insert('faq_pages_blocks')
            ->fields(array(
              'sid' => $pageId,
              'name' => $block['name'],
            ))->execute();
      }
      else {
        //update
        $blockId = $block['id'];
        db_update('faq_pages_blocks')
          ->fields(array(
            'sid' => $pageId,
            'name' => $block['name'],
          ))
          ->condition('bid', $blockId, '=')
          ->execute();
      }
      foreach ($block['topics'] as $topic) {
        if (is_null($topic['id'])) {
          //insert
          $topicId = db_insert('faq_pages_topics')
              ->fields(array(
                'bid' => $blockId,
                'name' => $topic['name'],
                'description' => $topic['description'],
              ))->execute();
        }
        else {
          //update
          $topicId = $topic['id'];
          db_update('faq_pages_topics')
            ->fields(array(
              'bid' => $blockId,
              'name' => $topic['name'],
              'description' => $topic['description'],
            ))->condition('toid', $topicId, '=')
            ->execute();
        }
      }
    }

//    db_merge('faq_pages')
//      ->key(array('sid' => 5))
//      ->fields(array(
//        'title' => 'db_merge próbafaq',
//        'url' => 'kukukuk',
//        'description' => 'asdsddsds',
//      ))->execute();
    var_dump($original);
    //save the faq page
//    db_merge('faq_pages')
//      ->key(array('sid' => $model['id']))
//      ->fields(array(
//        'title' => $model['title'],
//        'url' => $model['url'],
//        'description' => $model['description'],
//      ))->execute();
//    
  }

}
