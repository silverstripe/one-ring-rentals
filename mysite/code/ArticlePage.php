<?php

class ArticlePage extends Page {


	private static $db = array (
		'Date' => 'Date',
		'Teaser' => 'Text',
		'Author' => 'Varchar',
	);


	private static $can_be_root = false;


	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', DateField::create('Date','Date of article')
				->setConfig('showcalendar', true)
				->setConfig('dateformat', 'd MMMM yyyy')				
			,'Content');
		$fields->addFieldToTab('Root.Main', TextareaField::create('Teaser'),'Content');
		$fields->addFieldToTab('Root.Main', TextField::create('Author','Author of article'),'Content');

		return $fields;
	}
	
}

class ArticlePage_Controller extends Page_Controller {

}