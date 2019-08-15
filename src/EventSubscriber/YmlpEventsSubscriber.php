<?php

namespace Drupal\ymlp\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactory;

use Drupal\ymlp\Event\YmlpSubscribeEvent;
use Drupal\ymlp\Event\YmlpUnsubscribeEvent;
use Drupal\ymlp\Event\YmlpSendSubscribeEmailEvent;
use Drupal\ymlp\Event\YmlpSendUnsubscribeEmailEvent;

/**
 * Class YmlpEventsSubscriber.
 *
 * @package Drupal\ymlp\EventSubscriber\EventSubscriber
 */
class YmlpEventsSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * The Entity Query.
   *
   * @var QueryFactory $queryFactory
   */
  protected $entity_query;

  /**
   * The Entity Manager.
   *
   * @var EntityManagerInterface $manager
   */
  protected $manager;

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
   * {@inheritdoc}
   */
  public function __construct(QueryFactory $entity_query, EntityManagerInterface $manager, MailManagerInterface $mailManager, LoggerChannelFactoryInterface $loggerFactory) {
    $this->entity_query = $entity_query;
    $this->manager = $manager;
	$this->mailManager = $mailManager;
    $this->logger = $loggerFactory->get('ymlp');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity.manager'),
      $container->get('plugin.manager.mail'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      YmlpSubscribeEvent::EVENT_NAME => 'doSubscribe',
      YmlpUnsubscribeEvent::EVENT_NAME => 'doUnsubscribe',
      YmlpSendSubscribeEmailEvent::EVENT_NAME => 'sendSubscribeEmail',
      YmlpSendUnsubscribeEmailEvent::EVENT_NAME => 'sendUnsubscribeEmail'
    ];
  }

  /**
   * React to an event when user subscribed to ymlp.
   *
   * @param \Drupal\ymlp\Event\YmlpSubscribeEvent $event
   *   Subscribe event.
   */
  public function doSubscribe(YmlpSubscribeEvent $event) {
	//check if email adready exists
    $ids = $this->entity_query->get('ymlp_subscriber')
      ->condition('mail', $event->getEmail())
      ->execute();

	//check if exists
	if(is_array($ids) && count($ids)) {
	  $this->logger->info($this->t('Email %email already exists in the subscriber list.', ['%email' => $event->getEmail()]));
	  return;
	}

	$values = array(
	  'id' => NULL,
	  'label' => (!empty($event->getFirstName()) || !empty($event->getLastName()) ? $event->getFirstName().' '.$event->getLastName() : ''),
	  'uid' => $event->getUserId(),
	  'status' => TRUE,  //published
	  'first_name' => $event->getFirstName(),
	  'last_name' => $event->getLastName(),
	  'mail' => $event->getEmail(),
	);

	$entity = $this->manager->getStorage('ymlp_subscriber')->create($values);
	$entity->save();

	$this->logger->info($this->t('Email %email added to subscriber list.', ['%email' => $event->getEmail()]));
  }

  /**
   * React to an event when user unsubscribed from ymlp.
   *
   * @param \Drupal\ymlp\Event\YmlpUnsubscribeEvent $event
   *   Unsubscribe event.
   */
  public function doUnsubscribe(YmlpUnsubscribeEvent $event) {
	//check if email adready exists
    $ids = $this->entity_query->get('ymlp_subscriber')
      ->condition('mail', $event->getEmail())
      ->execute();

	//check if exists
	if(is_array($ids) && count($ids)) {
	  foreach($ids as $id) {
		$entity = $this->manager->getStorage('ymlp_subscriber')->load($id);
		if($entity != false) {
		  $entity->delete();
		}
	  }
	}

	$this->logger->info($this->t('Email %email removed from subscriber list.', ['%email' => $event->getEmail()]));
  }

  /**
   * React to an event, when user subscribe to ymlp
   *
   * @param \Drupal\ymlp\Event\YmlpSendSubscribeEmailEvent $event
   *   Subscribe email event.
   */
  public function sendSubscribeEmail(YmlpSendSubscribeEmailEvent $event) {
	$params = array();
	$params['subject'] = $event->getSubject();
	$params['body'] = $event->getBody();
	$params['from'] = $event->getFrom();

    $langcode = \Drupal::currentUser()->getPreferredLangcode();

	//send email now
	$result = $this->mailManager->mail('ymlp', 'subscribe', $event->getEmail(), $langcode, $params, NULL, true);
	if ($result['result'] !== true) {
	  $this->logger->error($this->t('Email %email subscription was successful but notification failed.', ['%email' => $event->getEmail()]));
	}
	else {
	  $this->logger->info($this->t('Email %email subscription was successful.', ['%email' => $event->getEmail()]));
	}
  }

  /**
   * React to an event, when user unsubscribe from ymlp
   *
   * @param \Drupal\ymlp\Event\YmlpSendUnsubscribeEmailEvent $event
   *   Unsubscribe email event.
   */
  public function sendUnsubscribeEmail(YmlpSendUnsubscribeEmailEvent $event) {
	$params = array();
	$params['subject'] = $event->getSubject();
	$params['body'] = $event->getBody();
	$params['from'] = $event->getFrom();

    $langcode = \Drupal::currentUser()->getPreferredLangcode();

	//send email now
	$result = $this->mailManager->mail('ymlp', 'unsubscribe', $event->getEmail(), $langcode, $params, NULL, true);
	if ($result['result'] !== true) {
	  $this->logger->error($this->t('Email %email subscription was cancelled successfully but notification failed.', ['%email' => $event->getEmail()]));
	}
	else {
	  $this->logger->info($this->t('Email %email subscription was cancelled successfully.', ['%email' => $event->getEmail()]));
	}
  }

}
