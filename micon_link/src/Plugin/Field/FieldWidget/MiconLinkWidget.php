<?php

namespace Drupal\micon_link\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Drupal\micon\Entity\Micon;

/**
 * Plugin implementation of the 'link' widget.
 *
 * @FieldWidget(
 *   id = "micon_link",
 *   label = @Translation("Link (with icon)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class MiconLinkWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'placeholder_url' => '',
      'placeholder_title' => '',
      'target' => FALSE,
      'packages' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['packages'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Icon Packages'),
      '#default_value' => $this->getSetting('packages'),
      '#description' => t('The icon packages that should be made available in this field. If no packages are selected, all will be made available.'),
      '#options' => Micon::loadActiveLabels(),
    ];

    $element['target'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow target selection'),
      '#description' => $this->t('If selected, an "open in new window" checkbox will be made available.'),
      '#default_value' => $this->getSetting('target'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['title']['#weight'] = -1;

    $item = $items[$delta];
    $options = $item->get('options')->getValue();
    $attributes = isset($options['attributes']) ? $options['attributes'] : [];

    $element['options']['attributes']['data-icon'] = [
      '#type' => 'micon',
      '#title' => $this->t('Icon'),
      '#default_value' => isset($attributes['data-icon']) ? $attributes['data-icon'] : NULL,
      '#packages' => $this->getPackages(),
      '#element_validate' => [[get_called_class(), 'validateIconElement']],
    ];

    if ($this->getSetting('target')) {
      $element['options']['attributes']['target'] = [
        '#type' => 'checkbox',
        '#title' => t('Open link in new window'),
        '#description' => t('If selected, the menu link will open in a new window/tab when clicked.'),
        '#default_value' => isset($attributes['target']),
        '#return_value' => '_blank',
        // '#element_validate' => [[get_called_class(), 'validateTargetElement']],
      ];
    }

    return $element;
  }

  /**
   * Get packages available to this field.
   */
  protected function getPackages() {
    return $this->getSetting('packages');
  }

  /**
   * Recursively clean up options array if no data-icon is set.
   */
  public static function validateIconElement($element, FormStateInterface $form_state, $form) {
    $parents = array_slice($element['#parents'], 0, -3);
    $values = $form_state->getValue($parents);
    if (!empty($values)) {
      foreach ($values['attributes'] as $attribute => $value) {
        if (!empty($value[$attribute])) {
          $values['options']['attributes'][$attribute] = $value[$attribute];
        }
        else {
          unset($values['options']['attributes'][$attribute]);
        }
      }
    }
    else {
      unset($values['options']['attributes']['data-icon']);
      unset($values['options']['attributes']['target']);
    }
    $form_state->setValue($parents, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $enabled_packages = array_filter($this->getSetting('packages'));
    if ($enabled_packages) {
      $enabled_packages = array_intersect_key(Micon::loadActiveLabels(), $enabled_packages);
      $summary[] = $this->t('With icon packages: @packages', ['@packages' => implode(', ', $enabled_packages)]);
    }
    else {
      $summary[] = $this->t('With icon packages: @packages', ['@packages' => 'All']);
    }
    if ($this->getSetting('target')) {
      $summary[] = $this->t('Allow target selection');
    }
    return $summary;
  }

}
