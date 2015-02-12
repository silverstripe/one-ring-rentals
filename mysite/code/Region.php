<?php

class Region extends DataObject {

	private static $db = array (
		'Title' => 'Varchar',
		'Description' => 'Text',
	);


	private static $has_one = array (
		'Photo' => 'Image',
		'RegionsPage' => 'RegionsPage'
	);

	private static $summary_fields = array (
		'GridThumbnail' => '',
		'Title' => 'Title of region',
		'Description' => 'Short description',
	);


	public function getGridThumbnail() {
		if($this->Photo()->exists()) {
			return $this->Photo()->SetWidth(100);
		}

		return '(no image)';
	}

	public function getCMSFields() {
		$fields = FieldList::create(
			TextField::create('Title'),
			TextareaField::create('Description'),
			$uploader = UploadField::create('Photo')
		);

		$uploader->setFolderName('region-photos');
		$uploader->getValidator()->setAllowedExtensions(array(
			'png','gif','jpeg','jpg'
		));

		return $fields;
	}
}