<?php

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

/**
 * Tests link tracking to files and images.
 */
class HomePageTest extends SapphireTest
{
    /**
     * Number of test data objects to create
     */
    const NUM_PROPERTIES = 20;
    const NUM_ARTICLES = 5;

    /**
     * Use a test database to avoid overwriting our local
     *
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * Run on each test.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Run once per class instantiation.
     */
    public static function setUpBeforeClass()
    {
        // Always bootstrap the parent.
        parent::setUpBeforeClass();

        // Injector / Config dependency injection could go here.
    }

    /**
     * Ensure the homepage is returning the correct amount of properties for this design.
     *
     * Create 20 properties, even ones (10) should be featured, but only 6 should be returned.
     *
     * reference: HomePageController::FeaturedProperties()
     */
    public function testFeaturedProperties(){
        // Create 20 properties, 10 should be featured.
        for ($i = 0; $i < self::NUM_PROPERTIES; $i++){
            Property::create(['FeaturedOnHomepage' => $i % 2])->write();
        }

        /**
         * @var $controller HomePageController
         * @var $properties Property[]
         */
        $controller = Injector::inst()->create('HomePageController', new HomePage());
        $properties = $controller->FeaturedProperties();
        $this->assertCount(HomePageController::NUM_FEATURED_PROPERTIES, $properties);

        foreach($properties as $property){
            $this->assertTrue((bool) $property->FeaturedOnHomepage);
        }
    }

    /**
     * Ensure the latest articles are coming from the Homepage LatestArticle Function
     */
    public function testLatestArticles(){
        // Create some articles.
        $articles = [];

        $parentID = ArticleHolder::create()->write();
        for ($i = 1; $i < self::NUM_ARTICLES; $i++) {
            $article = ArticlePage::create([
                'Created'=> date("Y-m-{$i}"),
                'ParentID' => $parentID
            ]);

            // store the ID to match.
            $articles[] = $article->write();
        }

        // our expected result set is in reverse order, newest to oldest.
        $articles =  array_reverse($articles);
        $articles = array_slice($articles,0,HomePageController::NUM_LATEST_ARTICLES);

        /**
         * @var $controller HomePageController
         * @var $articles ArticlePage[]
         */
        $controller = Injector::inst()->create('HomePageController', new HomePage());
        $latestArticles = $controller->LatestArticles();

        // Always check against constants in case business rules change.
        $this->assertCount(HomePageController::NUM_LATEST_ARTICLES, $latestArticles);

        // Make sure the results match that of the expected results.
        foreach($latestArticles as $article){
            $this->assertTrue($article instanceof ArticlePage);
            $this->assertTrue(in_array($article->ID, $articles),
                "The Article ID {$article->ID} wasn't in the expected result set");
        }
    }
}