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
use craft\helpers\StringHelper;
use simplygoodwork\telemetron\Telemetron;

use Craft;
use craft\base\Model;

/**
 * Telemetron Settings Model
 *
 * This is a model used to define the plugin's settings.
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
class Settings extends Model
{
  // Public Properties
  // =========================================================================

  /**
   * Airtable Base ID
   *
   * @var string
   */
  public $baseId;

  /**
   * @var string
   */
  public $apiKey;

  /**
   * @var string
   */
  public $tableName;

	/**
	 * @var string
	 */
	public $syncEnabled;

  // Public Methods
  // =========================================================================

  /**
   * @inheritdoc
   */
  public function __construct(array $config = [])
  {
    $config['tableName'] = StringHelper::toTitleCase(getenv('ENVIRONMENT')) . ' Inventory';
    parent::__construct($config);
  }

  /**
   * Returns the validation rules for attributes.
   *
   * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
   *
   * @return array
   */
  public function rules()
  {
      return [
          [['baseId', 'apiKey', 'tableName'], 'string'],
          [['baseId', 'apiKey', 'tableName'], 'required'],
      ];
  }

  /**
   *
   * @return string
   */
  public function getBaseId(): string
  {
    if(!empty($this->baseId)){
      return getenv($this->baseId) ?? $this->baseId;
    }
    return getenv("TELEMETRON_BASE_ID") ?? '';
  }

  public function getApiKey(): string
  {
    if(!empty($this->apiKey)){
      return getenv($this->apiKey) ?? $this->apiKey;
    }
    return getenv("TELEMETRON_API_KEY") ?? '';
  }

  public function getTableName(): string
  {
    return getenv($this->tableName) ?? $this->tableName;
  }

	public function getSyncEnabled(): bool
	{
		// if env var has been set in settings but the env var has not been set and we're in production, turn on sync
		if($this->syncEnabled !== '0' && $this->syncEnabled !== '1' && !isset($_ENV[$this->syncEnabled]) && getenv('ENVIRONMENT') === 'production')
		{
			return true;
		}

    if(!empty($this->syncEnabled)){
      if(is_bool($this->syncEnabled)){
        return $this->syncEnabled;
      }
      $enabled = getenv($this->syncEnabled);
      $boolMap = [
        'true' => true,
        'false' => false,
        '1' => true,
        '0' => false,
        'yes' => true,
        'no' => false,
        'on' => true,
        'off' => false,
        '' => false,
      ];
      return $boolMap[$enabled] ?? $this->syncEnabled ?? false;
    }
    
		return getenv("TELEMETRON_ENABLED") ?? false;
	}

}
