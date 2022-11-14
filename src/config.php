<?php
/**
 * Telemetron plugin for Craft CMS 4.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

/**
 * Telemetron config.php
 *
 * This file exists only as a template for the Telemetron settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'telemetron.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    'baseId' => getenv('TELEMETRON_BASE_ID'),
    'apiKey' => getenv('TELEMETRON_API_KEY'),
    'tableName' => '',
    'syncEnabled' => getenv('TELEMETRON_ENABLED')
];
