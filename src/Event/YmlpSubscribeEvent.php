<?php

namespace Drupal\ymlp\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user subscribed with ymlp
 */
class YmlpSubscribeEvent extends Event {

  const EVENT_NAME = 'ymlp_subscribe';

  /**
   * Email.
   *
   * @var string
   */
  protected $email;

  /**
   * First Name
   *
   * @var string
   */
  protected $first_name;

  /**
   * Last Name
   *
   * @var string
   */
  protected $last_name;

  /**
   * User ID
   *
   * @var int
   */
  protected $uid;

  /**
   * YmlpSubscribeEvent constructor.
   * @param $email
   * @param $first_name
   * @param $last_name
   */
  public function __construct($email, $first_name='', $last_name='', $uid=0) {
    $this->email = $email;
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->uid = $uid;
  }

  /**
   * Return email
   * @return string
   */
  public function getEmail(){
    return $this->email;
  }

  /**
   * Return first name
   * @return string
   */
  public function getFirstName(){
    return !empty($this->first_name) ? $this->first_name : '';
  }

  /**
   * Return last name
   * @return string
   */
  public function getLastName(){
    return !empty($this->last_name) ? $this->last_name : '';
  }

  /**
   * Return user id
   * @return int
   */
  public function getUserId(){
    return $this->uid;
  }

}
