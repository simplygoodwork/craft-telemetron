<?php
/**
 * Telemetron plugin for Craft CMS 4.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetron\services;

use craft\helpers\App;
use craft\helpers\Json;
use DateTime;
use simplygoodwork\telemetron\models\Packet;
use simplygoodwork\telemetron\Telemetron;

use Craft;
use craft\base\Component;
use simplygoodwork\telemetron\models\Plugin;
use Zadorin\Airtable\Client;
use Zadorin\Airtable\Errors\RequestError;
use Zadorin\Airtable\Record;

/**
 * Sync Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     1.0.0
 */
class Sync extends Component
{

  /**
   * @var Client
   */
  private $airtableClient;

  private static $_cacheKey = 'telemetron_daily_cache';

	public function dailySync():array
	{
		if(Craft::$app->cache->get(self::$_cacheKey) !== false || $this->_queueJobExists()){
			return [
				'success' => false,
				'response' => 'Daily sync already finished.'
			];
		}

		return $this->sync();
	}

	public function sync(): array
	{
		$settings = Telemetron::$plugin->settings;
		
		if(!$settings->getSyncEnabled()){
			return [
				'success' => false,
				'response' => 'Sync is not enabled in this environment.'
			];
		}

		$airtable = $this->_getAirtableClient();

		if(!$airtable){
			return [
				'success' => false,
				'response' => 'Missing Airtable credentials.'
			];
		}

		$packet = new Packet();

		$preflightSyncs = $this->_preflightSync($packet);

		if(!$preflightSyncs){
			return [
				'success' => false,
				'response' => 'Preflight checks failed.'
			];
		}

		$host = $packet->emailSettings['host'] ?? '';
		$smtpKey = "{$packet->emailSettings['transportType']} - {$host}({$packet->siteUrl})";

		$dataToSet = [
			'Name' => $packet->siteName,
			'Site URL' => $packet->siteUrl,
			'Plugins' => $packet->getPluginHashes(),
			'Server IP' => $packet->serverIp,
			'Webroot Path' => $packet->webroot,
			'Multisite' => $packet->isMultiSite,
			'Commerce' => $packet->isCommerce,
			'Craft Version' => [$packet->craftVersion],
			'Craft Edition' => $packet->craftEdition,
			'PHP Version' => [$packet->phpVersion],
			'Locales' => $packet->locales,
			'DB Version' => [$packet->dbVersion],
			'SMTP' => [$smtpKey]
		];

		$table = rawurlencode($settings->getTableName());
		try{
			$recordLookup = $airtable->table($table)
				->select('*')
				->where(['Name' => $packet->siteName])
				->limit(1)
				->execute();

			$recordExists = $recordLookup->count();

			if($recordExists){
				$record = $recordLookup->fetch();
				$record->setFields($dataToSet);
				$recordUpdate = $airtable->table($table)->update($record)->typecast()->execute();

				$this->_updateCache();

				return [
					'success' => true,
					'response' => $recordUpdate->fetch()->getFields()
				];
			}

			$insertRecord = $airtable->table($table)
				->insert([$dataToSet])
				->typecast()
				->execute();

			$this->_updateCache();

			return [
				'success' => true,
				'response' => $insertRecord->fetch()->getFields()
			];
		} catch(RequestError $e){
			if($airtable->getLastRequest()){
				Craft::error(Json::decode($airtable->getLastRequest()->getPlainResponse()), __METHOD__);
				return [
					'success' => false,
					'response' => Json::decode($airtable->getLastRequest()->getPlainResponse())
				];
			}
			return [
				'success' => false,
				'response' => $e->getMessage()
			];
		}
	}

	private function _getAirtableClient(): Client
	{
		$this->airtableClient = new Client(Telemetron::$plugin->getSettings()->getApiKey(), Telemetron::$plugin->getSettings()->getBaseId());
		return $this->airtableClient;
	}

	private function _queueJobExists(): bool
	{
		// Preflight check to ensure regular queue in place
		if(!Craft::$app->queue->hasProperty('jobInfo')){
			return false;
		}

		return in_array('Syncing project telemetry.', array_column(Craft::$app->queue->jobInfo, 'description'), true);
	}

	private function _updateCache():void
	{
		$dayInMs = 60 * 60 * 24;
		Craft::$app->cache->set(self::$_cacheKey, true, $dayInMs);
	}

