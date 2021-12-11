<?php

namespace Drupal\cep_parser_item\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the cep_parser_item entity edit forms.
 */
class CepParserItemForm extends ContentEntityForm {

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
      $this->messenger()->addStatus($this->t('New cep_parser_item %label has been created.', $message_arguments));
      $this->logger('cep_parser_item')->notice('Created new cep_parser_item %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The cep_parser_item %label has been updated.', $message_arguments));
      $this->logger('cep_parser_item')->notice('Updated new cep_parser_item %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.cep_parser_item.canonical', ['cep_parser_item' => $entity->id()]);
  }

}
