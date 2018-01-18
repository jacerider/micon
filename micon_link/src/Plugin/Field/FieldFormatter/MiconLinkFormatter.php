<?php

namespace Drupal\micon_link\Plugin\Field\FieldFormatter;

use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\micon\MiconIconizeTrait;
use Drupal\Core\Path\PathValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Utility\Token;

/**
 * Plugin implementation of the 'micon_link' formatter.
 *
 * @FieldFormatter(
 *   id = "micon_link",
 *   label = @Translation("Link (with icon)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class MiconLinkFormatter extends LinkFormatter {
  use MiconIconizeTrait;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, PathValidatorInterface $path_validator, Token $token) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $path_validator);
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('path.validator'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'title' => '',
      'icon' => '',
      'position' => 'before',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    if ($title = $this->getSetting('title')) {
      $summary[] = t('Link title as @title', ['@title' => $title]);
    }
    if ($icon = $this->getSetting('icon')) {
      $summary[] = $this->micon('Icon as')->setIcon($icon)->setIconAfter();
    }
    if ($position = $this->getSetting('position')) {
      $summary[] = t('Icon position: @value', ['@value' => ucfirst($position)]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#default_value' => $this->getSetting('title'),
      '#description' => $this->t('Will be used as the link title even if one has been set on the field. Supports token replacement.'),
    ];
    $form['icon'] = [
      '#type' => 'micon',
      '#title' => $this->t('Link icon'),
      '#default_value' => $this->getSetting('icon'),
      '#description' => $this->t('Will be used as the link icon even if one has been set on the field.'),
    ];
    $form['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Icon position'),
      '#options' => ['before' => $this->t('Before'), 'after' => $this->t('After')],
      '#default_value' => $this->getSetting('position'),
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = parent::viewElements($items, $langcode);
    $entity = $items->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $title = $this->getSetting('title');
    $position = $this->getSetting('position');
    foreach ($element as &$item) {
      $icon = $this->getSetting('icon');
      if ($title) {
        $item['#title'] = $this->token->replace($title, [$entity_type => $entity]);
      }
      if (!$icon && !empty($item['#options']['attributes']['data-icon'])) {
        $icon = $item['#options']['attributes']['data-icon'];
      }
      if ($icon) {
        $micon = $this->micon($item['#title'])->setIcon($icon);
        if ($position == 'after') {
          $micon->setIconAfter();
        }
        $item['#title'] = $micon;
        unset($item['#options']['attributes']['data-icon']);
      }
    }
    return $element;
  }

}
