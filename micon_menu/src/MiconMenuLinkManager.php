<?php

namespace Drupal\micon_menu;

use Drupal\Core\Menu\MenuLinkManager;

/**
 * Modifies the language manager service.
 */
class MiconMenuLinkManager extends MenuLinkManager {

  /**
   * {@inheritdoc}
   */
  public function updateDefinition($id, array $new_definition_values, $persist = TRUE) {
    $instance = $this->createInstance($id);
    if ($instance) {
      $new_definition_values['id'] = $id;
      $changed_definition = $instance->updateLink($new_definition_values, $persist);
      if (isset($new_definition_values['data-icon'])) {
        $changed_definition['options']['attributes']['data-icon'] = $new_definition_values['data-icon'];
      }
      $this->treeStorage->save($changed_definition);
    }
    return $instance;
  }

}
