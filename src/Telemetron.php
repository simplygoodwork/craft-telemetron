<?php
/**
 * Telemetron plugin for Craft CMS 4.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetron;

use simplygoodwork\telemetron\assetbundles\telemetron\TelemetronAsset;
use simplygoodwork\telemetron\variables\TelemetronVariable;
use simplygoodwork\telemetron\models\Settings;
use simplygoodwork\telemetron\utilities\TelemetronSync as TelemetronSyncUtility;
use simplygoodwork\telemetron\widgets\TelemetronWidget;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\web\UrlManager;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

use nystudio107\pluginvite\services\VitePluginService;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     1.0.0
 *
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Telemetron extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Telemetron::$plugin
     *
     * @var Telemetron
     */
    public static $plugin;

    // Public Properties
    // =========================================================================
    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     */
    public string $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     */
    public bool $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     */
    public bool $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
      $config['components'] = [
        'telemetron' => self::class,
        // Register the vite service
        'vite' => [
          'class' => VitePluginService::class,
          'assetClass' => TelemetronAsset::class,
          'useDevServer' => false,
          'devServerPublic' => 'http://localhost:3002',
          'serverPublic' => 'http://localhost:8000',
          'errorEntry' => 'src/index.js',
          'devServerInternal' => 'http://host.docker.internal:3002',
          'checkDevServer' => false,
        ],
      ];

      parent::__construct($id, $parent, $config);
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        $this->_registerComponents();

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'simplygoodwork\telemetron\console\controllers';
        }

        // Register our utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            static function (RegisterComponentTypesEvent $event) : void {
                $event->types[] = TelemetronSyncUtility::class;
            }
        );

        // Register our widgets
//        Event::on(
//            Dashboard::class,
//            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
//            function (RegisterComponentTypesEvent $event) {
//                $event->types[] = TelemetronWidget::class;
//            }
//        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event): void {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('telemetron', [
                  'class' => TelemetronVariable::class,
                  'viteService' => $this->vite,
                ]);
            }
        );

/**
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'telemetron',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================
    /**
     * Creates and returns the model used to store the plugin’s settings.
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'telemetron/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

  /**
   * Registers components.
   */
  private function _registerComponents(): void
  {
    // Register services as components
    $this->setComponents([
//      'sync' => SyncService::class,
    ]);
  }
}
