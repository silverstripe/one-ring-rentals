<?php

use SilverStripe\Control\Email\Mailer;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\TestMailer;
use SilverStripe\Forms\Form;

/**
 *
 */
class ArticlePageTest extends FunctionalTest
{

    protected $usesDatabase = true;

    /**
     * @var ArticlePage
     */
    protected $article = null;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        /**
         * Ensure we're using the draft site, so we don't have to publish all the pages.
         */
        $this->useDraftSite();

        $holder = ArticleHolder::create([
            'Title' => 'Travel Guides',
            'URLSegment' => 'travel-guides'
        ]);
        $holder->write();

        $page = ArticlePage::create([
            'Date' => date("Y-m-d"),
            'Teaser' => "My Article Teaser",
            'Author' => "Simon Gow",
            'Title' => 'My Special Test Article!',
            'URLSegment' => 'my-special-test-article',
            'ParentID' => $holder->ID,
        ]);
        $page->write();

        $holder->Children()->add($page);

        // store the reference for use in our tests
        $this->article = $page;

        // This is actually done on setUp by SapphireTest, but it shows how you can use injection at run time.
        Injector::inst()->registerService(new TestMailer(), Mailer::class);
    }

    /**
     *
     */
    public function testArticlePage()
    {
        $page = $this->get($this->article->Link());
        $this->assertEquals(200, $page->getStatusCode());
    }

    public function testArticleComment()
    {
        $this->get($this->article->Link());

        // Submitting the form once, posts the comment, and doesn't email anyone.
        $this->submitForm('Form_CommentForm',null, [
            'Name' => 'Gandalf',
            'Email' => 'gandalf@theshire.com',
            'Comment' => "Hi, this special article interests me a great deal, we should have some fireworks and a rather
            large party soon!, \n\n Regards, Gandalf."
        ]);

        // Make sure we didn't send any emails
        $this->assertNull($this->findEmail("gandalf@theshire.com"));

        // Make sure we submitted a comment, and it shows.
        $comments = $this->article->Comments();
        $this->assertCount(1, $comments);

        // Submitting the form again, posts the comment, and email's gandalf, that someone has replied.
        $this->submitForm('Form_CommentForm',null, [
            'Name' => 'Gandalf',
            'Email' => 'gandalf@theshire.com',
            'Comment' => "Hi, this special article interests me a great deal, we should have some fireworks and a rather
            large party soon!, \n\n Regards, Gandalf."
        ]);
    }
}