<?php
/**
 * Telemetron plugin for Craft CMS 4.x
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
  public $baseId = '$TELEMETRON_BASE_ID';

  /**
   * @var string
   */
  public $apiKey = '$TELEMETRON_API_KEY';

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
    $config['tableName'] = StringHelper::toTitleCase(App::env('ENVIRONMENT')) . ' Inventory';

    parent::__construct($config);
  }

  /**
   * Returns the validation rules for attributes.
   *
   * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
   *
   * @return array<int, array<string[]|string>>
   */
  public function rules(): array
  {
      return [
          [['baseId', 'apiKey', 'tableName'], 'string'],
          [['baseId', 'apiKey', 'tableName', 'syncEnabled'], 'required'],
      ];
  }

  public function getBaseId(): string
  {
	  if(!empty($this->baseId)){
		  return App::parseEnv($this->baseId);
	  }
	  return App::env("TELEMETRON_BASE_ID");
  }

  public function getApiKey(): string
  {
	  if(!empty($this->apiKey)){
		  return App::parseEnv($this->apiKey);
	  }
	  return App::env("TELEMETRON_API_KEY");
  }

  public function getTableName(): string
  {
    return App::parseEnv($this->tableName);
  }

	public function getSyncEnabled(): bool
	{
		// if env var has been set in settings but the env var has not been set and we're in production, turn on sync
		if($this->syncEnabled !== '0' && $this->syncEnabled !== '1' && !isset($_ENV[$this->syncEnabled]) && App::env('ENVIRONMENT') === 'production')
		{
			return true;
		}

		return App::parseBooleanEnv($this->syncEnabled) ?? false;
	}

}
