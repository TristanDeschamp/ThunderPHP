<?php

namespace Core;

/**
* App Class
*/
class App
{
	/* The main entry point for the application */
	public function index()
	{

		/* Execute actions before the controller logic */
		doAction('before_controller');
		/* Execute the main controller logic */
		doAction('controller');
		/* Execute actions after the controller logic */
		doAction('after_controller');

		/* Start output buffering to capture view output */
		ob_start();
		/* Execute actions before rendering the view */
		doAction('before_view');

		/* Capture the output before rendering the view */
		$before_content = ob_get_contents();
		/* Execute the main view rendering logic */
		doAction('view');
		/* Capture the output after rendering the view */
		$after_content = ob_get_contents();

		/* Cjeck if the view content had changed */
		if (strlen($after_content) == strlen($before_content))
		{
			/* If not, and the current page is not 404, redirect to the 404 page */
			if (page() != '404'){
				redirect('404');
			}
		}

		/* Execute actions after rendering the view */
		doAction('after_view');

	}
}