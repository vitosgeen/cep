<?php

namespace Drupal\cep_query\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Cep query edit forms.
 *
 * @ingroup cep_query
 */
class CepQueryForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\cep_query\Entity\CepQuery $entity */
    $form = parent::buildForm($form, $form_state);
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form["url"]['#ajax'] = [
      'callback' => 'ajax_forms_cep_query_url_form_callback',
      'wrapper' => 'message_area_number',
      'method' => 'replace',
    ];
    return $form;
  }

  public function cepQueryUrlChange(array &$form, FormStateInterface $form_state) {
    $form['name']['value'] = md5($form_state->getValue('url'));
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Cep query.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Cep query.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.cep_query.canonical', ['cep_query' => $entity->id()]);
  }

}
