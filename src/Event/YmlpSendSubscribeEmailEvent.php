<?php

namespace Drupal\ymlp\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user subscribe to ymlp
 */
class YmlpSendSubscribeEmailEvent extends Event {

  const EVENT_NAME = 'ymlp_send_subscriber_email';

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
   * From Email
   *
   * @var string
   */
  protected $from;

  /**
   * YmlpSendSubscribeEmailEvent constructor.
   * @param $email
   * @param $subject
   * @param $body
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
