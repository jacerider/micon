<?php

namespace Drupal\micon_link\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\micon\Entity\Micon;

/**
 * Class MiconLinkConfigForm.
 *
 * @package Drupal\micon_link\Form
 */
class MiconLinkConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'micon_link.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'micon_link_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('micon_link.config');
    $form['packages'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Icon Packages'),
      '#description' => $this->t('The icon packages that should be made available to menu items. If no packages are selected, all will be made available.'),
      '#options' => Micon::loadActiveLabels(),
      '#default_value' => $config->get('packages'),
    ];
    $form['menu_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable menu icons'),
      '#default_value' => $config->get('menu_enable'),
      '#description' => $this->t('Allow adding icons to menu items and render them.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('micon_link.config')
      ->set('packages', $form_state->getValue('packages'))
      ->set('menu_enable', $form_state->getValue('menu_enable'))
      ->save();

    drupal_flush_all_caches();
  }

}
