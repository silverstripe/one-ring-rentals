<?php

use SilverStripe\i18n\i18n;
use SilverStripe\Control\Director;

global $project;
$project = 'mysite';

global $database;
$database = '';

require_once('conf/ConfigureFromEnv.php');

// Set the site locale
i18n::set_locale('en_US');
