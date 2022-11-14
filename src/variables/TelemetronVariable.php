<?php
/**
 * Telemetron plugin for Craft CMS 4.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetron\variables;

use nystudio107\pluginvite\variables\ViteVariableInterface;
use nystudio107\pluginvite\variables\ViteVariableTrait;
use simplygoodwork\telemetron\Telemetron;

use Craft;

/**
 * Telemetron Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.telemetron }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     1.0.0
 */
class TelemetronVariable implements ViteVariableInterface
{
  use ViteVariableTrait;
    // Public Methods
    // =========================================================================
    /**
     * @param null $optional
     */
    public function exampleVariable($optional = null): string
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }

        return $result;
    }
}
