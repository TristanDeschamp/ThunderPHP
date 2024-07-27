<?php

/**
* Sets a value in the global USER_DATA array for a specific plugin.
* 
* @param string|array $key The key(s) for the value(s) to set.
* @param mixed $value The value to set. If $key is an array, $value is ignored.
* @return bool Returns true if the value(s) were successfully set, otherwise false.
*/
function setValue(string|array $key, mixed $value = ''):bool
{
	global $USER_DATA;

	$called_from = debug_backtrace();
	$ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
	$path = getPluginDir(debug_backtrace()[$ikey]['file']) . 'config.json';

	if (file_exists($path))
	{
		$json = json_decode(file_get_contents($path));
		$plugin_id = $json->id;

		if (is_array($key))
		{
			foreach ($key as $k => $value) {

				$USER_DATA[$plugin_id][$k] = $value;
			}
		}else
		{
			$USER_DATA[$plugin_id][$key] = $value;
		}


		return true;
	}

	return false;
}

/**
* Retrieves the plugin ID from the config.json file of the calling plugin.
* 
* @return string Returns the plugin ID or an empty string if not found.
*/
function pluginId():string
{
	$called_from = debug_backtrace();
	$ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
	$path = getPluginDir(debug_backtrace()[$ikey]['file']) . '/config.json';

	$json = json_decode(file_get_contents($path));
	return $json->id ?? '';
}

/**
* Retrieves a value from the global USER_DATA array for a specific plugin.
* 
* @param string $key The key to retrieve the value for.
* @return mixed Returns the value if found, otherwise null.
*/
function getValue(string $key = ''):mixed
{
	global $USER_DATA;

	$called_from = debug_backtrace();
	$ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
	$path = getPluginDir(debug_backtrace()[$ikey]['file']) . 'config.json';

	if (file_exists($path))
	{
		$json = json_decode(file_get_contents($path));
		$plugin_id = $json->id;

		if (empty($key))
			return $USER_DATA[$plugin_id];

		return !empty($USER_DATA[$plugin_id][$key]) ? $USER_DATA[$plugin_id][$key] : null;
	}

	return null;

}

/**
* Retrieves a value from the global APP array.
* 
* @param string $key The key to retrieve the value for. If empty, returns the entire APP array.
* @return mixed Returns the value if found, otherwise null.
*/
function APP($key = '')
{
	global $APP;

	if (!empty($key))
	{
		return !empty($APP[$key]) ? $APP[$key] : null;
	}else{

		return $APP;
	}

	return null;
}

/**
* Displays the names of all registered plugins.
*/
function showPlugins()
{
	global $APP;

	$names = array_column($APP['plugins'], 'name');
	dd($names ?? []);

}

/**
* Splits a URL query string into an array of segments.
* 
* @param string $url The URL to split.
* @return array An array of URL segments.
*/
function splitUrl($url)
{
	return explode("/", trim($url, '/'));
}


/**
* Retrieves a specific segment from the URL or the entire URL array.
* 
* @param string|int $key The index of the segment to retrieve. If empty, returns the entire URL array.
* @return string|array Returns the URL segment or the entire URL array.
*/
function URL($key = '')
{
	global $APP;

	if (is_numeric($key) || !empty($key))
	{
		if (!empty($APP['URL'][$key]))
		{
			return $APP['URL'][$key];
		}
	}else{
		return $APP['URL'];
	}

	return '';
}

/**
* Retrieves a list of all plugin folders.
* 
* @return array An array of plugin folder names.
*/
function getPluginFolders()
{
	$plugins_folder = 'plugins/';
	$res = [];
	$folders = scandir($plugins_folder);
	foreach ($folders as $folder) {
		if ($folder != '.' && $folder != '..' && is_dir($plugins_folder . $folder))
			$res[] = $folder;
	}

	return $res;
}

