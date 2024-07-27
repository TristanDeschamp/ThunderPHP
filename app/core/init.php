<?php

/* Register an autoload function to automatically include class files */
spl_autoload_register(function($classname){

	/* Split the namespace and class name */
	$parts = explode("\\", $classname);
	$classname = array_pop($parts);	/* Get the class name */

	/* Construct the path to the class file in the app/models directory */
	$path = 'app'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR. ucfirst($classname) . '.php';
	if (file_exists($path))
	{
		/* If the file exists, include it */
		require_once $path;
	}else{

		/* If not found, get the calling file path using debug_backtrace */
		$called_from = debug_backtrace();
		$key = array_search(__FUNCTION__, array_column($called_from, 'function'));

		/* Construct the path to the class file in the plugins directory */
		$path = getPluginDir(debug_backtrace()[$key]['file']) . 'models'. DIRECTORY_SEPARATOR . ucfirst($classname . '.php');
		if (file_exists($path))
		{
			/* If the file exists in the pkugins directory, include it */
			require_once $path;
		}
	}
});

/* Include essential files required for the application */

/* Functions file: contains general helper functions */
require 'functions.php';
/* Extensions file: contains function to check required PHP extensions */
require 'extensions.php';
/* Database class file: handles database connections and queries */
require 'Database.php';
/* Model class file: extends Database for more specific database operations */
require 'Model.php';
/* App class file: contains the main application class */
require 'App.php';