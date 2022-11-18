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

use craft\behaviors\EnvAttributeParserBehavior;
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
	 * @var bool
	 */
	public $syncEnabled = false;

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
	 * @inheritdoc
	 */
	public function behaviors(): array
	{
		return [
			'parser' => [
				'class' => EnvAttributeParserBehavior::class,
				'attributes' => ['baseId', 'tableName', 'apiKey'],
			],
		];
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

}
