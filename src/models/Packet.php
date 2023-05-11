<?php
/**
 * Telemetron plugin for Craft CMS 3.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetron\models;

use craft\helpers\App;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use simplygoodwork\telemetron\models\RemoteUpdates;
use simplygoodwork\telemetron\Telemetron;

use Craft;
use craft\base\Model;
use yii\base\NotSupportedException;

/**
 * Packet Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     1.0.0
 */
class Packet extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Site URL
     *
     * @var string
     */
    public string $siteUrl;

    /**
     * @var string
     */
    public string $siteName;

    /**
     * @var string
     */
    public string $serverIp;

    /**
     * @var string
     */
    public string $webroot;

    /**
     * @var ?string
     */
    public ?string $locales;

    /**
     * @var bool
     */
    public bool $isMultisite;

    /**
     * @var string|int
     */
    public string|int $craftVersion;

    /**
     * @var string
     */
    public string $craftEdition;

    /**
     * @var string|int
     */
    public string|int $phpVersion;

    /**
     * @var string|int
     */
    public string|int $dbVersion;

    /**
     * @var array
     */
    public array $plugins;

    /**
     * @var array
     */
    public array $cms;

    /**
     * @var array
     */
    public array $updates;

    /**
     * @var bool
     */
    public bool $isCommerce = false;

    public array $emailSettings = [];

    protected ?RemoteUpdates $pluginUpdateData = null;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        $config['siteUrl'] = UrlHelper::siteUrl('/');
        $config['siteName'] = Craft::$app->getSystemName();
        $config['serverIp'] = $_SERVER['SERVER_ADDR'] ?? '';
        $config['webroot'] = Craft::getAlias('@webroot');
        $config['isMultisite'] = Craft::$app->getIsMultiSite();
        $config['craftVersion'] = Craft::$app->getVersion();
        $config['craftEdition'] = App::editionName(Craft::$app->getEdition());
        $config['phpVersion'] = App::phpVersion();
        $config['plugins'] = $this->_getPlugins();
        $config['cms'] = $this->_getCmsUpdates();
        $config['updates'] = $this->_getUpdateSummary();

        $this->_setEmailAttributes();

        $config['locales'] = '';
        if ($config['isMultisite']) {
            $config['locales'] = self::_getMultiSiteString();
        }

        try {
            $config['dbVersion'] = self::_dbDriver();
        } catch (NotSupportedException $e) {
            $config['dbVersion'] = 'Error';
        }

        parent::__construct($config);
    }

    //  /**
    //   * Returns the validation rules for attributes.
    //   *
    //   * @return array
    //   */
    //  public function rules()
    //  {
    //    return [
    //      ['someAttribute', 'string'],
    //      ['someAttribute', 'default', 'value' => 'Some Default'],
    //    ];
    //  }

    /**
     * Returns the DB driver name and version
     *
     * @return string
     * @throws \yii\base\NotSupportedException
     */
    private static function _dbDriver(): string
    {
        $db = Craft::$app->getDb();

        if ($db->getIsMysql()) {
            $driver = 'MySQL';
        } else {
            $driver = 'PostgreSQL';
        }

        return $driver . ' ' . App::normalizeVersion($db->getSchema()->getServerVersion());
    }

    /**
     * returns all of the site names and urls
     *
     * @return string
     */
    private static function _getMultiSiteString(): string
    {
        $sites = Craft::$app->sites->getAllSites();

        $sitesString = "";
        foreach ($sites as $site) {
            $name = $site->name;
            $url = $site->baseUrl;
            $sitesString .= "${name} (${url}) \n";
        }
        return $sitesString;
    }

    private function _getPlugins(): array
    {
        $allPlugins = Craft::$app->plugins->getAllPluginInfo();
        $pluginUpdateData = $this->_getUpdates();
        $plugins = [];

        foreach ($allPlugins as $handle => $plugin) {
            if ($plugin['isEnabled']) {
                if ($plugin['name'] === 'Commerce') {
                    $this['isCommerce'] = true;
                }

                $plugins[$handle] = (new Plugin([
                    'name' => $plugin['name'],
                    'version' => $plugin['version'],
                    'edition' => $plugin['edition'],
                    'licensedEdition' => $plugin['licensedEdition'],
                    'licenseKeyStatus' => $plugin['licenseKeyStatus'],
                    'licenseIssues' => $plugin['licenseIssues'],
                    'developer' => $plugin['developer'],
                    'description' => $plugin['description'],
                    'isTrial' => $plugin['isTrial'],
                    'upgradeAvailable' => $plugin['upgradeAvailable'],
                    'private' => $plugin['private'] ?? false,
                ]))->toArray();

                if (isset($pluginUpdateData['plugins'][$handle])) {
                    // If it's a model, convert to array, otherwise we get yii validators and stuff in the output
                    $hasCritical = $pluginUpdateData['plugins'][$handle]->getHasCritical();
                    $data = $pluginUpdateData['plugins'][$handle]->toArray();
                    $data['phpConstraint'] = preg_replace('/[^0-9.]/', '', $data['phpConstraint']);
                    $data['hasCritical'] = $hasCritical;
                    $plugins[$handle] = ArrayHelper::merge($plugins[$handle], $data);
                }
            }
        }

        return $plugins;
    }

    private function _getCmsUpdates(): array
    {
        $updateData = $this->_getUpdates();

        if (!isset($updateData['cms'])) {
            return [];
        }

        $hasCritical = $updateData['cms']->getHasCritical();
        $data = $updateData['cms']->toArray();
        $data['phpConstraint'] = preg_replace('/[^0-9.]/', '', $data['phpConstraint']);
        $data['hasCritical'] = $hasCritical;
        return $data;
    }

    /**
     * @return array
     */
    private function _getUpdateSummary(): array
    {
        $updates = $this->_getUpdates();

        return [
            'total' => $updates->getTotal(),
            'critical' => $updates->getHasCritical(),
            'expired' => $updates->getExpired(),
            'breakpoints' => $updates->getBreakpoints(),
            'abandoned' => $updates->getAbandoned(),
        ];
    }

    /**
     * @return RemoteUpdates
     */
    private function _getUpdates(): RemoteUpdates
    {
        if($this->pluginUpdateData) {
            return $this->pluginUpdateData;
        } else {
            $this->pluginUpdateData = Craft::$app->cache->getOrSet('telemetron-plugin-update-data', function(){
                return new RemoteUpdates(Craft::$app->getApi()->getUpdates());
            }, 600);
        }

        return $this->pluginUpdateData;
    }

    private function _setEmailAttributes(): void
    {
        $ms = App::mailSettings();
        $transportType = explode('\\', $ms->transportType);

        $this->emailSettings = [
            'sender' => App::parseEnv($ms->fromEmail),
            'replyTo' => App::parseEnv($ms->fromEmail),
            'fromName' => App::parseEnv($ms->fromName),
            'transportType' => strtoupper(end($transportType)),
        ];

        if (isset($ms->transportSettings['encryptionMethod'])) {
            $this->emailSettings['encryptionMethod'] = App::parseEnv($ms->transportSettings['encryptionMethod']);
        }

        if (isset($ms->transportSettings['host'])) {
            $this->emailSettings['host'] = App::parseEnv($ms->transportSettings['host']);
        }

        if (isset($ms->transportSettings['username'])) {
            $this->emailSettings['username'] = App::parseEnv($ms->transportSettings['username']);
        }

        if (isset($ms->transportSettings['useAuthentication'])) {
            $this->emailSettings['useAuthentication'] = App::parseBooleanEnv($ms->transportSettings['useAuthentication']);
        }

        if (isset($ms->transportSettings['command'])) {
            $this->emailSettings['command'] = $ms->transportSettings['command'];
        }

    }

}
