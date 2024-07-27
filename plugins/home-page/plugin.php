<?php

/**
 * Plugin name: Home Page
 * Description: Display the home page of a website
 * 
 * 
 **/

addAction('view',function(){

	require pluginPath('/views/view.php');
});