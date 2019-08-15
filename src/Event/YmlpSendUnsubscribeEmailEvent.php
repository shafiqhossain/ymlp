<?php

namespace Drupal\ymlp\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user unsubscribe to ymlp
 */
class YmlpSendUnsubscribeEmailEvent extends Event {

  const EVENT_NAME = 'ymlp_send_unsubscriber_email';

  /**
   * Email.
   *
   * @var string
   */
  protected $email;

  /**
   * Subject
   *
   * @var string
   */
  protected $subject;

  /**
   * Body
   *
   * @var string
   */
  protected $body;

  /**
   * From email
   *
   * @var string
   */
  protected $from;

  /**
   * YmlpSendUnsubscribeEmailEvent constructor.
   * @param $email
   * @param $subject
   * @param $body
   * @param $from
   */
  public function __construct($email, $subject, $body, $from) {
    $this->email = $email;
    $this->subject = $subject;
    $this->body = $body;
    $this->from = $from;
  }

  /**
   * Return email
   * @return string
   */
  public function getEmail(){
    return $this->email;
  }

  /**
   * Return subject
   * @return string
   */
  public function getSubject(){
    return !empty($this->subject) ? $this->subject : '';
  }

  /**
   * Return body
   * @return string
   */
  public function getBody(){
    return !empty($this->body) ? $this->body : '';
  }

  /**
   * Return from email
   * @return string
   */
  public function getFrom(){
    return $this->from;
  }

}
