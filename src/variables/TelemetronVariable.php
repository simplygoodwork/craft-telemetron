<?php
/**
 * Telemetron plugin for Craft CMS 3.x
 *
 * An internal project tracking tool.
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
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }
}
