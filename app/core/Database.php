<?php

namespace Core;
use \PDO;
use \PDOException;

defined('ROOT') or die ("Direct script access denied");

/**
* Database Class
* Handles database connections and queries using PDO
*/
class Database
{
	private static $query_id 	= ''; /* Static variables to store the query ID */
	public $affected_rows 		= 0;	/* Number of rows affected by the last query */
	public $insert_id 			= 0;	/* ID of the last inserted row */
	public $errors 				= '';	/* Stores error messages */
	public $has_errors 			= false;	/* Flag to indicate if there were errors */

	/**
	* Connect to the database using the configured credentials and returns the PDO instance
	* @return PDO
	*/
	private function connect()
	{

		/* Database connections variables */
		$VARS['DB_NAME'] 		= DB_NAME;
		$VARS['DB_USER'] 		= DB_USER;
		$VARS['DB_PASSWORD'] = DB_PASSWORD;
		$VARS['DB_HOST'] 		= DB_HOST;
		$VARS['DB_DRIVER'] 	= DB_DRIVER;

		/* Apply filters before connecting to the database */
		$VARS = doFilter('before_db_connect', $VARS);

		/* Connection string for PDO */
		$string = "$VARS[DB_DRIVER]:hostname=$VARS[DB_HOST];dbname=$VARS[DB_NAME]";

		try
		{
			/* Create a new PDO instance */
			$con = new PDO($string, $VARS['DB_USER'], $VARS['DB_PASSWORD']);
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); /* Set error mode to exception */

		} catch (PDOException $e) {
			/* Handle connection errors */
			die ("Failed to connect to database with error " . $e->getMessage());
		}

		return $con; /* Return the PDO instance */

	}

	/**
   * Executes a query and returns a single row.
   * @param string $query The SQL query to execute.
   * @param array $data The data to bind to the query parameters.
   * @param string $data_type The type of data to return (object or array).
   * @return mixed The first row of the result set or false if no rows found.
   */
	public function getRow(string $query, array $data = [], string $data_type = 'object')
	{

		$result = $this->query($query, $data, $data_type);
		if (is_array($result) && count($result) > 0)
		{
			return $result[0]; /* Return the first row */
		}

		return false; /* Return false if no rows found */
	}

	/**
   * Executes a query and returns the result set.
   * @param string $query The SQL query to execute.
   * @param array $data The data to bind to the query parameters.
   * @param string $data_type The type of data to return (object or array).
   * @return mixed The result set or false if an error occurred.
   */
	public function query(string $query, array $data = [], string $data_type = 'object')
	{

		/* Apply filters before executing the query */
		$query = doFilter('before_query_query', $query);
		$data = doFilter('before_query_data', $data);

		$this->errors 			= ''; /*Reset errors */
		$this->has_errors 	= false; /* Reset erro flag */

		$con = $this->connect(); /* Connect to the database */

		try
		{
			/* Prepare and execute the query */
			$stm = $con->prepare($query);

			$result = $stm->execute($data);
			$this->affected_rows 	= $stm->rowCount();		/* Get the number of affected rows */
			$this->insert_id 			= $con->lastInsertId();	/* Get the ID of the last inserted row */

			if ($result)
			{
				/* Fetch the result set */
				if ($data_type == 'object'){
					$rows = $stm->fetchAll(PDO::FETCH_OBJ);	/* Fetch as objects */
				}else{
					$rows = $stm->fetchAll(PDO::FETCH_ASSOC);	/* Fetch as associative arrays */
				}

			}

		} catch (PDOException $e)
		{
			/* Handle query errors */
			$this->errors 			= $e->getMessage();
			$this->has_errors		= true;
		}

		/* Prepare the result array */
		$arr = [];
		$arr['query'] = $query;
		$arr['data'] = $data;
		$arr['result'] = $rows ?? [];
		$arr['query_id'] = self::$query_id;
		self::$query_id = '';

		/* Apply filters after executing the query */
		$result = doFilter('after_query', $arr);

		if (is_array($result) && count($result) > 0)
		{
			return $result;	/* Return the result set */
		}

		return false;	/* Return false if an error occured */
	}
}