<?php

class ArticleComment extends DataObject {

	private static $db = array (
		'Name' => 'Varchar',
		'Email' => 'Varchar',
		'Comment' => 'Text'
	);


	private static $has_one = array (
		'ArticlePage' => 'ArticlePage'
	);
}