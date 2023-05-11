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
     * Plugin edition (lite, pro, standard)
     * @var string
     */
    public string $edition;

    public ?string $licensedEdition;

    public array $licenseIssues;

    public string $issueText = '';

    public string $developer;

    public string $description;

    public bool $isTrial;

    public ?bool $upgradeAvailable;

    public bool $private;

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
        $config['edition'] = StringHelper::toTitleCase($config['edition']);

        if($config['licenseKeyStatus'] === 'unknown'){
            $config['licenseKeyStatus'] = 'Not Required';
        }

        $config['licenseKeyStatus'] = StringHelper::toTitleCase($config['licenseKeyStatus']);

        if (count($config['licenseIssues'])) {
            $config['issueText'] = implode(' ', $config['licenseIssues']);
        }

        parent::__construct($config);
    }
}
