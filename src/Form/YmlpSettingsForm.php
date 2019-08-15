<?php

namespace Drupal\ymlp\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;


class YmlpSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ymlp.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ymlp_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('ymlp.settings');

    $form['credentials'] = [
      '#type' => 'fieldset',
      '#description' => $this->t('You can get these from your Ymlp dashboard <a href="https://www.ymlp.com/app/dashboard.php">here</a>.'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Credentials'),
    ];
    $form['credentials']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['credentials']['api_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Username'),
      '#default_value' => $config->get('api_username'),
      '#required' => TRUE,
    ];

    $form['group'] = [
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Email Group'),
    ];

    $options = array();
    $options[''] = $this->t('--None--');

    if(!empty($config->get('api_key')) && !empty($config->get('api_username')) ) {
	  $ymlp = \Drupal::service('ymlp.helper');
	  $groups = $ymlp->groups();
      if($groups['status'] == 1) {
        $lists = $groups['list'];
      }
      else {
        $lists = array();
      }

      if(count($lists)>0) {
        foreach($lists as $key => $value) {
    	  $options[$key] = $value;
        }
      }
    }
    $form['group']['group_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Default email group'),
	  '#options' => $options,
	  '#default_value' => $config->get('group_id'),
	  '#required' => FALSE,
      '#empty_option' => $this->t('--Select--'),
      '#description' => $this->t('Default email group. This list will be populated after you save the API Key and API Username'),
    ];

    $form['fields'] = [
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Fields to display'),
    ];
    $form['fields']['show_first_name'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('First Name'),
	  '#default_value' => $config->get('show_first_name'),
	  '#return_value' => 1,
	  '#required' => FALSE,
    ];
    $form['fields']['show_last_name'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Last Name'),
	  '#default_value' => $config->get('show_last_name'),
	  '#return_value' => 1,
	  '#required' => FALSE,
    ];

    $form['notify'] = array(
      '#type' => 'details',
      '#title' => $this->t('Notification'),
      '#description' => $this->t('Settings for subscribe | unsubscribe notification.'),
  	  '#open' => TRUE,
    );
    $form['notify']['ymlp_subscribe_mail_subject'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Subscribe email subject'),
      '#description' => $this->t('Please enter the subscribe email subject'),
      '#default_value' => $config->get('ymlp_subscribe_mail_subject'),
	  '#size' => 50,
	  '#maxlength' => 255,
	  '#placeholder' => ' ',
    );
    $form['notify']['ymlp_subscribe_mail_body'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Subscribe email body'),
      '#description' => $this->t('Please enter the subscribe email body.'),
      '#default_value' => $config->get('ymlp_subscribe_mail_body'),
	  '#rows' => 10,
	  '#placeholder' => ' ',
    );
    $form['notify']['ymlp_unsubscribe_mail_subject'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Unsubscribe email subject'),
      '#description' => $this->t('Please enter the unsubscribe email subject'),
      '#default_value' => $config->get('ymlp_unsubscribe_mail_subject'),
	  '#size' => 50,
	  '#maxlength' => 255,
	  '#placeholder' => ' ',
    );
    $form['notify']['ymlp_unsubscribe_mail_body'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Unsubscribe email body'),
      '#description' => $this->t('Please enter the unsubscribe email body.'),
      '#default_value' => $config->get('ymlp_unsubscribe_mail_body'),
	  '#rows' => 10,
	  '#placeholder' => ' ',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('ymlp.settings');
    $config
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('api_username', $form_state->getValue('api_username'))
      ->set('group_id', $form_state->getValue('group_id'))

      ->set('show_first_name', $form_state->getValue('show_first_name'))
      ->set('show_last_name', $form_state->getValue('show_last_name'))

      ->set('ymlp_subscribe_mail_subject', $form_state->getValue('ymlp_subscribe_mail_subject'))
      ->set('ymlp_subscribe_mail_body', $form_state->getValue('ymlp_subscribe_mail_body'))
      ->set('ymlp_unsubscribe_mail_subject', $form_state->getValue('ymlp_unsubscribe_mail_subject'))
      ->set('ymlp_unsubscribe_mail_body', $form_state->getValue('ymlp_unsubscribe_mail_body'));

    $config->save();

	drupal_set_message($this->t('Configurations have been updated successfully!'));

    parent::submitForm($form, $form_state);
  }

}
