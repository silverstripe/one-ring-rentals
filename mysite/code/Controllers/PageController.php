<?php

use SilverStripe\View\Requirements;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\View\ThemeResourceLoader;

class PageController extends ContentController
{

    /**
     * An array of actions that can be accessed via a request. Each array element should be an action name, and the
     * permissions or conditions required to allow the user to access it.
     *
     * <code>
     * array (
     *     'action', // anyone can access this action
     *     'action' => true, // same as above
     *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
     *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
     * );
     * </code>
     *
     * @var array
     */
    private static $allowed_actions = array();

    public function doInit()
    {
        parent::doInit();
        Requirements::css("{$this->ThemeDir()}/css/bootstrap.min.css");
        Requirements::css("{$this->ThemeDir()}/css/style.css");

        Requirements::javascript("{$this->ThemeDir()}/js/common/modernizr.js");
        Requirements::javascript("{$this->ThemeDir()}/js/common/jquery-1.11.1.min.js");
        Requirements::javascript("{$this->ThemeDir()}/js/common/bootstrap.min.js");
        Requirements::javascript("{$this->ThemeDir()}/js/common/bootstrap-datepicker.js");
        Requirements::javascript("{$this->ThemeDir()}/js/common/chosen.min.js");
        Requirements::javascript("{$this->ThemeDir()}/js/common/bootstrap-checkbox.js");
        Requirements::javascript("{$this->ThemeDir()}/js/common/nice-scroll.js");
        Requirements::javascript("{$this->ThemeDir()}/js/common/jquery-browser.js");
        Requirements::javascript("{$this->ThemeDir()}/js/scripts.js");

    }

    public function ThemeDir()
    {
        return ThemeResourceLoader::inst()->getPath('one-ring');
    }

}
