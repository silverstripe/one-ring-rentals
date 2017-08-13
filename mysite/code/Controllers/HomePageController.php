<?php

class HomePageController extends PageController
{
    /**
     * Our design presently has space for 6 articles, or in columns of 3.
     */
    const NUM_FEATURED_PROPERTIES = 6;

    /**
     * The maximum number of latest articles
     */
    const NUM_LATEST_ARTICLES = 3;

    /**
     * @param int $count
     *
     * @return \SilverStripe\ORM\DataList
     */
    public function LatestArticles($count = self::NUM_LATEST_ARTICLES)
    {
        return ArticlePage::get()
            ->sort('Created', 'DESC')
            ->limit($count);
    }

    /**
     * Return the featured properties for the homepage.
     *
     * @return \SilverStripe\ORM\DataList
     */
    public function FeaturedProperties()
    {
        return Property::get()
            ->filter(array(
                'FeaturedOnHomepage' => true
            ))
            ->limit(self::NUM_FEATURED_PROPERTIES);
    }
}