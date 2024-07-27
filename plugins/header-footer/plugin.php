<?php

/**
 * Plugin name: Header Footer
 * Description: Display the header and footer of a website
 * 
 * 
 **/

addAction('before_view',function(){

	require pluginPath('/views/header.php');
});

addAction('after_view',function(){

	require pluginPath('/views/footer.php');
});