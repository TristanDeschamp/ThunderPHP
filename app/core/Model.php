<?php

namespace Model;
use \Core\Database;

defined('ROOT') or die ("Direct script access denied");

/**
* Model Class
* Extends the Dtabase class to provide a base model for interacting with database tables 
*/
class Model extends Database
{

	public $order 				= 'desc';	/* Default order direction */
	public $order_column 	= 'id';		/* Default order column */
	public $primary_key 		= 'id';		/* Default primary key */

	public $limit 				= 10;			/* Default limit for queries */
	public $offset 			= 0;			/* Defaulr offset for queries */
	public $errors 			= [];			/* Array to store errors */

	/**
   * Selects rows from the table based on given conditions.
   * @param array $where_array Conditions for where clause.
   * @param array $where_not_array Conditions for where not clause.
   * @param string $data_type Type of data to return (object or array).
   * @return array|bool The result set or false if an error occurred.
   */
	public function where(array $where_array = [], array $where_not_array = [], string $data_type = 'object'):array|bool
	{

		$query = "select * from $this->table where ";

		if (!empty($where_array))
		{
			foreach ($where_array as $key => $value) {
				$query .= $key . '= :'.$key . ' && ';
			}
		}

		if (!empty($where_not_array))
		{
			foreach ($where_not_array as $key => $value) {
				$query .= $key . ' != :'.$key . ' && ';
			}
		}

		$query = trim($query, ' && ');
		$query .= " order by $this->order_column $this->order limit $limit offset $offset";

		$data = array_merge($where_array, $where_not_array);

		return $this->query($query, $data);

	}

	/**
   * Returns the first row that matches the given conditions.
   * @param array $where_array Conditions for where clause.
   * @param array $where_not_array Conditions for where not clause.
   * @param string $data_type Type of data to return (object or array).
   * @return array|bool The first row or false if no rows found.
   */
	public function first(array $where_array = [], array $where_not_array = [], string $data_type = 'object'):array|bool
	{

		$rows = $this->where($where_array, $where_not_array, $data_type);
		if (!empty($rows))
			return $rows[0];	/* Return the first row */

		return false;	/* Return false if no row found */
	}

	/**
   * Returns all rows from the table.
   * @param string $data_type Type of data to return (object or array).
   * @return array|bool The result set or false if an error occurred.
   */
	public function getAll(string $data_type = 'object'):array|bool
	{

		$query = "select * from $this->table order by $this->order_column $this->order limit $limit offset $offset";
		return $this->query($query, [], $data_type);
	}

	/**
   * Inserts a new row into the table.
   * @param array $data The data to insert.
   * @return bool The result of the insert operation.
   */
	public function insert(array $data)
	{
		if (!empty($this->allowedColumns))
		{
			foreach ($data as $key => $value) {
				if (!in_array($key, $this->allowedColumns))
				{
					unset($data[$key]);	/* Remove disallowed columns */
				}
			}
		}

		if (!empty($data))
		{
			$keys = array_keys($data);

			$query = "insert into $this->table (".implode(",", $keys).") values (:".implode(",:", $keys).")";
			return $this->query($query, $data);
		}

		return false;
	}

	/**
   * Updates a row in the table.
   * @param string|int $my_id The ID of the row to update.
   * @param array $data The data to update.
	* @return bool The result of the update operation.
   */
	public function update(string|int $my_id, array $data)
	{
		if (!empty($this->allowedUpdateColumns) || !empty($this->allowedColumns))
		{
			$this->allowedUpdateColumns = empty($this->allowedUpdateColumns) ? $this->allowedColumns : $this->allowedUpdateColumns;
			foreach ($data as $key => $value) {
				if (!in_array($key, $this->allowedUpdateColumns))
				{
					unset($data[$key]);	/* Remove disallowed columns */
				}
			}
		}

		if (!empty($data))
		{
			$query = "update $this->table ";
			foreach ($data as $key => $value) {

				$query .= $key . '= :'.$key.",";
			}

			$query = trim($query, ",");
			$data['my_id'] = $my_id;

			$query .= " where $this->primary_key = :my_id";
			return $this->query($query, $data);
		}

		return false;
	}

	/**
   * Deletes a row from the table.
   * @param string|int $my_id The ID of the row to delete.
   * @return bool The result of the delete operation.
   */
	public function delete(string|int $my_id)
	{

		$query = "delete from $this->table ";
		$query .= " where $this->primary_key = :my_id limit 1";

		return $this->query($query, $data);

	}


}