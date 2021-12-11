<?php

namespace Drupal\cep_parser\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the cep_parser entity edit forms.
 */
class CepParserForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New cep_parser %label has been created.', $message_arguments));
      $this->logger('cep_parser')->notice('Created new cep_parser %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The cep_parser %label has been updated.', $message_arguments));
      $this->logger('cep_parser')->notice('Updated new cep_parser %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.cep_parser.canonical', ['cep_parser' => $entity->id()]);
  }

}
