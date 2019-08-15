<?php

namespace Drupal\ymlp;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Ymlp subscriber entities.
 *
 * @ingroup ymlp
 */
class YmlpSubscriberListBuilder extends EntityListBuilder {

  /**
   * The Entity Manager.
   *
   * @var EntityManagerInterface $manager
   */
  protected $entityManager;

  /**
   * The Entity Query.
   *
   * @var QueryFactory $queryFactory
   */
  protected $entityQuery;

  /**
   * Constructs a new YmlpSubscriberListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity Manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, QueryFactory $entity_query, EntityManagerInterface $entity_manager) {
    parent::__construct($entity_type, $storage);

    $this->entityQuery = $entity_query;
    $this->entityManager = $entity_manager;
  }


  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('entity.query'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $query = $this->entityQuery->get('ymlp_subscriber');
    $header = $this->buildHeader();

    $query->pager($this->limit);
    $query->tableSort($header);

    $ids = $query->execute();

    return $this->entityManager->getStorage('ymlp_subscriber')->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = array(
      'mail' => array(
        'data' => $this->t('Email'),
        'field' => 'mail',
        'specifier' => 'mail',
      ),
      'first_name' => array(
        'data' => $this->t('First Name'),
        'field' => 'first_name',
        'specifier' => 'first_name',
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      'last_name' => array(
        'data' => $this->t('Last Name'),
        'field' => 'last_name',
        'specifier' => 'last_name',
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      )
    );

    return $header + parent::buildHeader();
  }


  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\ymlp\Entity\YmlpSubscriber */
    $row['first_name'] = $entity->getFirstName();
    $row['last_name'] = $entity->getLastName();
    $row['mail'] = Link::createFromRoute(
      $entity->getEmailAddress(),
      'entity.ymlp_subscriber.edit_form',
      ['ymlp_subscriber' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#empty'] = $this->t('There are no subscribers available.');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOperations11(EntityInterface $entity) {
    $operations = $this->getDefaultOperations($entity);

    if ($entity->access('subscribe')) {
      $operations['subscribe'] = array(
        'title' => $this->t('Re-subscribe'),
        'weight' => 10,
        'url' => Url::fromRoute("ymlp.route.subscribe", ['id' => $entity->id()]),
      );
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    //if ($entity->access('subscribe')) {
      $operations['subscribe'] = array(
        'title' => $this->t('Re-subscribe'),
        'weight' => 10,
        'url' => Url::fromRoute("ymlp.route.subscribe", ['id' => $entity->id()]),
      );
    //}

    return $operations;
  }

}
