<?php

namespace Drupal\cep_query\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CepQueryTypeForm.
 */
class CepQueryTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $cep_query_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $cep_query_type->label(),
      '#description' => $this->t("Label for the Cep query type."),
      '#required' => TRUE,
    ];

    $form['tquery'] = [
      '#title' => $this->t('Type'),
      '#type' => 'select',
      '#options' => $this->cepQueryTypes(),
      '#empty_option' => $this->t('- Select a proxy type -'),
      '#default_value' => $cep_query_type->tquery(),
      '#description' => $this->t("External url source for the Cep proxy type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $cep_query_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\cep_query\Entity\CepQueryType::load',
      ],
      '#disabled' => !$cep_query_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cep_query_type = $this->entity;
    $status = $cep_query_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Cep query type.', [
          '%label' => $cep_query_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Cep query type.', [
          '%label' => $cep_query_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($cep_query_type->toUrl('collection'));
  }


  private function cepQueryTypes($type = NULL) {
    $types = [
      'phantomjs' => 'Phantom JS',
      'curl' => 'Curl',
      'filegc' => 'File get content',
      'chromehl' => 'Chrome Headless',
      'available' => 'Available',
    ];
    if(isset($types[$type])){
      return $types[$type];
    }
    return $types;
  }

}
