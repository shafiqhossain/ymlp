<?php

namespace Drupal\ymlp\Services;

use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;

use Drupal\ymlp\Event\YmlpSubscribeEvent;
use Drupal\ymlp\Event\YmlpUnsubscribeEvent;
use Drupal\ymlp\Event\YmlpSendSubscribeEmailEvent;
use Drupal\ymlp\Event\YmlpSendUnsubscribeEmailEvent;

/**
 * Ymlp Utility routines
 */
class YmlpHelper {
  use StringTranslationTrait;

  /**
   * The Entity Manager.
   *
   * @var EntityManagerInterface $manager
   */
  protected $manager;

  /**
   * The Entity Query.
   *
   * @var QueryFactory $entity_query
   */
  protected $entity_query;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The config object.
   *
   * @var ConfigFactoryInterface $config_factory
   */
  protected $config_factory;

  /**
   * @var AccountInterface $account
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public function __construct(QueryFactory $query_factory, EntityManagerInterface $manager, MailManagerInterface $mailManager, LoggerChannelFactoryInterface $loggerFactory, ConfigFactoryInterface $config_factory, AccountInterface $account) {
    $this->entity_query = $query_factory;
    $this->manager = $manager;
	$this->mailManager = $mailManager;
    $this->logger = $loggerFactory->get('ymlp');
    $this->config_factory = $config_factory;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity.manager'),
      $container->get('plugin.manager.mail'),
      $container->get('logger.factory'),
	  $container->get('config.factory'),
      $container->get('current_user')
    );
  }


  /**
   * Get the Ymlp API instance
   */
  public function ymlp_api() {
	$config = $this->config_factory->get('ymlp.settings');
	$apiKey = $config->get('api_key');
	$apiUsername = $config->get('api_username');

	//if any of api key or username is empty, return false;
	if(empty($apiKey) || empty($apiUsername)) return false;

	$ymlp = new \Drupal\ymlp\YmlpApi($apiKey, $apiUsername, false);

	return $ymlp;
  }

  /**
   * Subscribe an email address to a group
   */
  public function subscribe($email='', $fname='', $lname='', $uid=0, $group_id='', $dispatch_event=TRUE) {
	$output = [
	  'status' => 0,
	  'message' => '',
	  'response' => '',
	  'code' => ''
	];

    if(empty($email)) {
	  $output['message'] = $this->t('Email is empty');
      return $output;
    }

	//if empty get the group id from setting
    if(empty($group_id)) {
	  $group_id = $config->get('newsletter_id');
	}
	if(empty($group_id)) {
	  $output['message'] = $this->t('Group is empty');
	  return $output;
	}

	//params
	$overrule_unsubscribed_bounced = "0";

	//get the settings
	$config = $this->config_factory->get('ymlp.settings');

	//get the instance
	$api = $this->ymlp_api();
    if($api == false) {
	  $output['message'] = $this->t('API Key or Username is empty');
      return $output;
    }

	try {
	  $response = $api->ContactsAdd($email, array(), $group_id, $overrule_unsubscribed_bounced);

	  if ($api->getErrorMessage()) {
		$output = [
		  'status' => 0,
		  'message' => $api->getErrorMessage(),
		  'response' => $response,
		  'code' => ''
		];
		$this->logger->error($this->t('Email %email subscription failed due to %error.', ['%email' => $email, '%error' => $api->getErrorMessage()]));
	  }
	  else {
		$output = [
		  'status' => 1,
		  'message' => $response["Output"],
		  'response' => $response,
		  'code' => $response["Code"]
		];

		if($dispatch_event) {
	      // Get the event dispatcher server and dispatch the event.
	      $event_dispatcher = \Drupal::service('event_dispatcher');

	      // add to subscriber list.
	      $event = new YmlpSubscribeEvent($email, $fname, $lname, $uid);
	      $event_dispatcher->dispatch(YmlpSubscribeEvent::EVENT_NAME, $event);

		  //send the notification
	      $event = new YmlpSendSubscribeEmailEvent($email, $config->get('ymlp_subscribe_mail_subject'), $config->get('ymlp_subscribe_mail_body'), \Drupal::config('system.site')->get('mail'));
	      $event_dispatcher->dispatch(YmlpSendSubscribeEmailEvent::EVENT_NAME, $event);
	    }
	  }
	}
	catch(Exception $e) {
	  $output = [
		'status' => 0,
		'message' => $e->getMessage(),
		'response' => '',
		'code' => ''
	  ];

	  $this->logger->error($this->t('Email %email subscription failed due to %error.', ['%email' => $email, '%error' => $api->getErrorMessage()]));
	}

	return $output;
  }


