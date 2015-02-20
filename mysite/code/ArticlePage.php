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


	private static $many_many = array (
		'Categories' => 'ArticleCategory'
	);


	private static $has_many = array (
		'Comments' => 'ArticleComment'
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

		$fields->addFieldToTab('Root.Categories', CheckboxSetField::create(
			'Categories',
			'Selected categories',
			$this->Parent()->Categories()->map('ID', 'Title')
		));

		return $fields;
	}


	public function CategoriesList() {
		if($this->Categories()->exists()) {
			return implode(', ', $this->Categories()->column('Title'));
		}
	}
	
}

class ArticlePage_Controller extends Page_Controller {


	private static $allowed_actions = array (
		'CommentForm',
	);


	public function CommentForm() {
		$form = Form::create(
			$this,
			__FUNCTION__,
			FieldList::create(
				TextField::create('Name',''),
				EmailField::create('Email',''),
				TextareaField::create('Comment','')
			),
			FieldList::create(
				FormAction::create('handleComment','Post Comment')
					->setUseButtonTag(true)
					->addExtraClass('btn btn-default-color btn-lg')
			),
			RequiredFields::create('Name','Email','Comment')
		)->addExtraClass('form-style');

		foreach($form->Fields() as $field) {
			$field->addExtraClass('form-control')
				  ->setAttribute('placeholder', $field->getName().'*');
		}

		$data = Session::get("FormData.{$form->getName()}.data");
		
		return $data ? $form->loadDataFrom($data) : $form;
	}


	public function handleComment($data, $form) {
		Session::set("FormData.{$form->getName()}.data", $data);
		$existing = $this->Comments()->filter(array(
			'Comment' => $data['Comment']
		));		
		if($existing->exists() && strlen($data['Comment']) > 20) {
			$form->sessionMessage('That comment already exists! Spammer!','bad');

			return $this->redirectBack();
		}
		$comment = ArticleComment::create();
		$comment->ArticlePageID = $this->ID;
		$form->saveInto($comment);
		$comment->write();

		Session::clear("FormData.{$form->getName()}.data");
		$form->sessionMessage('Thanks for your comment','good');

		return $this->redirectBack();
	}







}