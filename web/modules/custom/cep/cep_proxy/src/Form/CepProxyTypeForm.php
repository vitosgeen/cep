<?php

namespace Drupal\cep_proxy\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CepProxyTypeForm add and edit form proxy type.
 */
class CepProxyTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $cep_proxy_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $cep_proxy_type->label(),
      '#description' => $this->t("Label for the CEP proxy type."),
      '#required' => TRUE,
    ];

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('External url'),
      '#maxlength' => 255,
      '#default_value' => $cep_proxy_type->url(),
      '#description' => $this->t("External url source for the CEP proxy type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $cep_proxy_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\cep_proxy\Entity\CepProxyType::load',
      ],
      '#disabled' => !$cep_proxy_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cep_proxy_type = $this->entity;
    $status = $cep_proxy_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label CEP proxy type.', [
          '%label' => $cep_proxy_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label CEP proxy type.', [
          '%label' => $cep_proxy_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($cep_proxy_type->toUrl('collection'));
  }

}
