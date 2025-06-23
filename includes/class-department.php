<?php
/**
 * Class Department
 *
 * The Department class represents organizational departments and provides functionality
 * to interact with a database (e.g., CRUD operations). It extends the Persistable class
 * to inherit common persistence functionalities and manages department-related data.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */

namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the WordPress-backend.
defined('ABSPATH') or die('Unauthorized!');


class Department  extends Persistable {
	/**
	 * The database table name for departments.
	 * @var string
	 * @since 1.0.0
	 */
	public static $db_table = "btz_employee_list_departments";
	/**
	 * The database identifier for a department.
	 * @var int
	 * @since 1.0.0
	 */
	private $id;
	/**
	 * The name of a department.
	 * @var string
	 * @since 1.0.0
	 */
	private $department;



	/**
	 * Getter for the database table name.
	 * @return string The name of the database table.
	 * @since 1.0.0
	 */
	protected function get_db_table_name() {
		return self::$db_table;
	}

	/**
	 * Getter for the database identifier of a department.
	 * @return int The database identifier of the department.
	 * @since 1.0.0
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Getter for the department name.
	 * @return string The department name.
	 * @since 1.0.0
	 */
	public function get_department() {
		return $this->department;
	}




	/**
	 * Primary constructor.
	 *
	 * The primary constructor to create new department-objects.
	 *
	 * @param $id int The database identifier of the department. Use 0 to create a new department.
	 * @param $department string The name of the department.
	 *
	 * @since 1.0.0
	 */
	public function __construct($id, $department) {
		$this->id = $id;
		$this->department = $department;
	}


	/**
	 * Creates a new department object from a database query results.
	 *
	 * Helper-Method to create a new instance of this class right from the results of a database query.
	 *
	 * @param $result array The associative array returned from a database-query ('column-name' => 'column-value').
	 *
	 * @return self A new instance of class Department based on the query-result.
	 *
	 * @since 1.0.0
	 */
	public static function from_associative_array($result) {
		return new self(
			$result['id'],
			$result['department']
		);
	}


	/**
	 * Creates an associative-array representation.
	 *
	 * Creates an associative-array representation of the department-object.
	 * Overrode method from the abstract Persistable class.
	 *
	 * @return string[] The associative-array representing the department-object.
	 *
	 * @see Persistable
	 *
	 * @since 1.0.0
	 */
	protected function to_associative_array() {
		return array('department' => $this->department);
	}


	/**
	 * Creates the database table.
	 *
	 * Creates the database table to store departments in the WordPress-Database.
	 *
	 * @return void
	 *
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @see wp-admin/includes/upgrade.php
	 *
	 * @since 1.0.0
	 */
	public static function create_db_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$table = self::$db_table;

		$sql = "CREATE TABLE IF NOT EXISTS $table (
    		id INT NOT NULL AUTO_INCREMENT,
    		department VARCHAR(255) NOT NULL UNIQUE,
            PRIMARY KEY (id)
        ) {$wpdb->get_charset_collate()};";
		dbDelta($sql);
	}




	/**
	 * Returns all departments.
	 *
	 * Returns all departments from the database as array.
	 *
	 * @return array An array with all departments; empty, if there are no departments in the database.
	 *
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @see wp-admin/includes/upgrade.php
	 *
	 * @since 1.0.0
	 */
	public static function get_all() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$sql = "SELECT * FROM " . self::$db_table . " ORDER BY department";
		$results = $wpdb->get_results($sql, ARRAY_A);

		$departments = [];
		if ($results) {
			foreach($results as $result) {
				$departments[] = self::from_associative_array($result);
			}
		}
		return $departments;
	}


	/**
	 * Returns all departments.
	 *
	 * Returns all departments as response to an AJAX request.
	 *
	 * @return void The response is an array of associative arrays of the object fields in JSON format. If
	 *              there are no departments in the database the response is an empty array.
	 *
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @see wp_send_json_success()
	 *
	 * @since 1.0.0
	 */
    public static function ajax_get_all() {
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	    global $wpdb;

	    $sql = "SELECT * FROM " . self::$db_table . " ORDER BY department";
	    $results = $wpdb->get_results($sql, ARRAY_A);

	    wp_send_json_success($results ?: []);
    }




	/**
	 * Returns a single department.
	 *
	 * Returns the department with the given id, if it exists.
	 *
	 * @param $id int A numeric department id.
	 *
	 * @return Department|null The department with the given id or null, if a department with that id doesn't exist.
	 *
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @see wp-admin/includes/upgrade.php
	 *
	 * @since 1.0.0
	 */
	public static function get_by_id($id) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$sql = "SELECT * FROM " . self::$db_table . " WHERE id = {$id}";
		$result = $wpdb->get_row($sql, ARRAY_A);
		if ($result) {
			return self::from_associative_array($result);
		}
		return null;
	}


	/**
	 * Return a single department.
	 *
	 * Returns a single department as response to an AJAX request.
	 *
	 * @param $id
	 *
	 * @return void The response is an associative array of the objects field in JSON format. If there
	 *              is no department with the given id, the response is an empty array.
	 *
	 * @see wp_send_json_success()
	 *
	 * @since 1.0.0
	 */
	public static function ajax_get_by_id($id) {
		$department = self::get_by_id(sanitize_text_field($id));
		$temp = $department->to_associative_array();
		$temp['id'] = $id;
        wp_send_json_success($temp);
    }




	/**
	 * Deletes a department.
	 *
	 * Deletes the department with the given id as response to an AJAX request.
	 *
	 * @param $id int The numeric id of the department.
	 *
	 * @return void The response is always the id.
	 *
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @see wp_send_json_success()
	 *
	 * @since 1.0.0
	 */
	public static function ajax_delete_department($id) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;
		$wpdb->delete(self::$db_table, array('id' => $id));
        wp_send_json_success($id);
	}




	/**
	 * Handles AJAX request to persist a department.
	 *
	 * Processes and persists a department object using provided POST data,
	 * retrieves the saved department, converts it to an associative array,
	 * and sends it as a JSON response.
	 *
	 * @param array $post Array containing POST data with 'department_id' and 'department_name' fields.
	 *
	 * @return void Sends a JSON response with the newly persisted department
	 *               including its ID and other properties.
	 *
	 * @see wp_send_json_success()
	 */
    public static function ajax_persist_department($post) {
        $department = new self(
            sanitize_text_field($post['department_id']),
            sanitize_text_field($post['department_name'])
        );
        $id = $department->persist();
        $new_department = self::get_by_id($id);
        $temp = $new_department->to_associative_array();
        $temp['id'] = $id;
        wp_send_json_success($temp);
    }

}
