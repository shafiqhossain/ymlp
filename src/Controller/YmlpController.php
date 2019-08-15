<?php

namespace Drupal\ymlp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;


/**
 * Controller routines
 */
class YmlpController extends ControllerBase {

  /**
   * The Entity Manager.
   *
   * @var EntityManagerInterface $manager
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
   * {@inheritdoc}
   */
  public function __construct(QueryFactory $query_factory, EntityManagerInterface $manager, ConfigFactoryInterface $config_factory) {
    $this->queryFactory = $query_factory;
    $this->manager = $manager;
    $this->config_factory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity.manager'),
	  $container->get('config.factory')
    );
  }


  /**
   * Subscribe to Ymlp
   */
  public function subscribe($id='') {
	if(empty($id)) return [];

	//load the entity
    $entity = $this->manager->getStorage('ymlp_subscriber')->load($id);

    if($entity == false) return [];

	//submit to Ymlp
	$ymlp = \Drupal::service('ymlp.helper');

	//ymlp settings
    $ymlpConfig = $this->config_factory->get('ymlp.settings');
	$group_ids = $ymlpConfig->get('group_id');

	$response = $ymlp->subscribe($entity->getEmailAddress(), $entity->getFirstName(), $entity->getLastName(), 0, $group_ids, false);
	if ($response['status'] == 1) {
	  \Drupal::logger('ymlp')->info($this->t('Email %email subscription was successful.', ['%email' => $entity->getEmailAddress()]));
	  drupal_set_message($this->t('Email %email subscription was successful.', ['%email' => $entity->getEmailAddress()]));
	}
	else {
	  \Drupal::logger('ymlp')->error($this->t('Email %email subscription failed due to %error.', ['%email' => $entity->getEmailAddress(), '%error' => $response['message']]));
	  drupal_set_message($this->t('Email %email subscription failed due to %error.', ['%email' => $entity->getEmailAddress(), '%error' => $response['message']]), 'error');
	}

	$url = Url::fromRoute('entity.ymlp_subscriber.collection');
	return new RedirectResponse($url->setAbsolute(TRUE)->toString(), 302);

    // no cache
    $build = [];
    $build['#cache']['max-age'] = 0;

	return $build;
  }

}