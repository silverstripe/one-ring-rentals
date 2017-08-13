<?php

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Control\HTTPRequest;

/**
 * Tests link tracking to files and images.
 */
class PropertySearchPageTest extends SapphireTest
{
    /**
     * Use a test database to avoid overwriting our local
     *
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * Our fixture file containing the object specifications used in our unit or other tests
     *
     * @var string
     */
    protected static $fixture_file = 'mysite/tests/fixtures/searchtest-properties.yml';

    /**
     * Builds a mock HTTP request to check the filter
     *
     * @param $map
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockHTTPRequest($map)
    {
        $stub = $this->createMock('\SilverStripe\Control\HTTPRequest');
        $stub->method('getVar')
            ->will($this->returnValueMap($map));

        return $stub;
    }

    /**
     * Helper function to return the result from
     * - take a map of intended filters to apply to the filter query,
     * - create a controller, and
     * - call the filterQuery method
     *
     * @param $map
     * @return mixed
     */
    protected function filterPropertiesThroughController($map)
    {
        // Mock an HTTP request.
        $stub = $this->createMockHTTPRequest($map);

        $searchController = new PropertySearchPageController();
        $paginatedProperties = self::callProtectedMethod($searchController, "filterQuery", [$stub]);
        return $paginatedProperties;
    }

    /**
     * Helper function using reflection to call protected methods.
     *
     * @param $object
     * @param $method
     * @param array $args
     *
     * @return mixed
     */
    public static function callProtectedMethod($object, $method, array $args = array())
    {
        $class = new ReflectionClass(get_class($object));
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

    /**
     * Ensure our fixtures are setup correctly
     */
    public function testPropertySearch()
    {
        $properties = Property::get();

        // We should only have 10 properties in the fixtures file.
        $this->assertCount(10, $properties);

        $regions = Region::get();
        $this->assertCount(9, $regions);
    }

    /**
     * Applying a keyword to the request filters the result set.
     */
    public function testKeywordFilter()
    {
        /**
         * Get a keyword from the first property in our fixtures. Only use a unique keyword for that property.
         *
         * @var Property $property
         */
        $property = $this->objFromFixture('Property', 'property1');
        $keywords = preg_split("/ /", $property->Title);

        $map = [
            [PropertySearchPageController::GET_FILTER_KEYWORDS, $keywords[count($keywords) - 1]],
        ];

        // Mock an HTTP request
        $paginatedProperties = $this->filterPropertiesThroughController($map);

        $this->assertCount(1, $paginatedProperties);
    }

    /**
     * Applying a keyword to the request filters the result set.
     */
    public function testKeywordNoMatchFilter()
    {
        $map = [
            [PropertySearchPageController::GET_FILTER_KEYWORDS, "This shouldn't match any properties"],
        ];

        // Mock an HTTP request.
        $paginatedProperties = $this->filterPropertiesThroughController($map);
        $this->assertCount(0, $paginatedProperties);
    }

    /**
     * Applying a arrival date, and number of nights filters out properties that don't match
     */
    public function testArrivalDateFilter()
    {
        /**
         * Get a property to match nights against.
         *
         * @var Property $property
         */
        $property = $this->objFromFixture('Property', 'property1');

        $map = [
            [PropertySearchPageController::GET_FILTER_ARRIVAL_DATE, $property->AvailableStart],
            [PropertySearchPageController::GET_FILTER_NIGHTS, 1],
        ];

        // Mock an HTTP request.
        $paginatedProperties = $this->filterPropertiesThroughController($map);

        $this->assertCount(1, $paginatedProperties);
        $this->assertDOSContains([['Title' => $property->Title]], $paginatedProperties->getList());
    }

    /**
     * Test that if the start date filter, includes the number of nights and correctly filters out properties that
     * aren't available for that length of time.
     */
    public function testArrivalDateNightsOverEndFilter()
    {
        /**
         * Get a start date from a property in our fixtures. #7 is only available for one day!
         * @var Property $property
         */
        $property = $this->objFromFixture('Property', 'property7');

        $map = [
            [PropertySearchPageController::GET_FILTER_ARRIVAL_DATE, $property->AvailableStart],
            [PropertySearchPageController::GET_FILTER_NIGHTS, 3], // over the 1 available night.
        ];
        $paginatedProperties = $this->filterPropertiesThroughController($map);
        $this->assertCount(0, $paginatedProperties);
    }

    /**
     * Test that if the start date filter spans multiple available start and end dates.
     * Based on the assumption that our fixtures file has 7 properties available until the end of 2017-08-31
     */
    public function testArrivalDateNightsSpanFilter()
    {
        /**
         * Get the start date from our 7th property, all properties should span this availability.
         * @var Property $property
         */
        $property = $this->objFromFixture('Property', 'property7');

        $map = [
            [PropertySearchPageController::GET_FILTER_ARRIVAL_DATE, $property->AvailableStart],
            [PropertySearchPageController::GET_FILTER_NIGHTS, 1],
        ];
        $paginatedProperties = $this->filterPropertiesThroughController($map);
        $this->assertCount(7, $paginatedProperties);
    }

    /**
     *
     */
    public function testBedroomsFilter()
    {
        /**
         * Get the start date from our 7th property, all properties should span this availability.
         * @var Property $property
         */
        $property = $this->objFromFixture('Property', 'property5');

        $map = [
            [PropertySearchPageController::GET_FILTER_BEDROOMS, $property->Bedrooms],
        ];

        $paginatedProperties = $this->filterPropertiesThroughController($map);
        $this->assertCount(6, $paginatedProperties);
    }

    /**

     */
    public function testPriceFilter()
    {
        /**
         * @var Property $property
         */
        $property = $this->objFromFixture('Property', 'property5');

        $map = [
            [PropertySearchPageController::GET_FILTER_MAX_PRICE, $property->PricePerNight],
        ];

        $paginatedProperties = $this->filterPropertiesThroughController($map);
        $this->assertCount(5, $paginatedProperties);
    }

    /**
     */
    public function testBathroomsFilter()
    {
        /**
         * @var Property $property
         */
        $property = $this->objFromFixture('Property', 'property6');

        $map = [
            [PropertySearchPageController::GET_FILTER_BEDROOMS, $property->Bedrooms],
        ];

        $paginatedProperties = $this->filterPropertiesThroughController($map);
        $this->assertCount(5, $paginatedProperties);
    }
}
