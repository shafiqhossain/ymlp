<?php

namespace Drupal\ymlp\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Ymlp subscriber edit forms.
 *
 * @ingroup ymlp
 */
class YmlpSubscriberForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ymlp\Entity\YmlpSubscriber */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %mail as Ymlp subscriber.', [
          '%mail' => $entity->getEmailAddress(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %mail as Ymlp subscriber.', [
          '%mail' => $entity->getEmailAddress(),
        ]));
    }
    $form_state->setRedirect('entity.ymlp_subscriber.canonical', ['ymlp_subscriber' => $entity->id()]);
  }

}
