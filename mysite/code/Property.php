<?php

class Property extends DataObject {

	private static $db = array (
		'Title' => 'Varchar',
		'PricePerNight' => 'Currency',
		'Bedrooms' => 'Int',
		'Bathrooms' => 'Int',
		'FeaturedOnHomepage' => 'Boolean',
		'AvailableStart' => 'Date',
		'AvailableEnd' => 'Date',
		'Description' => 'Text'
	);

	private static $has_one = array (
		'Region' => 'Region',
		'PrimaryPhoto' => 'Image'
	);

	private static $summary_fields = array (
		'Title' => 'Title',
		'Region.Title' => 'Region',
		'PricePerNight.Nice' => 'Price',
		'FeaturedOnHomepage.Nice' => 'Featured?'
	);

	public function searchableFields() {
		return array (
			'Title' => array (
				'filter' => 'PartialMatchFilter',
				'title' => 'Title',
				'field' => 'TextField'
			),
			'RegionID' => array (
				'filter' => 'ExactMatchFilter',
				'title' => 'Region',
				'field' => DropdownField::create('RegionID')
							->setSource(
								Region::get()->map('ID','Title')
							)
							->setEmptyString('-- Any region --')
			),
			'FeaturedOnHomepage' => array (
				'filter' => 'ExactMatchFilter',
				'title' => 'Only featured'
			)
		);
	}


	public function getCMSFields() {
		$fields = FieldList::create(TabSet::create('Root'));
		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('Title'),
			TextareaField::create('Description'),
			CurrencyField::create('PricePerNight', 'Price (per night)'),
			DropdownField::create('Bedrooms')
				->setSource(ArrayLib::valuekey(range(1,10))),
			DropdownField::create('Bathrooms')
				->setSource(ArrayLib::valuekey(range(1,10))),
			DropdownField::create('RegionID','Region')
				->setSource(Region::get()->map('ID','Title'))
				->setEmptyString('-- Select a region --'),
			CheckboxField::create('FeaturedOnHomepage','Feature on homepage')			
		));
		$fields->addFieldToTab('Root.Photos', $upload = UploadField::create(
			'PrimaryPhoto',
			'Primary photo'
		));

		$upload->getValidator()->setAllowedExtensions(array(
			'png','jpeg','jpg','gif'
		));
		$upload->setFolderName('property-photos');

		return $fields;
	}

}