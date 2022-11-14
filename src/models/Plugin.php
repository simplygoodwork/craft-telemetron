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

  class Plugin extends Model
  {

    /**
     * Plugin Name
     */
    public string $name;

    /**
     * Plugin version
     */
    public string $version;

    /**
     * "hash" of the plugin + version, used as ID in Airtable
     */
    public string $hash;

    /**
     * Plugin edition (lite, pro, standard)
     */
    public string $edition;

    /**
     * License status ('trial', 'valid', 'unknown' => '')
     */
    public string $licenseKeyStatus;

    /**
     * Documentation URL
     */
    public ?string $documentationUrl = '';


    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
      $name = $config['name'];
      $version = $config['version'];
      $config['hash'] = sprintf('%s (%s)', $name, $version);
      $config['edition'] = StringHelper::toTitleCase($config['edition']);

      if($config['licenseKeyStatus'] === 'unknown'){
        $config['licenseKeyStatus'] = 'Not Required';
      }

      $config['licenseKeyStatus'] = StringHelper::toTitleCase($config['licenseKeyStatus']);


      parent::__construct($config);
    }
  }