<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

defined('WEB_SERVER_SCHEME')    or define('WEB_SERVER_SCHEME','');
defined('WEB_SERVER_HOST')      or define('WEB_SERVER_HOST','');
defined('WEB_SERVER_PORT')      or define('WEB_SERVER_PORT','');
defined('WEB_SERVER_DOCROOT')   or define('WEB_SERVER_DOCROOT','');


return $loader;


//
//require_once __DIR__ . '/../vendor/autoload.php';
//
//// Command that starts the built-in web server
//$command = sprintf(
//    'php -d always_populate_raw_post_data=-1  -d short_open_tag=1 -S %s:%d -t %s > php.log 2>&1 & echo $!',
//    WEB_SERVER_HOST,
//    WEB_SERVER_PORT,
//    WEB_SERVER_DOCROOT
//);
// 
//// Execute the command and store the process ID
//$output = array(); 
//exec($command, $output);
//$pid = (int) $output[0];
// 
//echo sprintf(
//    '%s - Web server started on %s:%d with PID %d', 
//    date('r'),
//    WEB_SERVER_HOST, 
//    WEB_SERVER_PORT, 
//    $pid
//) . PHP_EOL;
//echo sprintf(
//    '%s', 
//    $command
//) . PHP_EOL;
// 
//// Kill the web server when the process ends
//register_shutdown_function(function() use ($pid) {
//    echo sprintf('%s - Killing process with ID %d', date('r'), $pid) . PHP_EOL;
//    exec('kill ' . $pid);
//});