  /**
   * Un-Subscribe an email address from all group
   */
  public function unsubscribe($email = '', $dispatch_event=TRUE) {
	$output = [
	  'status' => 0,
	  'message' => '',
	  'response' => '',
	  'code' => ''
	];

    if(empty($email)) {
	  $output['message'] = $this->t('Email is empty');
      return $output;
    }

	//get the settings
	$config = $this->config_factory->get('ymlp.settings');

	//get the instance
	$api = $this->ymlp_api();
    if($api == false) {
	  $output['message'] = $this->t('API Key or Username is empty');
      return $output;
    }

	try {
	  $response = $api->ContactsUnsubscribe($email);

	  $params = array();
	  $params['subject'] = $config->get('ymlp_unsubscribe_mail_subject');
	  $params['body'] = $config->get('ymlp_unsubscribe_mail_body');
	  $params['from'] = \Drupal::config('system.site')->get('mail');
 	  $langcode = \Drupal::currentUser()->getPreferredLangcode();

	  if ($api->getErrorMessage()) {
		$output = [
		  'status' => 0,
		  'message' => $api->getErrorMessage(),
		  'response' => $response,
		  'code' => ''
		];
		$this->logger->error($this->t('Email %email un-subscribe was failed due to %error.', ['%email' => $email, '%error' => $api->getErrorMessage()]));
	  }
	  else {
		$output = [
		  'status' => 1,
		  'message' => $response["Output"],
		  'response' => $response,
		  'code' => $response["Code"]
		];

		if($dispatch_event) {
	      // Get the event dispatcher server and dispatch the event.
	      $event_dispatcher = \Drupal::service('event_dispatcher');

	      // add to subscriber list.
	      $event = new YmlpUnsubscribeEvent($email);
	      $event_dispatcher->dispatch(YmlpUnsubscribeEvent::EVENT_NAME, $event);

		  //send the notification
	      $event = new YmlpSendUnsubscribeEmailEvent($email, $config->get('ymlp_subscribe_mail_subject'), $config->get('ymlp_subscribe_mail_body'), \Drupal::config('system.site')->get('mail'));
	      $event_dispatcher->dispatch(YmlpSendUnsubscribeEmailEvent::EVENT_NAME, $event);
	    }
	  }
	}
	catch(Exception $e) {
	  $output = [
		'status' => 0,
		'message' => $e->getMessage(),
		'response' => '',
		'code' => ''
	  ];
	  $this->logger->error($this->t('Email %email un-subscribe was failed due to %error.', ['%email' => $email, '%error' => $api->getErrorMessage()]));
	}

	return $output;
  }


  /**
   * Return list of newsletter groups
   */
  public function groups() {
	$output = [
	  'status' => 0,
	  'message' => '',
	  'response' => '',
	  'list' => array(),
	  'code' => ''
	];

	//get the instance
	$api = $this->ymlp_api();
    if($api == false) {
	  $output['message'] = $this->t('API Key or Username is empty');
      return $output;
    }

	try {
	  $response = $api->GroupsGetList();

	  if ($api->getErrorMessage()) {
		$output = [
		  'status' => 0,
		  'message' => $api->getErrorMessage(),
		  'response' => $response,
	  	  'list' => array(),
		  'code' => ''
		];
	  }
	  else {
	    $list = [];
		if (isset($response["Code"])) {
		  $list[$response["Code"]] = $response["Output"];
		}
		else {
		  foreach ($response as $item) {
			  $list[$item["ID"]] = $item["GroupName"];
		  }
		}

		$output = [
		  'status' => 1,
		  'message' => (isset($response["Output"]) ? $response["Output"] : ''),
		  'response' => $response,
	  	  'list' => $list,
		  'code' => (isset($response["Code"]) ? $response["Code"] : '')
		];
	  }
	}
	catch(Exception $e) {
	  $output = [
		'status' => 0,
		'message' => $e->getMessage(),
		'response' => '',
	  	'list' => array(),
		'code' => ''
	  ];
	}

	return $output;
  }


  /**
   * Return contact emails data
   */
  public function contact_list($email = '') {
	$output = [
	  'status' => 0,
	  'message' => '',
	  'response' => '',
	  'list' => array(),
	  'code' => ''
	];

    if(empty($email)) {
	  $output['message'] = $this->t('Email is empty');
      return $output;
    }

	//get the instance
	$api = $this->ymlp_api();
    if($api == false) {
	  $output['message'] = $this->t('API Key or Username is empty');
      return $output;
    }

	try {
	  $response = $api->ContactsGetContact($email);

	  if ($api->getErrorMessage()) {
		$output = [
		  'status' => 0,
		  'message' => $api->getErrorMessage(),
		  'response' => $response,
	  	  'list' => array(),
		  'code' => ''
		];
	  }
	  else {
	    $list = [];
		if (isset($response["Code"])) {
		  $list[$response["Code"]] = $response["Output"];
		}
		else {
		  foreach ($response as $item) {
			foreach ($item as $key => $value) {
			  $list[$key] = $value;
			}
		  }
		}

		$output = [
		  'status' => 1,
		  'message' => (isset($response["Output"]) ? $response["Output"] : ''),
		  'response' => $response,
	  	  'list' => $list,
		  'code' => (isset($response["Code"]) ? $response["Code"] : '')
		];
	  }
	}
	catch(Exception $e) {
	  $output = [
		'status' => 0,
		'message' => $e->getMessage(),
		'response' => '',
	  	'list' => array(),
		'code' => ''
	  ];
	}

	return $output;
  }

  /**
   * Check the subscription status by Email.
   *
   * @return bool
   *   True or False - whether subscribed or not.
   */
  public function hasSubscribed($mail) {
    $status = 0;
    $ids = $this->entity_query->get('ymlp_subscriber')
      ->condition('mail', $mail)
      ->execute();

	if(count($ids)) $status = 1;

    return (bool) $status;
  }

  /**
   * Check the subscription status by User.
   *
   * @return bool
   *   True or False - whether subscribed or not.
   */
  public function hasUserSubscribed($uid=0) {
	if(empty($uid)) $uid = $this->account->id();

	$user = $this->manager->getStorage('user')->load($uid);
	$mail = '';
	if($user != false) {
	  $mail = $user->getEmail();
	}

    $status = 0;
    $ids = $this->entity_query->get('ymlp_subscriber')
      ->condition('mail', $mail)
      ->execute();

	if(count($ids)) $status = 1;

    return (bool) $status;
  }

}