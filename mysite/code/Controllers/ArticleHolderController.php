<?php

use SilverStripe\ORM\PaginatedList;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\FieldType\DBField;

class ArticleHolderController extends PageController
{

    private static $allowed_actions = array(
        'category',
        'region',
        'date'
    );

    protected $articleList;

    public function init()
    {
        parent::init();

        $this->articleList = ArticlePage::get()->filter(array(
            'ParentID' => $this->ID
        ))->sort('Date DESC');
    }

    public function category(HTTPRequest $r)
    {
        $category = ArticleCategory::get()->byID(
            $r->param('ID')
        );

        if (!$category) {
            return $this->httpError(404, 'That category was not found');
        }

        $this->articleList = $this->articleList->filter(array(
            'Categories.ID' => $category->ID
        ));

        return array(
            'SelectedCategory' => $category
        );
    }

    public function region(HTTPRequest $r)
    {
        $region = Region::get()->byID(
            $r->param('ID')
        );

        if (!$region) {
            return $this->httpError(404, 'That region was not found');
        }

        $this->articleList = $this->articleList->filter(array(
            'RegionID' => $region->ID
        ));

        return array(
            'SelectedRegion' => $region
        );
    }

    public function date(HTTPRequest $r)
    {
        $year = $r->param('ID');
        $month = $r->param('OtherID');

        if (!$year) {
            return $this->httpError(404);
        }

        $startDate = $month ? "{$year}-{$month}-01" : "{$year}-01-01";

        if (strtotime($startDate) === false) {
            return $this->httpError(404, 'Invalid date');
        }

        $adder = $month ? '+1 month' : '+1 year';
        $endDate = date('Y-m-d', strtotime(
            $adder,
            strtotime($startDate)
        ));

        $this->articleList = $this->articleList->filter(array(
            'Date:GreaterThanOrEqual' => $startDate,
            'Date:LessThan' => $endDate
        ));

        return array(
            'StartDate' => DBField::create_field('SS_DateTime', $startDate),
            'EndDate' => DBField::create_field('SS_DateTime', $endDate)
        );

    }


    public function PaginatedArticles($num = 10)
    {
        return PaginatedList::create(
            $this->articleList,
            $this->getRequest()
        )->setPageLength($num);
    }


}