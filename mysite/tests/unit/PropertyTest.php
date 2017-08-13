<?php

use SilverStripe\Dev\SapphireTest;

/**
 * Test our property business logic.
 */
class PropertyTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Check the business rule around a property being available for rental at the current time.
     */
    public function testIsAvailable()
    {
        // Create a property with an available date.
        $property = new Property([
            'AvailableStart' => date("Y-m-d", time()),
            'AvailableEnd' => date("Y-m-d", time())
        ]);
        $this->assertTrue($property->isAvailable());

        /**if (self::$mock_now) {
            $now = self::$mock_now;
        } else {**/
    }

    public function testIsAvailableNoStart()
    {
        // Create a property with an available date.
        $property = new Property([
            'AvailableEnd' => date("Y-m-d", time())
        ]);
        $this->assertFalse($property->isAvailable());
    }

    public function testIsAvailableNoEnd()
    {
        // Create a property with an available date.
        $property = new Property([
            'AvailableStart' => date("Y-m-d", time()),
        ]);
        $this->assertFalse($property->isAvailable());
    }

    public function testIsAvailableStartTimeInFuture()
    {
        // Create a property with an available date.
        $property = new Property([
            'AvailableStart' => date("Y-m-d", time() + 86400), // one day in the future
            'AvailableEnd' => date("Y-m-d", time())
        ]);
        $this->assertFalse($property->isAvailable());
    }

    public function testIsAvailableEndTimeInPast()
    {
        // Create a property with an available date.
        $property = new Property([
            'AvailableStart' => date("Y-m-d", time() - 86400 * 2), // two days in the past
            'AvailableEnd' => date("Y-m-d", time() - 86400) // one day in the past
        ]);
        $this->assertFalse($property->isAvailable());
    }
}