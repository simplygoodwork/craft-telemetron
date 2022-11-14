<?php
/**
 * Telemetron plugin for Craft CMS 4.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetron\utilities;

use simplygoodwork\telemetron\Telemetron;
use simplygoodwork\telemetron\assetbundles\telemetronsyncutility\TelemetronSyncUtilityAsset;

use Craft;
use craft\base\Utility;

/**
 * Telemetron Utility
 *
 * Utility is the base class for classes representing Control Panel utilities.
 *
 * https://craftcms.com/docs/plugins/utilities
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     1.0.0
 */
class TelemetronSync extends Utility
{
    // Static
    // =========================================================================

    /**
     * Returns the display name of this utility.
     *
     * @return string The display name of this utility.
     */
    public static function displayName(): string
    {
        return Craft::t('telemetron', 'Telemetron Sync');
    }

    /**
     * Returns the utility’s unique identifier.
     *
     * The ID should be in `kebab-case`, as it will be visible in the URL (`admin/utilities/the-handle`).
     */
    public static function id(): string
    {
        return 'telemetron-sync';
    }

    /**
     * Returns the path to the utility's SVG icon.
     *
     * @return string|null The path to the utility SVG icon
     */
    public static function iconPath(): ?string
    {
        return Craft::getAlias("@simplygoodwork/telemetron/icon.svg");
    }

    /**
     * Returns the number that should be shown in the utility’s nav item badge.
     *
     * If `0` is returned, no badge will be shown
     */
    public static function badgeCount(): int
    {
        return 0;
    }

    /**
     * Returns the utility's content HTML.
     */
    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(TelemetronSyncUtilityAsset::class);

        $someVar = 'Have a nice day!';
        return Craft::$app->getView()->renderTemplate(
            'telemetron/_components/utilities/TelemetronSync_content',
            [
                'someVar' => $someVar
            ]
        );
    }
}
