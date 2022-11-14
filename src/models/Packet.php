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
  public $siteUrl;

	/**
	 * @var string
	 */
	public $id;

  /**
   * @var string
   */
  public $siteName;

  /**
   * @var string
   */
  public $serverIp;

  /**
   * @var string
   */
  public $webroot;

  /**
   * @var ?string
   */
  public $locales;

  /**
   * @var bool
   */
  public $isMultiSite;

  /**
   * @var string|int
   */
  public $craftVersion;

  /**
   * @var string
   */
  public $craftEdition;

  /**
   * @var string|int
   */
  public $phpVersion;

  /**
   * @var string|int
   */
  public $dbVersion;

  /**
   * @var array
   */
  public $plugins;

	/**
	 * @var array
	 */
  public $pluginHashes = [];

  /**
   * @var bool
   */
  public $isCommerce = false;

	public $emailSettings = [];



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
    $config['isMultiSite'] = Craft::$app->getIsMultiSite();
    $config['craftVersion'] = Craft::$app->getVersion();
    $config['craftEdition'] = App::editionName(Craft::$app->getEdition());
    $config['phpVersion'] = App::phpVersion();
    $config['plugins'] = $this->_getPlugins();
		$config['id'] = md5(Craft::$app->getSystemName() . App::env('ENVIRONMENT') . App::env('APP_ID'));

		$this->_setEmailAttributes();

    $config['locales'] = '';
    if($config['isMultiSite']){
      $config['locales'] = self::_getMultiSiteString();
    }

    try{
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

public function getPluginHashes(): array
{
  $pluginHashes = [];
  foreach($this->plugins as $plugin){
    $pluginHashes[] = $plugin->hash;
  }

  $this->pluginHashes = $pluginHashes;
  return $this->pluginHashes;
}

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
    foreach($sites as $site){
      $name = $site->name;
      $url = $site->baseUrl;
      $sitesString .= "${name} (${url}) \n";
    }
    return $sitesString;
  }

  private function _getPlugins(): array
  {
    $allPlugins = Craft::$app->plugins->getAllPluginInfo();

    $plugins = [];

    foreach($allPlugins as $plugin){
      if($plugin['isEnabled']){
        if($plugin['name'] === 'Commerce'){
          $this['isCommerce'] = true;
        }
        $plugins[] = new Plugin([
          'name' => $plugin['name'],
          'version' => $plugin['version'],
          'edition' => $plugin['edition'],
          'licenseKeyStatus' => $plugin['licenseKeyStatus'],
          'documentationUrl' => $plugin['documentationUrl']
        ]);
      }
    }

    return $plugins;
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

		if(isset($ms->transportSettings['encryptionMethod'])){
			$this->emailSettings['encryptionMethod'] = App::parseEnv($ms->transportSettings['encryptionMethod']);
		}

		if(isset($ms->transportSettings['host'])){
			$this->emailSettings['host'] = App::parseEnv($ms->transportSettings['host']);
		}

		if(isset($ms->transportSettings['username'])){
			$this->emailSettings['username'] = App::parseEnv($ms->transportSettings['username']);
		}

		if(isset($ms->transportSettings['useAuthentication'])){
			$this->emailSettings['useAuthentication'] = App::parseBooleanEnv($ms->transportSettings['useAuthentication']);
		}

		if(isset($ms->transportSettings['command'])){
			$this->emailSettings['command'] = $ms->transportSettings['command'];
		}

	}

}