/**
* Loads and activates plugins based on their config.json files.
* 
* @param array $plugin_folders An array of plugin folder names.
* @return bool Returns true if plugins were successfully loaded, otherwise false.
*/
function loadPlugins($plugin_folders)
{
	global $APP;
	$loaded = false;

	foreach ($plugin_folders as $folder) {

		$file = 'plugins/' . $folder . '/config.json';
		if (file_exists($file))
		{
			$json = json_decode(file_get_contents($file));

			if (is_object($json) && isset($json->id))
			{
				if (!empty($json->active))
				{
					$file = 'plugins/' . $folder . '/plugin.php';
					if (file_exists($file) && validRoute($json))
					{
						$json->index = $json->index ?? 1;
						$json->index_file = $file;
						$json->path = 'plugins/' . $folder . '/';
						$json->http_path = ROOT . '/' . $json->path;

						$APP['plugins'][] = $json;

					}
				}
			}
		}
	}

	if (!empty($APP['plugins']))
	{
		$APP['plugins'] = sortPlugins($APP['plugins']);
		foreach ($APP['plugins'] as $json)
		{
			if (file_exists($json->index_file))
			{
				require_once $json->index_file;
				$loaded = true;
			}
		}
	}

	return $loaded;
}

/**
* Sorts an array of plugins by their index.
* 
* @param array $plugins An array of plugin objects.
* @return array The sorted array of plugins.
*/
function sortPlugins(array $plugins):array
{
	$to_sort = [];
	$sorted = [];

	foreach ($plugins as $key => $obj) {
		$to_sort[$key] = $obj->index;
	}

	asort($to_sort);

	foreach ($to_sort as $key => $value) {
		$sorted[] = $plugins[$key];
	}

	return $sorted;
}

/**
* Validates whether the current page is within the allowed or disallowed routes of a plugin.
* 
* @param object $json The plugin configuration object.
* @return bool Returns true if the route is valid, otherwise false.
*/
function validRoute(object $json):bool
{
	if (!empty($json->routes->off) && is_array($json->routes->off))
	{
		if (in_array(page(), $json->routes->off))
			return false;
	}

	if (!empty($json->routes->on) && is_array($json->routes->on))
	{
		if ($json->routes->on[0] == 'all')
			return true;

		if (in_array(page(), $json->routes->on))
			return true;
	}

	return false;
}

/**
* Adds a function to a specific hook with a given priority.
* 
* @param string $hook The name of the hook.
* @param mixed $func The function to add.
* @param int $priority The priority of the function. Default is 10.
* @return bool Always returns true.
*/
function addAction(string $hook, mixed $func, int $priority = 10):bool
{

	global $ACTIONS;

	while (!empty($ACTIONS[$hook][$priority])) {
		$priority++;
	}

	$ACTIONS[$hook][$priority] = $func;

	return true;
}

/**
* Executes all functions attached to a specific hook.
* 
* @param string $hook The name of the hook.
* @param array $data Data to pass to the functions.
*/
function doAction(string $hook, array $data = [])
{
	global $ACTIONS;

	if (!empty($ACTIONS[$hook]))
	{
		ksort($ACTIONS[$hook]);
		foreach ($ACTIONS[$hook] as $key => $func) {
			$func($data);
		}
	}

}

/**
* Adds a function to a specific filter with a given priority.
* 
* @param string $hook The name of the filter.
* @param mixed $func The function to add.
* @param int $priority The priority of the function. Default is 10.
* @return bool Always returns true.
*/
function addFilter(string $hook, mixed $func, int $priority = 10):bool
{
	global $FILTERS;

	while (!empty($FILTERS[$hook][$priority])) {
		$priority++;
	}

	$FILTERS[$hook][$priority] = $func;

	return true;
}

/**
* Applies all functions attached to a specific filter.
* 
* @param string $hook The name of the filter.
* @param mixed $data Data to pass through the functions.
* @return mixed The filtered data.
*/
function doFilter(string $hook, mixed $data = ''):mixed
{
	global $FILTERS;

	if (!empty($FILTERS[$hook]))
	{
		ksort($FILTERS[$hook]);
		foreach ($FILTERS[$hook] as $key => $func) {
			$data = $func($data);
		}
	}

	return $data;
}

