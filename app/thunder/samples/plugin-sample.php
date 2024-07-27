<?php

/**
 * Plugin name: 
 * Description: 
 * 
 * 
 **/

setValue([

	'plugin_route'	=>'my-plugin',
	'table'			=>'my_table',

]);

/** set user permissions for this plugin **/
addFilter('permissions',function($permissions){

	$permissions[] = 'my_permission';

	return $permissions;
});


/** run this after a form submit **/
addAction('controller',function(){

	$vars = getValue();

	require pluginPath('/controllers/controller.php');
});


/** displays the view file **/
addAction('view',function(){

	$vars = getValue();

	require pluginPath('/views/view.php');
});


/** for manipulating data after a query operation **/
addFilter('after_query',function($data){

	
	if(empty($data['result']))
		return $data;

	foreach ($data['result'] as $key => $row) {
		


	}

	return $data;
});