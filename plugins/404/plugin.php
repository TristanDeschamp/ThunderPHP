<?php

/**
 * Plugin name: 404
 * Description: The Plugin for the 404 page
 * 
 * 
 **/

/** Display the view file **/
addAction('view', function(){

	$results = doFilter(pluginId(). '_search_for_item', []);

	require pluginPath('/views/view.php');
});