/**
* Prints and formats data for debugging.
* 
* @param mixed $data The data to debug.
*/
function dd($data)
{
	echo "<pre><div style='margin: 1px;background-color: #444;color: white;padding: 5px 10px'>";
	print_r($data);
	echo "</div></pre>";
}

/**
* Retrieves the current page from the URL.
* 
* @return string The current page URL segment.
*/
function page()
{
	return URL(0);
}

/**
* Redirects the browser to a specified URL.
* 
* @param string $url The URL to redirect to.
*/
function redirect($url)
{
	header("Location: ". ROOT .'/'. $url);
	die;
}

/**
* Retrieves the path to the plugin directory from the calling file.
* 
* @param string $path Optional path to append to the plugin directory.
* @return string The full path to the plugin directory.
*/
function pluginPath(string $path = '')
{
	$called_from = debug_backtrace();
	$key = array_search(__FUNCTION__, array_column($called_from, 'function'));
	return getPluginDir(debug_backtrace()[$key]['file']) . $path;
}

/**
* Retrieves the HTTP path to the plugin directory from the calling file.
* 
* @param string $path Optional path to append to the HTTP plugin directory.
* @return string The full HTTP path to the plugin directory.
*/
function pluginHttpPath(string $path = '')
{
	$called_from = debug_backtrace();
	$key = array_search(__FUNCTION__, array_column($called_from, 'function'));

	return ROOT . DIRECTORY_SEPARATOR . getPluginDir(debug_backtrace()[$key]['file']) . $path;
}

/**
* Extracts the plugin directory from a given file path.
* 
* @param string $filepath The file path to extract the plugin directory from.
* @return string The extracted plugin directory path.
*/
function getPluginDir(string $filepath):string
{

	$path = "";

	$basename = basename($filepath);
	$path = str_replace($basename, "", $filepath);

	if (strstr($path, DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR, $path));
	{
		$parts = explode(DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR, $path);
		$parts = explode(DIRECTORY_SEPARATOR, $parts[1]);
		$path = 'plugins' . DIRECTORY_SEPARATOR . $parts[0];

	}

	return $path;
}

/**
* Checks if the user has a specific permission.
* 
* @param string|null $permission The permission to check. If null, always returns true.
* @return bool Returns true if the user has the permission, otherwise false.
*/
function userCan(?string $permission):bool
{
	if (empty($permission)) return true;

	$ses = new \Core\Session;

	if ($permission == 'logged_in')
	{
		if ($ses->isLoggedIn())
			return true;

		return false;
	}

	if ($permission == 'not_logged_in')
	{
		if (!$ses->isLoggedIn())
			return true;

		return false;
	}

	if ($ses->isAdmin())
		return true;

	global $APP;

	if (empty($APP['user_permissions']))
		$APP['user_permissions'] = [];

	$APP['user_permissions'] = doFilter('before_check_permissions', $APP['user_permissions']);

	if (in_array($permission, $APP['user_permissions']))
		return true;

	return false;
}

/**
* Retrieves the old value of a form field from POST or GET data.
* 
* @param string $key The field key to retrieve.
* @param string $default The default value to return if the field is not found.
* @param string $type The type of request ('post' or 'get'). Default is 'post'.
* @return string The old value or the default.
*/
function oldValue(string $key, string $default = '', string $type = 'post'):string
{
	$array = $_POST;
	if ($type == 'get')
		$array = $_GET;

	if (!empty($array[$key]))
		return $array[$key];

	return $default;
}

/**
* Checks if a specific value should be selected in a select field.
* 
* @param string $key The field key to check.
* @param string $value The value to check.
* @param string $default The default value to check if the field is empty.
* @param string $type The type of request ('post' or 'get'). Default is 'post'.
* @return string ' selected ' if the value matches, otherwise an empty string.
*/
function oldSelect(string $key, string $value, string $default = '', string $type = 'post'):string
{
	$array = $_POST;
	if ($type == 'get')
		$array = $_GET;

	if (!empty($array[$key]))
	{
		if ($array[$key] == $value)
			return ' selected ';
	}else
	{
		if ($default == $value)
			return ' selected ';
	}

	return '';
}

