<?php

namespace Core;

defined('ROOT') or die ("Direct script access denied");

/**
* Request Class
*/
class Request
{
	public $upload_max_size 	= 20;	/* Maximum file upload size in MB */
	public $upload_folder 		= 'uploads';	/* Default folder for file uploads */
	public $upload_errors 		= [];	/* Array to hold any upload errors */
	public $upload_error_code 	= 0;	/* Error code for the last upload attempt */
	public $upload_file_type 	= [
		'image/jpeg',
		'image/png',
		'image/webp',
		'image/gif',
	];	/* Allowed file types for upload */

	/**
   * Get the request method (GET, POST, etc.)
   *
   * @return string
   */
	public function method():string
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
   * Check if the request method is POST
   *
   * @return bool
   */
	public function posted():bool
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	/**
   * Retrieve a POST variable or all POST data if no key is provided
   *
   * @param string $key
   * @return string|array
   */
	public function post(string $key = ''):string|array
	{
		if (empty($key))
			return $_POST;

		if (!empty($_POST[$key]))
			return $_POST[$key];

		return '';
	}

	/**
   * Retrieve a POST variable or a default value if not set
   *
   * @param string $key
   * @param string $default
   * @return string
   */
	public function input(string $key, string $default = ''):string
	{
		if (!empty($_POST[$key]))
			return $_POST[$key];

		return $default;
	}

	/**
   * Retrieve a GET variable or all GET data if no key is provided
   *
   * @param string $key
   * @return string|array
   */
	public function get(string $key = ''):string
	{
		if (empty($key))
			return $_GET;

		if (!empty($_GET[$key]))
			return $_GET[$key];

		return '';
	}

	/**
   * Retrieve a FILES variable or all FILES data if no key is provided
   *
   * @param string $key
   * @return string|array
   */
	public function files(string $key = ''):string|array
	{
		if (empty($key))
			return $_FILES;

		if (!empty($_FILES[$key]))
			return $_FILES[$key];

		return '';
	}

	/**
   * Retrieve a REQUEST variable or all REQUEST data if no key is provided
   *
   * @param string $key
   * @return string|array
   */
	public function all(string $key = ''):string|array
	{
		if (empty($key))
			return $_REQUEST;

		if (!empty($_REQUEST[$key]))
			return $_REQUEST[$key];

		return '';
	}

	/**
   * Handle file uploads and store them in the specified folder
	*
   * @param string $key
   * @return string|array
   */
	public function uploadFiles(string $key = ''):string|array
	{
		$this->upload_errors 			= [];
		$this->upload_error_code 		= 0;

		$uploaded = empty($key) ? [] : '';

		if (!empty($this->files()))
		{
			$get_one = false;
			if (!empty($key))
				$get_one = true;

			if ($get_one && empty($this->files()[$key]))
			{
				$this->upload_errors['name'] = "File not found";
				return '';
			}

			$uploaded = [];
			foreach ($this->files() as $key => $file_arr) {

				if ($file_arr['error'] > 0)
				{
					$this->upload_error_code = $file_arr['error'];
					$this->upload_errors[] = "An error occured with file: ". $file_arr['name'];
					continue;
				}

				if (!in_array($file_arr['type'], $this->upload_file_type))
				{
					$this->upload_errors[] = "Invalid file type: ". $file_arr['name'];
					continue;
				}

				if ($file_arr['size'] > ($this->upload_max_size * 1024 * 1024))
				{
					$this->upload_errors[] = "File too large: ". $file_arr['name'];
					continue;
				}

				$folder = trim($this->upload_folder, '/') . '/';
				$destination = $folder . $file_arr['name'];

				$num = 0;
				while (file_exists($destination) && $num < 10)
				{
					$num++;
					$ext = explode(".", $destination);
					$ext = end($ext);

					$destination = preg_replace("/\.$ext$/", "_" . rand(0,99) . ".$ext", $destination);
				}

				if (!is_dir($folder))
					mkdir($folder, 0777, true);

				move_uploaded_file($file_arr['tmp_name'], $destination);
				$uploaded[] = $destination;

				if ($get_one)
					break;
			}

			if ($get_one)
				return $uploaded[0] ?? '';

			return $uploaded;

		}

		return $uploaded;
	}


}