<?php

namespace Drupal\ymlp\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Ymlp subscriber entities.
 *
 * @ingroup ymlp
 */
class YmlpSubscriberDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %mail?', ['%mail' => $this->entity->getEmailAddress()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deleting the email %mail, will un-subscribe from Ymlp as well. This action can not be undone.', ['%mail' => $this->entity->getEmailAddress()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.ymlp_subscriber.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    drupal_set_message(
      $this->t('Email @mail has beed deleted successfully!',
        [
          '@mail' => $this->entity->getEmailAddress(),
        ]
      )
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
