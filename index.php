<?php

/* Start a new session or resume the existing session */
session_start();

/* Define the minimum PHP version required to run the application */
$minPHPVersion = '8.0';
if (phpversion() < $minPHPVersion)
	/* Stop script execution if the PHP version is lower than the required version */
	die ("You need a minimum of PHP version $minPHPVersion to run this app.");

/* Define constants for directory separation and the root path of the application */
define('DS', DIRECTORY_SEPARATOR);
define('ROOTPATH', __DIR__.DS);

/* Include the configuration and initialization files of the application */
require 'config.php';
require 'app'.DS.'core'.DS.'init.php';

/* Display or hide errors based on the DEBUG constant */
DEBUG ? ini_set('display_errors', 1) : ini_set('display_errors', 0);

/* Initialize global arrays and variables */
$ACTIONS 				= [];
$FILTERS 				= [];
$APP['URL'] 			= splitUrl($_GET['url'] ?? 'home'); /* Determine the requested URL or use 'home' by default */
$APP['permissions'] 	= [];
$USER_DATA 				= [];

/* Load Plugins */
$PLUGINS = getPluginFolders();
if (!loadPlugins($PLUGINS))
	/* Stop script execution if no plugins are found */
	die ("<center><h1 style ='font-family: tahoma'>No plugins were found ! Please put at least one plugin in the plugins folder</h1></center>");

/* Apply filters to get user permissions */
$APP['permissions'] = doFilter('user_permissions', $APP['permissions']);

/* Load the application */
$app = new \Core\App();
$app->index(); /* Call the main method of the application to start processing */