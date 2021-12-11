<?php

namespace Drupal\cep_proxy\Form;

use Drupal\cep_proxy\Entity\CepProxy;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete CEP proxy type entities.
 */
class CepProxyTypeDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.cep_proxy_type.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $type_proxy = $this->entity->getIdCepProxyType();
    $entity = CepProxy::create([
      'type' => $type_proxy,
    ]);
    $param = [
      'type' => $type_proxy,
    ];
    $ps = $entity->getEntities($param);
    foreach ($ps as $p) {
      $entity->load($p)->delete();
    }

    $this->entity->delete();

    $this->messenger()->addMessage(
      $this->t('content @type: deleted @label.', [
        '@type' => $this->entity->bundle(),
        '@label' => $this->entity->label(),
      ])
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
