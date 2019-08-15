<?php

namespace Drupal\ymlp\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Ymlp subscriber entities.
 *
 * @ingroup ymlp
 */
interface YmlpSubscriberInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Ymlp subscriber name.
   *
   * @return string
   *   Name of the Ymlp subscriber.
   */
  public function getName();

  /**
   * Sets the Ymlp subscriber name.
   *
   * @param string $name
   *   The Ymlp subscriber name.
   *
   * @return \Drupal\ymlp\Entity\YmlpSubscriberInterface
   *   The called Ymlp subscriber entity.
   */
  public function setName($name);

  /**
   * Gets the Ymlp subscriber creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Ymlp subscriber.
   */
  public function getCreatedTime();

  /**
   * Sets the Ymlp subscriber creation timestamp.
   *
   * @param int $timestamp
   *   The Ymlp subscriber creation timestamp.
   *
   * @return \Drupal\ymlp\Entity\YmlpSubscriberInterface
   *   The called Ymlp subscriber entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Ymlp subscriber published status indicator.
   *
   * Unpublished Ymlp subscriber are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Ymlp subscriber is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Ymlp subscriber.
   *
   * @param bool $published
   *   TRUE to set this Ymlp subscriber to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\ymlp\Entity\YmlpSubscriberInterface
   *   The called Ymlp subscriber entity.
   */
  public function setPublished($published);

  /**
   * Gets the Ymlp subscriber first name.
   *
   * @return string
   *   First Name of the Ymlp subscriber.
   */
  public function getFirstName();

  /**
   * Sets the Ymlp subscriber first name.
   *
   * @param string $name
   *   The Ymlp subscriber first name.
   *
   * @return \Drupal\ymlp\Entity\YmlpSubscriberInterface
   *   The called Ymlp subscriber entity.
   */
  public function setFirstName($name);

  /**
   * Gets the Ymlp subscriber last name.
   *
   * @return string
   *   Last Name of the Ymlp subscriber.
   */
  public function getLastName();

  /**
   * Sets the Ymlp subscriber last name.
   *
   * @param string $name
   *   The Ymlp subscriber last name.
   *
   * @return \Drupal\ymlp\Entity\YmlpSubscriberInterface
   *   The called Ymlp subscriber entity.
   */
  public function setLastName($name);

  /**
   * Gets the Ymlp subscriber email address.
   *
   * @return string
   *   Email Address of the Ymlp subscriber.
   */
  public function getEmailAddress();

  /**
   * Sets the Ymlp subscriber email address.
   *
   * @param string $mail
   *   The Ymlp subscriber email address.
   *
   * @return \Drupal\ymlp\Entity\YmlpSubscriberInterface
   *   The called Ymlp subscriber entity.
   */
  public function setEmailAddress($mail);

}
