<?php
# See includes/DefaultSettings.php for all configurable settings
# and their default values, but don't forget to make changes in _this_
# file, not there.
#
# Further documentation for configuration settings may be found at:
# https://www.mediawiki.org/wiki/Manual:Configuration_settings
if (getenv('PHP_ENV') !== 'production') {
    error_reporting(E_ALL & ~E_STRICT);
    ini_set('display_errors', 1);

    $wgDebugToolbar = true;

    $wgShowDebug = false;
    $wgDebugPrintHttpHeaders  = true;
    $wgDebugTimestamps  = true;
    $wgDevelopmentWarnings = true;
    $wgShowSQLErrors = true;
    $wgShowDBErrorBacktrace = true;
    $wgShowExceptionDetails = true;
}

//////////////////////////////////
// configure database from dokku env
$DATABASE_URL = parse_url(getenv('DATABASE_URL'));
if (empty($DATABASE_URL)) {
    die('"DATABASE_URL" is not set.. have you linked the database?');
}

## Database settings
$wgDBtype = $DATABASE_URL['scheme'];
$wgDBserver = $DATABASE_URL['host'];
$wgDBname = trim($DATABASE_URL['path'], '/');
$wgDBuser = substr($DATABASE_URL['user'], 0, 16);
$wgDBpassword = $DATABASE_URL['pass'];

# MySQL specific settings
$wgDBprefix = "";

# MySQL table options to use during installation or update
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

# Experimental charset support for MySQL 5.0.
$wgDBmysql5 = false;

//////////////////////////////////
// configure memcached from dokku env
## Shared memory settings
/*
$wgMainCacheType = CACHE_MEMCACHED;
$wgMemCachedServers = array( '127.0.0.1:11211' );
*/

## The URL base path to the directory containing the wiki;
## defaults for all runtime URL paths are based off of this.
## For more information on customizing the URLs
## (like /w/index.php/Page_title to /wiki/Page_title) please see:
## https://www.mediawiki.org/wiki/Manual:Short_URL
$wgScriptPath = '';
#$wgScriptPath = "/w";        // this should already have been configured this way
#$wgArticlePath = "/wiki/$1";

## The protocol and server name to use in fully-qualified URLs
$wgServer = '//' . $_SERVER['HTTP_HOST'];

## The URL path to static resources (images, scripts, etc.)
$wgResourceBasePath = $wgScriptPath;

## To enable image uploads, make sure the 'images' directory
## is writable, then set this to true:
if (getenv('MEDIAWIKI_ENABLE_UPLOADS')) {
    $wgEnableUploads = true;
}

# The following permissions were set based on your choice in the installer
if (getenv('MEDIAWIKI_DISABLE_ANONYMOUS_EDIT')) {
    $wgGroupPermissions['*']['edit'] = false;
}

$wgSitename = "DokkuMediaWiki";

// include app custom settings which can override this and inherited LocalSettings.php
/** @noinspection UnnecessaryParenthesesInspection */
@include('/app/LocalSettings.php');
