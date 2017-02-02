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
// Configure database from dokku env
//
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
$wgDBprefix = '';

# MySQL table options to use during installation or update
$wgDBTableOptions = 'ENGINE=InnoDB, DEFAULT CHARSET=binary';

# Experimental charset support for MySQL 5.0.
$wgDBmysql5 = false;

//////////////////////////////////
// Configure redis caching from dokku env
//
// @see https://www.mediawiki.org/wiki/Redis

$REDIS_URL = parse_url(getenv('REDIS_URL'));
if (!empty($REDIS_URL['host'])) {

    if (!isset($wgObjectCaches)) {
        $wgObjectCaches = [];
    }

    /**
     * @see mediawiki-1.28.0/includes/libs/objectcache/RedisBagOStuff.php
     *
     * Construct a RedisBagOStuff object. Parameters are:
     *
     *   - servers: An array of server names. A server name may be a hostname,
     *     a hostname/port combination or the absolute path of a UNIX socket.
     *     If a hostname is specified but no port, the standard port number
     *     6379 will be used. Arrays keys can be used to specify the tag to
     *     hash on in place of the host/port. Required.
     *
     *   - connectTimeout: The timeout for new connections, in seconds. Optional,
     *     default is 1 second.
     *
     *   - persistent: Set this to true to allow connections to persist across
     *     multiple web requests. False by default.
     *
     *   - password: The authentication password, will be sent to Redis in
     *     clear text. Optional, if it is unspecified, no AUTH command will be
     *     sent.
     *
     *   - automaticFailover: If this is false, then each key will be mapped to
     *     a single server, and if that server is down, any requests for that key
     *     will fail. If this is true, a connection failure will cause the client
     *     to immediately try the next server in the list (as determined by a
     *     consistent hashing algorithm). True by default. This has the
     *     potential to create consistency issues if a server is slow enough to
     *     flap, for example if it is in swap death.
     */
    $wgObjectCaches['redis'] = [
        'class'                => 'RedisBagOStuff',
        'servers'              => [ $REDIS_URL['host'] . ':' . ($REDIS_URL['port'] ?: 6379) ],
        // 'connectTimeout'    => 1,
        // 'persistent'        => false,
        'password'          => $REDIS_URL['pass'] ?: '',
        // 'automaticFailOver' => true,
    ];

    // You'll now be able to acquire a Redis object cache object via wfGetCache( 'redis' ).
    // If you'd like to use Redis as the default cache for various data,
    // you may set any of the following configuration options
    $wgMainCacheType = 'redis';
    $wgSessionCacheType = 'redis';  // same as WMF prod

    // Not widely tested:
    $wgMessageCacheType = 'redis';
    $wgParserCacheType = 'redis';
    $wgLanguageConverterCacheType = 'redis';

    /**
     * Job queue
     *
     * Note: if not daemonized;
     *
     * InvalidArgumentException from line 98 of /var/www/html/includes/jobqueue/JobQueueRedis.php: .
     * Non-daemonized mode is no longer supported. Please install the mediawiki/services/jobrunner service
     * and update $wgJobTypeConf as needed.
     *
     * @see mediawiki-1.28.0/includes/jobqueue/JobQueueRedis.php
     * @see mediawiki-1.28.0/includes/libs/redis/RedisConnectionPool.php
     */
    if (getenv('MEDIAWIKI_ENABLE_REDIS_JOBQUEUE')) {
        $wgJobTypeConf['default'] = array(
            'class'          => 'JobQueueRedis',
            'redisServer'    => $wgObjectCaches['redis']['servers'][0],
            'redisConfig'    => [
                // 'connectTimeout'    => 1,
                // 'readTimeout'       => 1,
                // 'persistent'       => false,
                'password' => $wgObjectCaches['redis']['password'],
                // 'serializer'
            ],
            'daemonized'     => true,
            'claimTTL'       => 3600
        );
    }

}

//////////////////////////////////
// Configure smtp mailing
//
// @see https://www.mediawiki.org/wiki/Mail

$SMTP_URL = parse_url(trim(getenv('SMTP_URL')));
if (!empty($SMTP_URL['host'])) {

    $params = [];
    if (!empty($SMTP_URL['query'])) {
        parse_str($SMTP_URL['query'], $params);
    }

    $wgSMTP = array(
        // could also be an IP address. Where the SMTP server is located
        'host' => (isset($params['ssl']) && $params['ssl'] === '1' ? 'ssl://' : '') . ($SMTP_URL['host'] ?: 'localhost'),
        // Generally this will be the domain name of your website (aka mywiki.org)
        'IDHost' => $params['IDHost'] ?: 'localhost', // TODO try to retrieve from user name...
        // Port to use when connecting to the SMTP server
        'port' => $SMTP_URL['port'] ?: 25,
        // Should we use SMTP authentication (true or false)
        'auth' => isset($params['auth']) && $params['auth'] === '1',
        // Username to use for SMTP authentication (if being used)
        'username' => $SMTP_URL['user'] ?: '',
        // Password to use for SMTP authentication (if being used)
        'password' => $SMTP_URL['pass'] ?: ''
    );

}

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
$wgServer = '//' . ($_SERVER['HTTP_HOST'] ?: 'localhost');

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

$wgSitename = 'DokkuMediaWiki';

// include app custom settings which can override this and inherited LocalSettings.php
/** @noinspection UnnecessaryParenthesesInspection */
/** @noinspection UntrustedInclusionInspection */
/** @noinspection PhpIncludeInspection */
@include('/app/LocalSettings.php');
