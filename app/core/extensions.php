<?php

/**
* Checks if the required PHP extensions are loaded.
* If any of the required extensions are not loaded, it will output a message
* listing those extensions that need to be enabled in the php.ini file.
*/
function checkExtensions()
{
	/* List of required PHP extensions */
	$extensions =
	[
		'gd',			/* Extension for image processing */
		'pdo_mysql'	/* Extension for MySQL databse access using PDO */
	];

	$not_loaded = [];	/* Array to hold extensions that are not loaded */

	/* Check each extension to see if it is loaded */
	foreach ($extensions as $ext) {
		if (!extension_loaded($ext))	/* If the extension is not loaded */
			$not_loaded[] = $ext;	/* Add it to the not_loaded array */
	}

	/* If there are any extensions that are not loaded */
	if (!empty($not_loaded))
		/* Output a message listing the missing extensions */
		dd("Please load the following extensions in your php.ini file: " . implode(",", $not_loaded));

}

/* Call the function to check extensions */
checkExtensions();