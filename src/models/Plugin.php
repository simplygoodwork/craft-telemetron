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

  class Plugin extends Model
  {

    /**
     * Plugin Name
     * @var string
     */
    public string $name;

    /**
     * Plugin version
     * @var string
     */
    public string $version;

    /**
     * "hash" of the plugin + version, used as ID in Airtable
     * @var string
     */
    public string $hash;

    /**
     * Plugin edition (lite, pro, standard)
     * @var string
     */
    public string $edition;

    /**
     * License status ('trial', 'valid', 'unknown' => '')
     * @var string
     */
    public string $licenseKeyStatus;

    /**
     * Documentation URL
     * @var null|string
     */
    public ?string $documentationUrl = '';


    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
      $name = $config['name'];
      $version = $config['version'];
      $config['hash'] = "${name} (${version})";
      $config['edition'] = StringHelper::toTitleCase($config['edition']);

      if($config['licenseKeyStatus'] === 'unknown'){
        $config['licenseKeyStatus'] = 'Not Required';
      }

      $config['licenseKeyStatus'] = StringHelper::toTitleCase($config['licenseKeyStatus']);


      parent::__construct($config);
    }
  }