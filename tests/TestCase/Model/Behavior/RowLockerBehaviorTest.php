<?php
namespace RowLocker\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use RowLocker\Model\Behavior\RowLockerBehavior;

/**
 * RowLocker\Model\Behavior\RowLockerBehavior Test Case
 */
class RowLockerBehaviorTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->RowLocker = new RowLockerBehavior();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->RowLocker);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
