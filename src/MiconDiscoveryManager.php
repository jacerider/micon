<?php

namespace Drupal\micon;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;

/**
 * Provides the default micon.icon manager.
 */
class MiconDiscoveryManager extends DefaultPluginManager {

  /**
   * Provides default values for all micon.icon plugins.
   *
   * @var array
   */
  protected $defaults = array(
    'text' => '',
    'regex' => '',
    'icon' => '',
    'weight' => 0,
  );

  /**
   * Constructs a MiconDiscoveryManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    // Add more services as required.
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'micon.discovery', array('micon.discovery'));
  }

  /**
   * Match a string against the icon definitions.
   *
   * @param string $string
   *   A string to match against icon definitions.
   *
   * @return string
   *   The icon id as defined within the definition.
   */
  public function getDefinitionMatch($string) {
    $definitions = $this->getDefinitions();
    $icon_id = NULL;
    // Check for exact string matches first.
    foreach ($definitions as $definition) {
      if ($definition['text'] && $definition['text'] == $string) {
        $icon_id = $definition['icon'];
        break;
      }
    }
    if (!$icon_id) {
      // Check for regex exact string matches second.
      foreach ($definitions as $definition) {
        if ($definition['regex'] && preg_match('!' . $definition['regex'] . '!', $string)) {
          $icon_id = $definition['icon'];
          break;
        }
      }
    }
    return $icon_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('micon.icons', $this->moduleHandler->getModuleDirectories());
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    if (empty($definition['id'])) {
      throw new PluginException(sprintf('Plugin (%s) definition must include "id".', $plugin_id));
    }

    if (empty($definition['icon'])) {
      throw new PluginException(sprintf('Plugin (%s) definition must include "icon".', $plugin_id));
    }

    if (empty($definition['text']) && empty($definition['regex'])) {
      throw new PluginException(sprintf('Plugin (%s) definition must include "text" or "regex".', $plugin_id));
    }
  }

}