/**
* Checks if a specific value should be checked in a checkbox field.
* 
* @param string $key The field key to check.
* @param string $value The value to check.
* @param string $default The default value to check if the field is empty.
* @param string $type The type of request ('post' or 'get'). Default is 'post'.
* @return string ' checked ' if the value matches, otherwise an empty string.
*/
function oldChecked(string $key, string $value, string $default = '', string $type = 'post'):string
{
	$array = $_POST;
	if ($type == 'get')
		$array = $_GET;

	if (!empty($array[$key]))
	{
		if ($array[$key] == $value)
			return ' checked ';
	}else
	{
		if ($default == $value)
			return ' checked '; 
	}

	return '';
}

/**
* Generates a CSRF token and returns an HTML hidden input field for it.
* 
* @param string $sesKey The session key to use for the CSRF token. Default is 'csrf'.
* @param int $hours The expiration time in hours. Default is 1 hour.
* @return string An HTML hidden input field containing the CSRF token.
*/
function csrf(string $sesKey = 'csrf', int $hours = 1):string
{
	$key = '';

	$ses = new \Core\Session;
	$key = hash('sha256', time() . rand(0,99));
	$expires = time() + ((60*60)*$hours);

	$ses->get($sesKey,[
		'key'=>$key,
		'expires'=>$expires
	]);

	return "<input type='hidden' value='$key' name='$sesKey' />";
}

/**
* Verifies a CSRF token from a POST request.
* 
* @param array $post The POST data containing the CSRF token.
* @param string $sesKey The session key used for the CSRF token. Default is 'csrf'.
* @return mixed Returns true if the token is valid and not expired, otherwise false.
*/
function csrfVerify(array $post, string $sesKey = 'csrf'):mixed
{
	if (empty($post[$sesKey]))
		return false;

	$ses = new \Core\Session;
	$data = $ses->get($sesKey);
	if (is_array($data))
	{
		if ($data['key'] !== $post[$sesKey])
			return false;

		if ($data['expires'] > time())
			return true;

		$ses->pop($sesKey);

	}

	return false;
}

/**
* Retrieves the URL of an image based on its path or type.
* 
* @param string $path The path to the image file.
* @param string $type The type of image ('post', 'male', 'female'). Default is 'post'.
* @return string The full URL to the image.
*/
function getImage(string $path = '', string $type = 'post')
{
	if (file_exists($path))
		return ROOT . '/' . $path;

	if ($type == 'post')
		return ROOT . '/assets/images/no_image.jpg';

	if ($type == 'male')
		return ROOT . '/assets/images/user_male.jpg';

	if ($type == 'female')
		return ROOT . '/assets/images/user_female.jpg';

	return ROOT . '/assets/images/no_image.jpg';
}

/**
* Escapes a string for safe output in HTML.
* 
* @param string|null $str The string to escape.
* @return string|null The escaped string.
*/
function esc(?string $str):?string
{
	return htmlspecialchars($str);
}

/**
* Formats a date string into a readable format.
* 
* @param string $date The date string to format.
* @return string The formatted date.
*/
function get_date(string $date):string
{
	return date("jS M, Y", strtotime($date));
}

/**
* Manages flash messages stored in the session.
* 
* @param string $msg Optional message to set. If empty, retrieves the message from the session.
* @param bool $erase Whether to erase the message after retrieval. Default is false.
* @return string|null The message or an empty string if not set.
*/
function message(string $msg = '', bool $erase = false):?string
{
	$ses = new \Core\Session;

	if (!empty($msg))
	{
		$ses->set('message', $msg);
	}else
	if (!empty($ses->get('message')))
	{
		$msg = $ses->get('message');

		if ($erase)
			$ses->pop('message');

		return $msg;
	}

	return '';
}