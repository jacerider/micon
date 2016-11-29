<?php

/**
 * @file
 * Contains \Drupal\micon\TwigExtension\Micon.
 */

namespace Drupal\micon\TwigExtension;

class Micon extends \Twig_Extension {

  /**
   * Gets a unique identifier for this Twig extension.
   *
   * @return string
   *   A unique identifier for this Twig extension.
   */
  public function getName() {
    return 'twig.micon';
  }


  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('micon', array($this, 'renderIcon')),
    );
  }


  public static function renderIcon($icon) {
    $build = [
      '#theme' => 'micon_icon',
      '#icon' => $icon
    ];
    return $build;
  }

}
