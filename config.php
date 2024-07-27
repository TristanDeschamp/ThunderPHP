<?php

/* Enable or disable debugging */
define('DEBUG', true);

/* Define the name and description of the application */
define('APP_NAME', 'ThunderPHP App');
define("APP_DESCRIPTION", 'The best website ever !');

/* Check if the server name is empty (indicating CLI usage) or if the server is running locally */
if ((empty($_SERVER['SERVER_NAME']) && strpos(PHP_SAPI, 'cgi') !== 0) || (!empty($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'localhost'))
{
	/* The name of your database */
	define( 'DB_NAME', 'pluginphp_db' );
	
	/* Database username */
	define( 'DB_USER', 'root' );
	
	/* Database password */
	define( 'DB_PASSWORD', '' );
	
	/* Database hostname */
	define( 'DB_HOST', 'localhost' );
	
	/* Database driver */
	define( 'DB_DRIVER', 'mysql' );

	/* Define the root URL for the local environment */
	define('ROOT', 'http://localhost/thunderPhp');

}else
{
	/* The name of your database */
	define( 'DB_NAME', 'pluginphp_db' );
	
	/* Database username */
	define( 'DB_USER', 'root' );
	
	/* Database password */
	define( 'DB_PASSWORD', '' );
	
	/* Database hostname */
	define( 'DB_HOST', 'localhost' );
	
	/* Database driver */
	define( 'DB_DRIVER', 'mysql' );

	/* Define the root URL for the production environment */
	define('ROOT', 'http://yourwebsite');
}