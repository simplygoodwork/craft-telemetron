<?php
/**
 * Telemetron plugin for Craft CMS 4.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetron\console\controllers;

use simplygoodwork\telemetron\Telemetron;

use Craft;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Sync Command
 *
 * The first line of this class docblock is displayed as the description
 * of the Console Command in ./craft help
 *
 * Craft can be invoked via commandline console by using the `./craft` command
 * from the project root.
 *
 * Console Commands are just controllers that are invoked to handle console
 * actions. The segment routing is plugin-name/controller-name/action-name
 *
 * The actionIndex() method is what is executed if no sub-commands are supplied, e.g.:
 *
 * ./craft telemetron/sync
 *
 * Actions must be in 'kebab-case' so actionDoSomething() maps to 'do-something',
 * and would be invoked via:
 *
 * ./craft telemetron/sync/do-something
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     1.0.0
 */
class SyncController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================

    /**
     * Handle telemetron/sync console command
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $sync = Telemetron::$plugin->sync->sync();

        if($sync['success']){
          echo "Site successfully synced!\n";
        } else {
          echo "There was an error syncing the project.\n";
        }

        return $sync['success'];
    }
}
