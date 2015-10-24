<?php

global $project;
$project = 'mysite';

global $database;
$database = '';

require_once('conf/ConfigureFromEnv.php');

// Set the site locale
i18n::set_locale('en_US');

if(Director::isTest()) {
    SS_Log::add_writer(new SS_LogFileWriter('../silverstripe-errors-warnings.log'), SS_Log::WARN, '<=');

    SS_Log::add_writer(new SS_LogFileWriter('../silverstripe-errors.log'), SS_Log::ERR);
}

if(Director::isLive()) {
    SS_Log::add_writer(new SS_LogEmailWriter('me@example.com'), SS_Log::ERR);
}

