<?php
/**
 * Telemetron plugin for Craft CMS 3.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetron\controllers;

use craft\helpers\App;
use craft\services\Sites;
use simplygoodwork\telemetron\jobs\Sync;
use simplygoodwork\telemetron\models\Packet;
use simplygoodwork\telemetron\Telemetron;

use Craft;
use craft\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * Sync Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
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
    protected $allowAnonymous = ['index', 'queue-sync'];

    // Public Methods
    // =========================================================================

	/**
	 * Immediately syncs project data to Airtable
	 *
	 * @return \yii\web\Response
	 * @throws ForbiddenHttpException
	 */
    public function actionIndex(): \yii\web\Response
    {
				$this->requireAdmin();
        return $this->asJson(Telemetron::$plugin->sync->sync());
    }

		/**
		 * @throws ForbiddenHttpException
		 */
		public function actionQueueSync(): \yii\web\Response
		{
			$this->requireAdmin();
      $queue = Craft::$app->getQueue()->push(new Sync());
      return $this->asJson('Sync job added to queue.');
    }
}
