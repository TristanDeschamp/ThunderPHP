<?php

namespace Migration;

defined('FCPATH') or die ("Direct script access denied");

use \Core\Database;
/**
* Migration Class
*/
class Migration extends Database
{
	private $columns 			= [];
	private $keys 				= [];
	private $data 				= [];
	private $primaryKeys 	= [];
	private $foreignKeys 	= [];
	private $uniqueKeys 		= [];
	private $fullTextKeys 	= [];

	/**
   * Creates a new table with the specified columns, keys, and constraints.
   *
   * @param string $table The name of the table to create.
   */
	public function createTable(string $table)
	{
		if (!empty($this->columns))
		{

			$query = "CREATE TABLE IF NOT EXISTS $table (";

			/* Add columns */
			$query .= implode(",", $this->columns) . ',';

			/* Add primary keys */
			foreach ($this->primaryKeys as $key) {
				$query .= "primary key ($key),";
			}

			/* Add keys */
			foreach ($this->keys as $key) {
				$query .= "key ($key),";
			}

			/* Add unique keys */
			foreach ($this->uniqueKeys as $key) {
				$query .= "unique key ($key),";
			}

			/* Add full-text keys */
			foreach ($this->fullTextKeys as $key) {
				$query .= "fulltext key ($key),";
			}

			$query = trim($query, ",");

			/* Specify table options */
			$query .= ")ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4";

			/* Execute the query */
			$this->query($query);

			/* Clear the properties for reuse */
			$this->columns 		= [];
			$this->keys 			= [];
			$this->data 			= [];
			$this->primaryKeys 	= [];
			$this->foreignKeys 	= [];
			$this->uniqueKeys 	= [];
			$this->fullTextKeys 	= [];

			echo "\n\rTable $table created successfully !";
		}else{

			echo "\n\rColumn data not found ! Could not create table: $table";
		}
	}

	/**
   * Inserts data into the specified table.
   *
   * @param string $table The name of the table to insert data into.
   */
	public function insert(string $table)
	{
		if (!empty($this->data) && is_array($this->data))
		{

			foreach ($data as $rows) {

				$keys = array_keys($rows);
				$column_string = implode(",:", $keys);
				$values_string = ':'.implode(",:", $keys);

				$query = "INSERT INTO $table ($column_string) VALUES ($values_string)";
				$this->query($query, $rows);
			}

			$this->data = [];
			echo "\n\rData inserted successfully in table: $table";
		}else
		{
			echo "\n\rRow data not found ! No data inserted in table: $table";
		}
	}

	/**
   * Adds a column to the table schema.
   *
   * @param string $column The column definition.
   */
	public function addColumn(string $column)
	{
		$this->columns[] = $column;
	}

	/**
   * Adds a key to the table schema.
   *
   * @param string $key The key definition.
   */
	public function addKey(string $key)
	{
		$this->keys[] = $key;
	}

	/**
   * Adds a primary key to the table schema.
   *
   * @param string $primaryKey The primary key definition.
   */
	public function addPrimaryKey(string $primaryKey)
	{
		$this->primaryKeys[] = $primaryKey;
	}

	/**
   * Adds a unique key to the table schema.
   *
   * @param string $key The unique key definition.
   */
	public function addUniqueKey(string $key)
	{
		$this->uniqueKeys[] = $key;
	}

	/**
   * Adds a full-text key to the table schema.
   *
   * @param string $key The full-text key definition.
   */
	public function addFullTextKey(string $key)
	{
		$this->fullTextKeys[] = $key;
	}

	/**
   * Adds data to be inserted into the table.
   *
   * @param array $data The data to insert.
   */
	public function addData(array $data)
	{
		$this->data[] = $data;
	}

	/**
   * Drops the specified table.
   *
   * @param string $table The name of the table to drop.
   */
	public function dropTable(string $table)
	{
		$query = "DROP TABLE IF EXISTS $table ";
		$this->query($query);

		echo "\n\rTable $table deleted successfully !";
	}
}