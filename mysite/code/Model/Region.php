<?php

use SilverStripe\Assets\Image;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;

class Region extends DataObject
{

    private static $db = array(
        'Title' => 'Varchar',
        'Description' => 'HTMLText',
    );


    private static $has_one = array(
        'Photo' => Image::class,
        'RegionsPage' => 'RegionsPage'
    );

    private static $has_many = array(
        'Articles' => 'ArticlePage'
    );

    private static $summary_fields = array(
        'GridThumbnail' => '',
        'Title' => 'Title of region',
        'Description.Plain' => 'Short description',
    );

    public function getGridThumbnail()
    {
        if ($this->Photo()->exists()) {
            return $this->Photo()->ScaleWidth(100);
        }

        return '(no image)';
    }

    public function getCMSFields()
    {
        $fields = FieldList::create(
            TextField::create('Title'),
            HtmlEditorField::create('Description'),
            $uploader = UploadField::create('Photo')
        );

        $uploader->setFolderName('region-photos');
        $uploader->getValidator()->setAllowedExtensions(array(
            'png',
            'gif',
            'jpeg',
            'jpg'
        ));

        return $fields;
    }


    public function Link()
    {
        return $this->RegionsPage()->Link('show/' . $this->ID);
    }


    public function LinkingMode()
    {
        return Controller::curr()->getRequest()->param('ID') == $this->ID ? 'current' : 'link';
    }

    public function ArticlesLink()
    {
        $page = ArticleHolder::get()->first();

        if ($page) {
            return $page->Link('region/' . $this->ID);
        }
    }


}