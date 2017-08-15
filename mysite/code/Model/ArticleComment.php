<?php

use SilverStripe\ORM\DataObject;

/**
 * Class ArticleComment
 * @property String Name
 * @property String Email
 * @property String Comment
 */
class ArticleComment extends DataObject
{
    private static $db = array(
        'Name' => 'Varchar',
        'Email' => 'Varchar',
        'Comment' => 'Text'
    );

    private static $has_one = array(
        'ArticlePage' => 'ArticlePage'
    );
}