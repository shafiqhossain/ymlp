<?php

namespace Drupal\ymlp\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user unsubscribed with ymlp
 */
class YmlpUnsubscribeEvent extends Event {

  const EVENT_NAME = 'ymlp_unsubscribe';

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
   * YmlpUnsubscribeEvent constructor.
   * @param $email
   * @param $first_name
   * @param $last_name
   */
  public function __construct($email, $first_name='', $last_name='') {
    $this->email = $email;
    $this->first_name = $first_name;
    $this->last_name = $last_name;
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

}
