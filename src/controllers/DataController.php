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
use simplygoodwork\telemetron\models\Packet;
use simplygoodwork\telemetron\Telemetron;

use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;

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
class DataController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'test'];

    /**
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        if(!$this->_auth()) {
            throw new NotFoundHttpException();
        }

        $packet = new Packet();

        return $this->asJson($packet);
    }

    public function actionTest()
    {
        return $this->asJson(Craft::$app->plugins->getAllPluginInfo());
    }

    private function _auth()
    {
        $headers = $this->request->getHeaders();
        $token = $headers->get('X-REMOTE-KEY');

        if(!$token) {
            return false;
        }

        $pluginKey = App::parseEnv(Telemetron::$plugin->getSettings()->apiKey) ?? Craft::parseEnv(Telemetron::$plugin->getSettings()->apiKey);

        if(!$pluginKey || $pluginKey !== $token) {
            return false;
        }

        return true;
    }
}
