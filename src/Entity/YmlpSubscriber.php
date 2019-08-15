<?php

namespace Drupal\ymlp\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines the Ymlp subscriber entity.
 *
 * @ingroup ymlp
 *
 * @ContentEntityType(
 *   id = "ymlp_subscriber",
 *   label = @Translation("Ymlp subscriber"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ymlp\YmlpSubscriberListBuilder",
 *     "views_data" = "Drupal\ymlp\Entity\YmlpSubscriberViewsData",
 *     "translation" = "Drupal\ymlp\YmlpSubscriberTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\ymlp\Form\YmlpSubscriberForm",
 *       "add" = "Drupal\ymlp\Form\YmlpSubscriberForm",
 *       "edit" = "Drupal\ymlp\Form\YmlpSubscriberForm",
 *       "delete" = "Drupal\ymlp\Form\YmlpSubscriberDeleteForm",
 *     },
 *     "access" = "Drupal\ymlp\YmlpSubscriberAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ymlp\YmlpSubscriberHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "ymlp_subscriber",
 *   data_table = "ymlp_subscriber_field_data",
 *   fieldable = FALSE,
 *   translatable = TRUE,
 *   admin_permission = "administer ymlp subscribers",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "mail",
 *     "uuid" = "uuid",
 *     "mail" = "mail",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   constraints = {
 *     "UniqueSubscriberEmail" = {}
 *   },
 *   links = {
 *     "canonical" = "/admin/config/services/ymlp/subscribers/{ymlp_subscriber}",
 *     "add-form" = "/admin/config/services/ymlp/subscribers/add",
 *     "edit-form" = "/admin/config/services/ymlp/subscribers/{ymlp_subscriber}/edit",
 *     "delete-form" = "/admin/config/services/ymlp/subscribers/{ymlp_subscriber}/delete",
 *     "collection" = "/admin/config/services/ymlp/subscribers",
 *   },
 *   field_ui_base_route = "ymlp_subscriber.settings"
 * )
 */
class YmlpSubscriber extends ContentEntityBase implements YmlpSubscriberInterface {

  use EntityChangedTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstName() {
    return !empty($this->get('first_name')->value) ? $this->get('first_name')->value : '';
  }

  /**
   * {@inheritdoc}
   */
  public function setFirstName($name) {
    $this->set('first_name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastName() {
    return !empty($this->get('last_name')->value) ? $this->get('last_name')->value : '';
  }

  /**
   * {@inheritdoc}
   */
  public function setLastName($name) {
    $this->set('last_name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmailAddress() {
    return $this->get('mail')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEmailAddress($mail) {
    $this->set('mail', $mail);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Subscriber ID'))
      ->setDescription(t('Primary key: Unique subscriber ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['first_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('First Name'))
      ->setDescription(t('The first name of the subscriber.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'settings' => array(
          'display_label' => TRUE,
        ),
        'weight' => -9,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['last_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Last Name'))
      ->setDescription(t('The last name of the subscriber.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'settings' => array(
          'display_label' => TRUE,
        ),
        'weight' => -8,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['mail'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t("The subscriber's email address."))
      ->setSetting('default_value', '')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'email_default',
        'settings' => array(),
        'weight' => 5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('The corresponding user.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'hidden',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'hidden',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language'))
      ->setDescription(t("The subscriber's preferred language."));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('Contains the requested subscription changes.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the subscriber was created.'));

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The subscriber UUID.'))
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Boolean indicating the status of the subscriber.'))
      ->setDefaultValue(TRUE);

    return $fields;
  }


  /**
   * {@inheritdoc}
   */
  public function postSave(\Drupal\Core\Entity\EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

	//submit to Ymlp
	$ymlp = \Drupal::service('ymlp.helper');

	//ymlp settings
	$ymlpConfig = \Drupal::config('ymlp.settings');
	$group_ids = $ymlpConfig->get('group_id');

	$response = $ymlp->subscribe($this->getEmailAddress(), $this->getFirstName(), $this->getLastName(), 0, $group_ids, false);
	if ($response['status'] == 1) {
		\Drupal::logger('ymlp')->info($this->t('Email %email subscription was successful.', ['%email' => $this->getEmailAddress()]));
	}
	else {
	  \Drupal::logger('ymlp')->error($this->t('Email %email subscription failed due to %error.', ['%email' => $this->getEmailAddress(), '%error' => $response['message']]));
	}
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(\Drupal\Core\Entity\EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

	//submit to Ymlp
	$ymlp = \Drupal::service('ymlp.helper');

    foreach ($entities as $entity) {
	  $response = $ymlp->unsubscribe($entity->getEmailAddress(), false);
	  if ($response['status'] == 1) {
		\Drupal::logger('ymlp')->info(t('Email %email subscription cancel was successful.', ['%email' => $entity->getEmailAddress()]));
	  }
	  else {
	    \Drupal::logger('ymlp')->error(t('Email %email subscription cancel failed due to %error.', ['%email' => $entity->getEmailAddress(), '%error' => $response['message']]));
	  }
    }
  }

}
