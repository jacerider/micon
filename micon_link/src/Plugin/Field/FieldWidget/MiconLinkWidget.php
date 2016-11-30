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
    return array(
      'placeholder_url' => '',
      'placeholder_title' => '',
      'packages' => [],
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['packages'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Icon Packages'),
      '#default_value' => $this->getSetting('packages'),
      '#description' => t('The icon packages that should be made available in this field. If no packages are selected, all will be made available.'),
      '#options' => Micon::loadActiveLabels(),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $build_info = $form_state->getBuildInfo();

    $item = $items[$delta];
    $options = $item->get('options')->getValue();
    $attributes = isset($options['attributes']) ? $options['attributes'] : [];

    $element['options']['attributes']['data-icon'] = [
      '#type' => 'micon',
      '#title' => $this->t('Icon'),
      '#default_value' => isset($attributes['data-icon']) ? $attributes['data-icon'] : NULL,
      '#packages' => $this->getPackages(),
      '#element_validate' => array(array(get_called_class(), 'validateIconElement')),
    ];

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
    if ($values = $form_state->getValue('link')) {
      foreach ($values as &$value) {
        if (empty($value['options']['attributes']['data-icon'])) {
          unset($value['options']['attributes']['data-icon']);
        }
        if (empty($value['options']['attributes'])) {
          unset($value['options']['attributes']);
        }
        if (empty($value['options'])) {
          unset($value['options']);
        }
      }
      $form_state->setValue('link', $values);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $enabled_packages = array_filter($this->getSetting('packages'));
    if ($enabled_packages) {
      $enabled_packages = array_intersect_key(Micon::loadActiveLabels(), $enabled_packages);
      $summary[] = $this->t('With icon packages: @packages', array('@packages' => implode(', ', $enabled_packages)));
    } else {
      $summary[] = $this->t('With icon packages: @packages', array('@packages' => 'All'));
    }
    return $summary;
  }

}
