<?php

use SilverStripe\Dev\FunctionalTest;

class PropertySearchTest extends FunctionalTest
{
    protected static $fixture_file = 'mysite/tests/fixtures/searchtest-properties.yml';

    /**
     * Ensure our PropertySearchPage is 'live'
     */
    public function setUp()
    {
        parent::setUp();

        /**
         * Ensure we're using the draft site, so we don't have to publish all the pages.
         */
        $this->useDraftSite();
    }

    /**
     * Make sure we can visit the property search page.
     */
    public function testPropertySearchView()
    {
        $page = $this->get('find-a-rental');
        $this->assertEquals(200, $page->getStatusCode());
    }

    /**
     * Make sure we can submit the search form on each page, and it filters the results correctly.
     */
    public function testPropertySearchForm()
    {
        // Request the page, and submit the form
        $page = $this->get('find-a-rental');
        $this->assertEquals(200, $page->getStatusCode());

        // search for a property in our fixtures
        $property = $this->objFromFixture('Property', 'property9');
        $keywords = preg_split("/ /", $property->Title);

        $this->submitForm( "Form_PropertySearchForm", null, [
            // the last keyword in the title is unique for that property.
            PropertySearchPageController::GET_FILTER_KEYWORDS => $keywords[count($keywords) - 1]
        ]);

        $properties = $this->cssParser()->getBySelector(".item");
        $this->assertCount(1, $properties);

        // check property matches fixture
    }

    /**
     * Make sure we have the latest news feature present and returning items on the page.
     */
    public function testPropertySearchLatestNews()
    {
        // Request the page, and submit the form
        $page = $this->get('find-a-rental');
        $this->assertEquals(200, $page->getStatusCode());

        $latestNewsItems = $this->cssParser()->getBySelector(".latest-news li.col-md-12");
        $this->assertCount(3, $latestNewsItems);
    }
}