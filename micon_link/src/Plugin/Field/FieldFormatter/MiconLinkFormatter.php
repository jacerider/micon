<?php

namespace Drupal\micon_link\Plugin\Field\FieldFormatter;

use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\micon\MiconIconizeTrait;

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
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = parent::viewElements($items, $langcode);
    foreach ($element as $delta => &$item) {
      if (!empty($item['#options']['attributes']['data-icon'])) {
        $item['#title'] = $this->micon($item['#title'])->setIcon($item['#options']['attributes']['data-icon']);
        unset($item['#options']['attributes']['data-icon']);
      }
    }
    return $element;
  }

}
