<?php

namespace Drupal\cep_proxy\Form;

use Drupal\cep_proxy\Entity\CepProxy;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;

/**
 * Implements the CepProxyForm form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class CepProxyListForm extends FormBase {
  /**
   * Build the CepProxyForm.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */

  /**
   * Form add list proxy.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('This page for configuration CEP proxy with IP and external api so that adds ip'),
    ];

    $form['cep_proxy_form_list_add'] = [
      '#type' => 'textarea',
      '#title' => $this->t('add proxy ip one for row'),
      '#description' => $this->t('Textarea, #type = textarea'),
      '#attributes' => ['style' => 'width: auto;'],
      '#cols' => 60,
    ];
    $options = [];
    $types_proxy = \Drupal::entityTypeManager()->getStorage('cep_proxy_type')->loadByProperties();

    foreach ($types_proxy as $k => $p) {
      $options[$k] = $p->label();
    }

    $form['proxy_type'] = [
      '#title' => $this->t('Type'),
      '#type' => 'select',
      '#options' => $options,
      '#empty_option' => $this->t('- Select a proxy type -'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'cep_proxy_CepProxyForm';
  }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $type_proxy = $form_state->getValue('proxy_type');
    if (empty($type_proxy)) {
      $form_state->setErrorByName('cep_proxy_form_list_add', $this->t('Please selected type proxy.'));
      return FALSE;
    }
    $cep_proxy_form_list_add = $form_state->getValue('cep_proxy_form_list_add');
    if (strlen($cep_proxy_form_list_add) < 8) {
      $entity_proxy_type = \Drupal::entityTypeManager()->getStorage('cep_proxy_type')->load($type_proxy);
      $url = $entity_proxy_type->url();
      if (!empty($url)) {

        // var_dump($entity_proxy_type->url());
        $response = $this->getHttpRequest($url);
        if($response !== FALSE){
          $form_state->setValue('cep_proxy_form_list_add', $response);
        }
      }

    }
    $cep_proxy_form_list_add = $form_state->getValue('cep_proxy_form_list_add');
    if (strlen($cep_proxy_form_list_add) < 8) {
      $form_state->setErrorByName('cep_proxy_form_list_add', $this->t('The proxy list must be at least 8 characters long.'));
    }
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $list_ip = $form_state->getValue('cep_proxy_form_list_add');
    $proxyList = preg_split('[\n]', $list_ip);
    $type_proxy = $form_state->getValue('proxy_type');

    $entity = CepProxy::create([
      'type' => $type_proxy,
    ]);
    $cc = 0;
    $cc_d = 0;
    foreach ($proxyList as $proxyIp) {
      if (empty($proxyIp)) {
        continue;
      }
      $proxy = str_replace("\r", "", $proxyIp);
      $name = md5($proxy);
      $pe = $entity->getEntityByName($name);
      if (!$pe) {
        $e = CepProxy::create([
          'type' => $type_proxy,
        ]);
        $e->name->value = $name;
        $e->ip->value = $proxy;
        $e->save();
        $cc++;
      }
      else {
        $cc_d++;
      }
    }
    $message = $this->t('Items were added %value items of IP', ['%value' => $cc]);
    $this->messenger()->addMessage($message);
    $message = $this->t('Duplicates were proved %value items of IP', ['%value' => $cc_d]);
    $this->messenger()->addMessage($message);
    return $form;
  }

  /**
   * Get http request.
   */
  public function getHttpRequest($url) {
    try {
      $httpClient = new Client();

      $request = $httpClient->request(
          'GET', $url,
      );
      $response = $request->getBody()->getContents();
      return $response;
    }
    catch (Exception $ex) {
      var_dump($ex);
      return FALSE;
    }
  }

}
