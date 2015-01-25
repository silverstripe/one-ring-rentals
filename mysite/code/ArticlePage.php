<?php

class ArticlePage extends Page {


	private static $db = array (
		'Date' => 'Date',
		'Teaser' => 'Text',
		'Author' => 'Varchar',
	);


	private static $has_one = array (
		'Photo' => 'Image',
		'Brochure' => 'File'
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
		$fields->addFieldToTab('Root.Attachments', $photo = UploadField::create('Photo'));
		$fields->addFieldToTab('Root.Attachments', $brochure = UploadField::create('Brochure','Travel brochure, optional (PDF only)'));

		$photo->getValidator()->setAllowedExtensions(array('png','gif','jpg','jpeg'));
		$photo->setFolderName('travel-photos');

		$brochure->getValidator()->setAllowedExtensions(array('pdf'));
		$brochure->setFolderName('travel-brochures');


		return $fields;
	}
	
}

class ArticlePage_Controller extends Page_Controller {

}