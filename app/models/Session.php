<?php

namespace Core;

defined('ROOT') or die ("Direct script access denied");

/**
* Session Class
*/
class Session
{
	private $varKey = 'APP';	/* Session key for application data */
	private $userKey = 'USER';	/* Session key for user data */

	/**
   * Start the session if it has not already been started
   *
   * @return int
   */
	private function startSession():int
	{
		if (session_status() === PHP_SESSION_NONE)
			session_start();

		return 1;
	}

	/**
   * Set a session variable or multiple variables
   *
   * @param string|array $keyOrArray
   * @param mixed $value
   * @return bool
   */
	public function set(string|array $keyOrArray, mixed $value = null):bool
	{
		$this->startSession();
		if (is_array($keyOrArray))
		{
			foreach ($keyOrArray as $key => $value) {
				$_SESSION[$this->varKey][$key] = $value;
			}
			return true;
		}else
		{
			$_SESSION[$this->varKey][$keyOrArray] = $value;
			return true;
		}

		return false;
	}

	/**
   * Get a session variable by key
   *
   * @param string $key
   * @return mixed
   */
	public function get(string $key):mixed
	{
		$this->startSession();
		if (!empty($_SESSION[$this->varKey][$key]))
		{
			return $_SESSION[$this->varKey][$key];
		}

		return false;
	}

	/**
   * Get and remove a session variable by key
   *
   * @param string $key
   * @return mixed
   */
	public function pop(string $key):mixed
	{
		$this->startSession();
		if (!empty($_SESSION[$this->varKey][$key]))
		{
			$var = $_SESSION[$this->varKey][$key];
			unset($_SESSION[$this->varKey][$key]);
			return $var;
		}

		return false;
	}

	/**
   * Authenticate a user and store their information in the session
   *
   * @param object|array $row
   * @return bool
   */
	public function auth(object|array $row):bool
	{
		$this->startSession();
		$_SESSION[$this->userKey] = $row;

		return true;
	}

	/**
   * Check if the current user is an admin
   *
   * @return bool
   */
	public function isAdmin():bool
	{

		if (!$this->isLoggedIn())
			return false;

		$arr = doFilter('before_check_admin', ['is_admin'=>false]);

		if ($arr['is_admin'])
			return true;

		return false;
	}

	/**
   * Check if a user is logged in
   *
   * @return bool
   */
	public function isLoggedIn():bool
	{
		$this->startSession();

		if (empty($_SESSION[$this->userKey]))
			return false;

		if (is_object($_SESSION[$this->userKey]))
			return true;

		if (is_array($_SESSION[$this->userKey]))
			return true;

		return false;
	}

	/**
   * Destroy the session and regenerate a new session ID
   *
   * @return bool
   */
	public function reset():bool
	{
		session_destroy();
		session_regenerate_id();

		return true;
	}

	/**
   * Log out the user by removing their information from the session
   *
   * @return bool
   */
	public function logout():bool
	{
		$this->startSession();

		if (!empty($_SESSION[$this->userKey]))
			unset($_SESSION[$this->userKey]);

		return true;
	}

	/**
   * Get the authenticated user's information or a specific attribute
   *
   * @param string $key
   * @return mixed
   */
	public function user(string $key = ''):mixed
	{
		$this->startSession();

		if (!empty($_SESSION[$this->userKey]))
		{
			if (empty($key))
				$_SESSION[$this->userKey];

			if (is_object($_SESSION[$this->userKey]))
			{
				if (!empty($_SESSION[$this->userKey]->$key))
					return $_SESSION[$this->userKey]->$key;
			}else
			if (is_array($_SESSION[$this->userKey]))
			{
				if (!empty($_SESSION[$this->userKey][$key]))
					return $_SESSION[$this->userKey][$key];
			}

		}

		return null;
	}

	/**
   * Get all session variables under the application key
   *
   * @return mixed
   */
	public function all():mixed
	{
		$this->startSession();
		if (!empty($_SESSION[$this->varKey]))
		{
			return $_SESSION[$this->varKey];
		}

		return null;
	}

}