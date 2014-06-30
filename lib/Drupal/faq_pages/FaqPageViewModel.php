<?php

/**
 * @file
 * Contains \Drupal\faq_pages\FaqPageViewModel.
 */

namespace Drupal\faq_pages;

use Drupal\node\Entity\Node;

/**
 * Viewmodel containing all data to a view page.
 */
class FaqPageViewModel {
  
  /**
   * An array of nodes relating to this FAQ page.
   * 
   * @var array 
   */
  protected $nodes;
  
  /**
   * Identifier of the this FAQ page.
   * 
   * @var int
   */
  protected $sid;
  
  /**
   * Raw array of the database query's result.
   * 
   * @var array
   */
  protected $data;
  
  /**
   * Constructs the FaqPageViewModel object.
   * 
   * @param int $sid Identifier of the FAQ page.
   */
  public function __construct($sid) {
    $this->sid = $sid;
    $this->data = $this->queryDatabase();
    $this->nodes = Node::loadMultiple(array_keys($this->data));
  }
  
  /**
   * Tells that the given idenfifier is valid or not.
   * 
   * @param int $sid
   *   Identifier of a FAQ page.
   * @return boolean TRUE if a FAQ page exists with this id, else FALSE.
   */
  public static function isExists($sid) {
    $query = db_select('faq_sites', 'fs');
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
    $query = db_select('faq_sites', 'fs');
    $query->join('faq_sites_blocks', 'fsb', 'fsb.sid = fs.sid');
    $query->join('faq_sites_topics', 'fst', 'fst.bid = fsb.bid');
    $query->join('faq_sites_terms', 't', 't.toid = fst.toid');
    $query->join('taxonomy_index', 'ti', 'ti.tid = t.tid');
    $query->fields('fs', array('sid', 'title', 'description'));
    $query->fields('fsb', array('bid', 'name'));
    $query->fields('fst', array('toid', 'name', 'description'));
    $query->fields('t', array('tid'));
    $query->fields('ti', array('nid'));
    $query->condition('fs.sid', $this->sid);
    
    return $query->execute()->fetchAllAssoc('nid');
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
   * Returns the raq data from the database.
   * 
   * @return array
   */
  public function getRaqData() {
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
  
}