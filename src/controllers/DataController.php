<?php
/**
 * Telemetron plugin for Craft CMS 4.x
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
use yii\web\NotFoundHttpException;

/**
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     4.1.0
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
    protected array|int|bool $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================
    /**
     * Immediately syncs project data to Airtable
     *
     * @throws NotFoundHttpException
     */
    public function actionIndex(): \yii\web\Response
    {
        if(!$this->_auth()) {
            throw new NotFoundHttpException();
        }

        $packet = new Packet();

        return $this->asJson($packet);
    }

    private function _auth(): bool
    {
        $headers = $this->request->getHeaders();
        $token = $headers->get('X-REMOTE-KEY');

        if(!$token) {
            return false;
        }

        $pluginKey = App::parseEnv(Telemetron::$plugin->getSettings()->apiKey);

        if(!$pluginKey || $pluginKey !== $token) {
            return false;
        }

        return true;
    }
}