	private function _preflightSync(Packet $packet): bool
	{
		$craftVersionSync = $this->_syncCraftVersion($packet->craftVersion);
		$phpVersionSync = $this->_syncPHPVersion($packet->phpVersion);
		$dbVersionSync = $this->_syncDatabaseVersion($packet->dbVersion);
		$pluginsSync = $this->_syncPlugins($packet->plugins);
		$smtpSync = $this->_syncSMTP($packet->emailSettings, $packet->siteUrl);

		if(!$craftVersionSync || !$phpVersionSync || !$dbVersionSync || !$pluginsSync || !$smtpSync){
			return false;
		}

		return true;
	}

	private function _syncCraftVersion(string $craftVersion): bool
	{
		$tableName = rawurlencode('Craft Versions');

		$recordLookup = $this->airtableClient->table($tableName)
			->select('*')
			->where(['Name' => $craftVersion])
			->limit(1)
			->execute();

		$recordExists = $recordLookup->count();

		if($recordExists){
			return true;
		}

		$insertRecord = $this->airtableClient->table($tableName)->insert([
			[
				'Name' => $craftVersion,
			]
		])->execute();

		if(!$insertRecord->fetch()->getId()){
			return false;
		}

		return true;
	}

	private function _syncPHPVersion(string $phpVersion): bool
	{
		$tableName = rawurlencode('PHP Versions');

		$recordLookup = $this->airtableClient->table($tableName)
			->select('*')
			->where(['Name' => $phpVersion])
			->limit(1)
			->execute();

		$recordExists = $recordLookup->count();

		if($recordExists){
			return true;
		}

		$insertRecord = $this->airtableClient->table($tableName)->insert([
			[
				'Name' => $phpVersion,
			]
		])->execute();

		if(!$insertRecord->fetch()->getId()){
			return false;
		}

		return true;
	}

	private function _syncDatabaseVersion(string $dbVersion): bool
	{
		$tableName = rawurlencode('Database Versions');

		$recordLookup = $this->airtableClient->table($tableName)
			->select('*')
			->where(['Name' => $dbVersion])
			->limit(1)
			->execute();

		$recordExists = $recordLookup->count();

		if($recordExists){
			return true;
		}

		$insertRecord = $this->airtableClient->table($tableName)->insert([
			[
				'Name' => $dbVersion,
			]
		])->execute();

		if(!$insertRecord->fetch()->getId()){
			return false;
		}

		return true;
	}

	private function _syncPlugins(array $plugins): bool
	{
		$tableName = rawurlencode('Plugins');

		foreach($plugins as $plugin){
			$recordLookup = $this->airtableClient->table($tableName)
				->select('Hash')
				->where(['Hash' => $plugin->hash])
				->limit(1)
				->execute();

			$recordExists = $recordLookup->count();

			if($recordExists){
				continue;
			}

			$insertRecord = $this->airtableClient->table($tableName)->insert([
				[
					'Hash' => $plugin->hash,
					'Name' => $plugin->name,
					'Version' => $plugin->version,
					'Documentation URL' => $plugin->documentationUrl,
				]
			])->execute();

			if(!$insertRecord->fetch()->getId()){
				Craft::error('Issue saving plugin record to Airtable.', __METHOD__);
			}
		}

		return true;
	}

	private function _syncSMTP($emailSettings, $siteUrl): bool
	{
		$tableName = rawurlencode('SMTP');
		$host = $emailSettings['host'] ?? '';
		$key = "{$emailSettings['transportType']} - {$host}({$siteUrl})";

		$dataToSet = [
			'Name' => $key,
			'Transportation Type' => $emailSettings['transportType'],
			'Host' => $emailSettings['host'] ?? '',
			'Username' => $emailSettings['username'] ?? '',
			'Use Authentication' => $emailSettings['useAuthentication'] ?? false,
			'Sender' => $emailSettings['sender'] ?? '',
			'Reply To' => $emailSettings['replyTo'] ?? '',
			'From Name' => $emailSettings['fromName'] ?? '',
			'Encryption Method' => $emailSettings['encryptionMethod'] ?? '',
			'Command' => $emailSettings['command'] ?? ''
		];

		foreach($dataToSet as $key => $val){
			if($val === ''){
				unset($dataToSet[$key]);
			}
		}

		$recordLookup = $this->airtableClient->table($tableName)
			->select('*')
			->where(['Name' => $key])
			->limit(1)
			->execute();

		$recordExists = $recordLookup->count();

		if($recordExists){
			$record = $recordLookup->fetch();
			$record->setFields($dataToSet);
			$this->airtableClient->table($tableName)->update($record)->typecast()->execute();

			return true;
		}

		$insertRecord = $this->airtableClient->table($tableName)->insert([
			$dataToSet
		])->execute();

		if(!$insertRecord->fetch()->getId()){
			return false;
		}

		return true;
	}
}
