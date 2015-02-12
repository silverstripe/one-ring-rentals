<?php

class ArticleHolder extends Page {

	private static $has_many = array (
		'Categories' => 'ArticleCategory'
	);


	private static $allowed_children = array (
		'ArticlePage'
	);


	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Categories', GridField::create(
			'Categories',
			'Article categories',
			$this->Categories(),
			GridFieldConfig_RecordEditor::create()
		));

		return $fields;
	}

}

class ArticleHolder_Controller extends Page_Controller {

}