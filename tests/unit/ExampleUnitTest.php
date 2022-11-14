<?php
/**
 * Telemetron plugin for Craft CMS 4.x
 *
 * Send your Craft "telemetry" like versions, installed plugins, and more to Airtable.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetrontests\unit;

use Codeception\Test\Unit;
use UnitTester;
use Craft;
use simplygoodwork\telemetron\Telemetron;

/**
 * ExampleUnitTest
 *
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     1.0.0
 */
class ExampleUnitTest extends Unit
{
    // Properties
    // =========================================================================

    /**
     * @var UnitTester
     */
    protected $tester;

    // Public methods
    // =========================================================================

    // Tests
    // =========================================================================

    /**
     *
     */
    public function testPluginInstance(): void
    {
        $this->assertInstanceOf(
            Telemetron::class,
            Telemetron::$plugin
        );
    }

    /**
     *
     */
    public function testCraftEdition(): void
    {
        Craft::$app->setEdition(Craft::Pro);

        $this->assertSame(
            Craft::Pro,
            Craft::$app->getEdition()
        );
    }
}
