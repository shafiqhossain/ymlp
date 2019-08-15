<?php

/**
 * @file
 * Contains Drupal\ymlp\Form\YmlpSubscriptionForm.
 */

namespace Drupal\ymlp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Egulias\EmailValidator\EmailValidator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Render\Markup;
use Drupal\Core\Ajax\AjaxResponse;

/**
  * Ymlp Subscription form
  */
class YmlpSubscriptionForm extends FormBase {

  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * @var AccountInterface $account
   */
  protected $account;

  /**
   * The Entity Manager.
   *
   * @var EntityTypeManagerInterface $manager
   */
  protected $manager;

  /**
   * The Entity Query.
   *
   * @var QueryFactory $queryFactory
   */
  protected $queryFactory;

  /**
   * The config object.
   *
   * @var ConfigFactoryInterface $config_factory
   */
  protected $config_factory;

  /**
   * The description
   *
   * @var String $description
   */
  protected $description;

  /**
   * The subscribe message
   *
   * @var String $subscribe_message
   */
  protected $subscribe_message;

  /**
   * The unsubscribe message
   *
   * @var String $unsubscribe_message
   */
  protected $unsubscribe_message;

  /**
   * The Groups
   *
   * @var array $groups
   */
  protected $groups;

  /**
   * The unique id
   *
   * @var String $unique_id
   */
  protected $unique_id;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $manager, AccountInterface $account, EmailValidator $email_validator, ConfigFactoryInterface $config_factory) {
    $this->manager = $manager;
    $this->account = $account;
    $this->emailValidator = $email_validator;
    $this->config_factory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('email.validator'),
	  $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    if (empty($this->unique_id)) {
      throw new \Exception('Unique ID must be set with setUniqueId.');
    }
    return 'ymlp_subscription_form_'.$this->unique_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setUniqueId($id) {
    $this->unique_id = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroups($groups) {
    $this->groups = $groups;
  }

  /**
   * {@inheritdoc}
   */
  public function setMessage($text) {
    $this->description = $text;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubscribeMessage($text) {
    $this->subscribe_message = $text;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnsubscribeMessage($text) {
    $this->unsubscribe_message = $text;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
	//ymlp settings
    $ymlpConfig = $this->config_factory->get('ymlp.settings');
	$show_first_name = $ymlpConfig->get('show_first_name');
	$show_last_name = $ymlpConfig->get('show_last_name');

    $ymlp = \Drupal::service('ymlp.helper');

    $form['description'] = [
	  '#markup' => $this->description,
	  '#prefix' => '<div class="ymlp-subscribe-description">',
	  '#suffix' => '</div>'
    ];

    $form['error_message'] = [
	  '#markup' => '',
	  '#prefix' => '<div class="ymlp-error-message">',
	  '#suffix' => '</div>'
    ];

	$form['block_id'] = array(
	  '#type' => 'hidden',
	  '#value' => $this->unique_id,
	);

	if($this->account->id()) {
	  if($ymlp->hasUserSubscribed()) {
	    $form['first_name'] = array(
	      '#markup' => '',
	      '#prefix' => '<div class="ymlp-subscribe-first-name">',
	      '#suffix' => '</div>'
	    );

	    $form['last_name'] = array(
	      '#markup' => '',
	      '#prefix' => '<div class="ymlp-subscribe-last-name">',
	      '#suffix' => '</div>'
	    );

	    $form['email'] = array(
	      '#type' => 'hidden',
	      '#value' => $this->account->getEmail(),
	      '#prefix' => '<div class="ymlp-subscribe-email">',
	      '#suffix' => '</div>'
	    );

		$form['unsubscribe'] = array(
		  '#type' => 'submit',
		  '#value' => $this->t('Unsubscribe'),
		  '#attributes' => [
			'class' => [
			  'use-ajax',
			],
		  ],
		  '#ajax' => array(
			 'callback' => [$this, 'processUnsubscription'],
			 'event' => 'click',
			 'progress' => array(
			   'type' => 'throbber',
			   'message' => NULL,
			 ),
			 'disable-refocus' => true,
		  ),
		);
	  }
	  else {
		if($show_first_name) {
		  $form['first_name'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('First Name'),
			'#size' => 20,
			'#maxlength' => 255,
			'#default_value' => ($form_state->hasValue('first_name') ? $form_state->getValue('first_name') : ''),
			'#required' => TRUE,
			'#prefix' => '<div class="ymlp-subscribe-first-name">',
			'#suffix' => '</div>',
			'#placeholder' => $this->t('First Name'),
		  );
		}

		if($show_last_name) {
		  $form['last_name'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Last Name'),
			'#size' => 20,
			'#maxlength' => 255,
			'#default_value' => ($form_state->hasValue('last_name') ? $form_state->getValue('last_name') : ''),
			'#required' => TRUE,
			'#prefix' => '<div class="ymlp-subscribe-last-name">',
			'#suffix' => '</div>',
			'#placeholder' => $this->t('Last Name'),
		  );
		}

	    $form['email'] = array(
	      '#type' => 'hidden',
	      '#value' => $this->account->getEmail(),
	      '#prefix' => '<div class="ymlp-subscribe-email">',
	      '#suffix' => '</div>'
	    );

		$form['subscribe'] = array(
		  '#type' => 'submit',
		  '#value' => $this->t('Subscribe'),
		  '#attributes' => [
			'class' => [
			  'use-ajax',
			],
		  ],
		  '#ajax' => array(
			 'callback' => [$this, 'processSubscription'],
			 'event' => 'click',
			 'progress' => array(
			   'type' => 'throbber',
			   'message' => NULL,
			 ),
			 'disable-refocus' => true,
		  ),
		);
	  }
	}
	else {
	  if($show_first_name) {
	    $form['first_name'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('First Name'),
		  '#size' => 20,
		  '#maxlength' => 255,
		  '#default_value' => ($form_state->hasValue('first_name') ? $form_state->getValue('first_name') : ''),
		  '#required' => TRUE,
		  '#prefix' => '<div class="ymlp-subscribe-first-name">',
		  '#suffix' => '</div>',
 		  '#placeholder' => $this->t('First Name'),
	    );
	  }

	  if($show_last_name) {
	    $form['last_name'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('Last Name'),
		  '#size' => 20,
		  '#maxlength' => 255,
		  '#default_value' => ($form_state->hasValue('last_name') ? $form_state->getValue('last_name') : ''),
		  '#required' => TRUE,
		  '#prefix' => '<div class="ymlp-subscribe-last-name">',
		  '#suffix' => '</div>',
 		  '#placeholder' => $this->t('Last Name'),
	    );
	  }

	  $form['email'] = array(
	    '#type' => 'textfield',
	    '#title' => $this->t('Email Address'),
	    '#size' => 20,
	    '#maxlength' => 255,
	    '#default_value' => ($form_state->hasValue('email') ? $form_state->getValue('email') : ''),
	    '#required' => TRUE,
	    '#prefix' => '<div class="ymlp-subscribe-email">',
	    '#suffix' => '</div>',
 	    '#placeholder' => $this->t('E-mail'),
	  );

	  $form['subscribe'] = array(
	    '#type' => 'submit',
	    '#value' => $this->t('Subscribe'),
	    '#attributes' => [
	  	  'class' => [
	  	    'use-ajax',
	  	  ],
	    ],
        '#ajax' => array(
	       'callback' => [$this, 'processSubscription'],
           'event' => 'click',
           'progress' => array(
             'type' => 'throbber',
             'message' => NULL,
           ),
           'disable-refocus' => true,
        ),
	  );
	}

    $form['#theme'] = 'ymlp_subscription_form';
	$form['#prefix'] = '<div class="ymlp-subscription-wrapper" id="ymlp-subscription'.(!empty($this->unique_id) ? '-'.$this->unique_id : '').'">';
	$form['#suffix'] = '</div>';

	//attach the libraries
	$form['#attached']['library'][] = 'ymlp/ymlp_style';
    $form['#cache']['max-age'] = 0;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }


  /**
   * {@inheritdoc}
   */
  public function processSubscription(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();

	$first_name = ($form_state->hasValue('first_name') && !$form_state->isValueEmpty('first_name') ? $form_state->getValue('first_name') : '');
	$last_name = ($form_state->hasValue('last_name') && !$form_state->isValueEmpty('last_name') ? $form_state->getValue('last_name') : '');
	$email = ($form_state->hasValue('email') && !$form_state->isValueEmpty('email') ? $form_state->getValue('email') : '');
	$unique_id = ($form_state->hasValue('block_id') && !$form_state->isValueEmpty('block_id') ? $form_state->getValue('block_id') : '');

	//email validation check
    if (empty($email)) {
      $error_message = $this->t('Email address is empty');
	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : '')." .ymlp-error-message", 'html', array($error_message)));
      return $ajax_response;
    }
    else if (!empty($email) && !$this->emailValidator->isValid($email)) {
      $error_message = $this->t('Email address is not valid: '.$unique_id);
	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : '')." .ymlp-error-message", 'html', array($error_message)));
      return $ajax_response;
    }

	if(is_array($this->groups)) {
	  $group_ids = implode(',',$this->groups);
	}
	else {
	  $group_ids = $this->groups;
	}

	//ymlp settings
    $ymlpConfig = $this->config_factory->get('ymlp.settings');
	$group_id = $ymlpConfig->get('group_id');

    //if none is set, assign default email group
    if(empty($this->groups)) {
	  $group_ids = $group_id;
    }

	if(empty($group_ids)) {
      $error_message = $this->t('No email group is set!');
	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : '')." .ymlp-error-message", 'html', array($error_message)));
      return $ajax_response;
	}

	//submit to Ymlp
	$ymlp = \Drupal::service('ymlp.helper');
	$response = $ymlp->subscribe($email, $first_name, $last_name, $this->account->id(), $group_ids);

    if($response['status'] == 1) {
      $error_message = '';
      $message = $this->subscribe_message;

	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : '')." .ymlp-error-message", 'html', array($error_message)));
	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : ''), 'html', array($message)));
    }
    else {
      $error_message = $response['message'];
	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : '')." .ymlp-error-message", 'html', array($error_message)));
    }

    return $ajax_response;
  }

  /**
   * {@inheritdoc}
   */
  public function processUnsubscription(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();

	$email = ($form_state->hasValue('email') && !$form_state->isValueEmpty('email') ? $form_state->getValue('email') : '');
	$unique_id = ($form_state->hasValue('block_id') && !$form_state->isValueEmpty('block_id') ? $form_state->getValue('block_id') : '');

	if(is_array($this->groups)) {
	  $group_ids = implode(',',$this->groups);
	}
	else {
	  $group_ids = $this->groups;
	}

	//ymlp settings
    $ymlpConfig = $this->config_factory->get('ymlp.settings');
	$group_id = $ymlpConfig->get('group_id');

    //if none is set, assign default email group
    if(empty($this->groups)) {
	  $group_ids = $group_id;
    }

	if(empty($group_ids)) {
      $error_message = $this->t('No email group is set!');
	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : '')." .ymlp-error-message", 'html', array($error_message)));
      return $ajax_response;
	}

	//submit to Ymlp
	$ymlp = \Drupal::service('ymlp.helper');
	$response = $ymlp->unsubscribe($email);

    if($response['status'] == 1) {
      $error_message = '';
      $message = $this->unsubscribe_message;

	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : '')." .ymlp-error-message", 'html', array($error_message)));
	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : ''), 'html', array($message)));
    }
    else {
      $error_message = $response['message'];
	  $ajax_response->addCommand(new InvokeCommand("#ymlp-subscription".(!empty($unique_id) ? '-'.$unique_id : '')." .ymlp-error-message", 'html', array($error_message)));
    }

    return $ajax_response;
  }

}

