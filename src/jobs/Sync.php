<?php
/**
 * Telemetron plugin for Craft CMS 3.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetron\jobs;

use simplygoodwork\telemetron\Telemetron;

use Craft;
use craft\queue\BaseJob;

/**
 * Sync job
 *
 * Jobs are run in separate process via a Queue of pending jobs. This allows
 * you to spin lengthy processing off into a separate PHP process that does not
 * block the main process.
 *
 * You can use it like this:
 *
 * use simplygoodwork\telemetron\jobs\Sync as SyncJob;
 *
 * $queue = Craft::$app->getQueue();
 * $jobId = $queue->push(new SyncJob([
 *     'description' => Craft::t('telemetron', 'This overrides the default description'),
 *     'someAttribute' => 'someValue',
 * ]));
 *
 * The key/value pairs that you pass in to the job will set the public properties
 * for that object. Thus whatever you set 'someAttribute' to will cause the
 * public property $someAttribute to be set in the job.
 *
 * Passing in 'description' is optional, and only if you want to override the default
 * description.
 *
 * More info: https://github.com/yiisoft/yii2-queue
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     1.0.0
 */
class Sync extends BaseJob
{

    // Public Methods
    // =========================================================================

    /**
     */
    public function execute($queue)
    {
        $sync = Telemetron::$plugin->sync->sync();
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns a default description for [[getDescription()]], if [[description]] isn’t set.
     *
     * @return string The default task description
     */
    protected function defaultDescription(): string
    {
        return Craft::t('telemetron', 'Syncing project telemetry.');
    }
}
