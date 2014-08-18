<?php

/**
 * @file
 * Contains \Drupal\faq_pages\Form.
 */

namespace Drupal\faq_pages\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Delete form for a custom FAQ page.
 */
class DeletePageForm extends ConfirmFormBase {
  
  /**
   * The current route matcher service object.
   * 
   * @var \Drupal\Core\Routing\RouteMatchInterface 
   */
  protected $routeMatcher;
  
  /**
   * Constructs a DeletePageForm object.
   * 
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_matcher
   *   The route matcher.
   */
  public function __construct(RouteMatchInterface $route_matcher) {
    $this->routeMatcher = $route_matcher;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match')
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('faq_pages.list');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'faq_page_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this?');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $pageId = $this->routeMatcher->getRawParameter('page');
    drupal_set_message('dssfd');
    $list_page = new \Drupal\Core\Url('faq_pages.list');
    $form_state['redirect'] = array(
      '/admin/config/content/faq/faq-pages'
    );
  }

}