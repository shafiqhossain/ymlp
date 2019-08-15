<?php
/**
* @file
* Contains \Drupal\ymlp\Plugin\Block\YmlpSubscriptionBlock.
*/

namespace Drupal\ymlp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
* Ymlp Subscription Block
*
* @Block(
* id = "ymlp_subscription_block",
* admin_label = @Translation("Ymlp Subscription Block"),
* category = @Translation("Ymlp"),
* )
*/
class YmlpSubscriptionBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form_builder Service.
   *
   * @var FormBuilder $formBuilder
   */
  protected $formBuilder;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  protected $config_factory;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $formBuilder, ConfigFactoryInterface $config_factory) {
    //Call parent construct method.
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
	$this->formBuilder = $formBuilder;
	$this->config_factory = $config_factory;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
	  $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
	  $container->get('form_builder'),
	  $container->get('config.factory')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'description' => $this->t('Stay informed - subscribe to our newsletter.'),
      'subscribe_message' => $this->t('Thank you for your subscription!'),
      'unsubscribe_message' => $this->t('You have been un-subscribed successfully!'),
      'groups' => array(),
      'unique_id' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();
    $ymlpConfig = $this->config_factory->get('ymlp.settings');

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => !empty($this->configuration['description']) ? $this->configuration['description'] : $this->defaultConfiguration()['description'],
      '#required' => FALSE,
    ];

    $form['subscribe_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Subscribe Message'),
      '#default_value' => !empty($this->configuration['subscribe_message']) ? $this->configuration['subscribe_message'] : $this->defaultConfiguration()['subscribe_message'],
      '#required' => FALSE,
    ];

    $form['unsubscribe_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Unsubscribe Message'),
      '#default_value' => !empty($this->configuration['unsubscribe_message']) ? $this->configuration['unsubscribe_message'] : $this->defaultConfiguration()['unsubscribe_message'],
      '#required' => FALSE,
    ];

    $options = array();
    if(!empty($ymlpConfig->get('api_key')) && !empty($ymlpConfig->get('api_username')) ) {
	  $ymlp = \Drupal::service('ymlp.helper');
	  $groups = $ymlp->groups();
      if($groups['status'] == 1) {
        $lists = $groups['list'];
      }
      else {
        $lists = array();
      }

      if(count($lists)>0) {
        foreach($lists as $key => $value) {
    	  $options[$key] = $value;
        }
      }
    }
    $form['groups'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Email Groups'),
	  '#options' => $options,
      '#default_value' => !empty($this->configuration['groups']) ? $this->configuration['groups'] : $this->defaultConfiguration()['groups'],
	  '#required' => FALSE,
      '#empty_option' => $this->t('--Select--'),
    ];

    $form['unique_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Unique ID'),
      '#size' => 60,
      '#maxlength' => 255,
      '#description' => t('Each subscription block must have a unique form ID. If no value is provided, a random ID will be generated. Use this to have a predictable, short ID, e.g. to configure this form use a CAPTCHA.'),
      '#default_value' => $this->configuration['unique_id'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['groups'] = array_filter($form_state->getValue('groups'));
    $this->configuration['subscribe_message'] = $form_state->getValue('subscribe_message');
    $this->configuration['unsubscribe_message'] = $form_state->getValue('unsubscribe_message');
    $this->configuration['unique_id'] = empty($form_state->getValue('unique_id')) ? \Drupal::service('uuid')->generate() : $form_state->getValue('unique_id');
  }

  /**
  * {@inheritdoc}
  */
  public function build() {
    /** @var \Drupal\ymlp\Form\YmlpSubscriptionForm $form_object */
	$form_object = \Drupal::service('ymlp.subscription_form');
    $form_object->setUniqueId($this->configuration['unique_id']);
    $form_object->setGroups($this->configuration['groups']);
    $form_object->setMessage($this->configuration['description']);
    $form_object->setSubscribeMessage($this->configuration['subscribe_message']);
    $form_object->setUnsubscribeMessage($this->configuration['unsubscribe_message']);

    return $this->formBuilder->getForm($form_object);
  }

}
