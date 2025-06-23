<?php
/**
 * Data-Entities: Employee-Department-Relationship
 *
 * This file contains the Employee-Department-Class.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */
namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the Wordpress-backend.
defined('ABSPATH') or die('Unauthorized!');


/**
 * Data-Entity representing the Employee-Department-Relationship.
 *
 * The Employee-Department-Class realizes the 1-to-n-relationship between an employee and
 * the departments.
 * The class consists of static methods for interaction with the database and
 * constructs objects which capsule all necessary information to represent this relationship.
 */
class Employee_Department {
	/**
	 * The name of the database-table.
	 *
	 * @since 1.0.0
	 * @var string The name of the database-table where all information regarding the
	 *              employee-department-relationship are stored.
	 */
	public static $db_table = "btz_employee_list_employee_departments";

	/**
	 * The employee-id.
	 *
	 * @since 1.0.0
	 * @var int The employee-id-property to store the database-id of an employee.
	 */
	private $employee_id;

	/**
	 * The departments-ids.
	 *
	 * @since 1.0.0
	 * @var array The ids of all departments the employee belongs to.
	 */
	private $departments = [];


	/**
	 * A getter-method for the employee_id-property.
	 *
	 * @since 1.0.0
	 *
	 * @return int The employee_id-property of the employee-department-relationship.
	 */
	public function get_employee_id() {
		return $this->employee_id;
	}

	/**
	 * A getter-method for the departments-id-property.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array with the ids of all departments associated with this employee.
	 */
	public function get_departments() {
		return $this->departments;
	}


	/**
	 * Primary constructor.
	 *
	 * The primary constructor to create new employee-department-objects. It is called with an
	 * employee-id and requests the department-ids belonging to this employee from the database.
	 *
	 * @since 1.0.0
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 * @param $employee_id int The database id of an employee.
	 */
	public function __construct($employee_id) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$this->employee_id = $employee_id;

		$sql = "SELECT * FROM " . self::$db_table . " WHERE employee_id = " . $employee_id;
		$department_ids = $wpdb->get_results($sql);

		foreach ($department_ids as $department_id) {
			$this->departments[] = Department::get_by_id($department_id->department_id);
		}
	}


	/**
	 * Creates the database-table.
	 *
	 * Creates the database-table to store the n-to-n relation between employees and
	 * departments in the WordPress-Database.
	 *
	 * @since 1.0.0
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @return void
	 */
	public static function create_db_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$table = self::$db_table;

		$employee_table = Employee::$db_table;
		$department_table = department::$db_table;

		$sql = "CREATE TABLE IF NOT EXISTS $table (
    		employee_id INT NOT NULL, -- Foreign Key
    		department_id INT NOT NULL, -- Foreign Key
    		FOREIGN KEY (employee_id) REFERENCES $employee_table(id) ON DELETE CASCADE,
    		FOREIGN KEY (department_id) REFERENCES $department_table(id) ON DELETE CASCADE,
            PRIMARY KEY (employee_id, department_id)
        ) {$wpdb->get_charset_collate()};";
		dbDelta($sql);
	}








	/**
	 * Registers an employee-department-relationship.
	 *
	 * Registers a relationship between an employee and a department.
	 *
	 * @since 1.0.0
	 *
	 * @param $employee_id int The numeric employee-id used for this association.
	 * @param $department_id int The numeric department-id used for this association.
	 *
	 * @return void
	 */
	public static function register($employee_id, $department_id) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$data = array('employee_id' => $employee_id, 'department_id' => $department_id);
		$wpdb->insert(self::$db_table, $data);
	}





	/**
	 * Remove an employee.
	 *
	 * Remove an employee from the known employee-department-associations.
	 * This method DOES NOT delete an employee from the database.
	 *
	 * @since 1.0.0
	 *
	 * @see Employee for the deletion of employees from the database.
	 *
	 * @param $employee_id int The numeric employee-id to be removed.
	 *
	 * @return void
	 */
	public static function clear_department_associations($employee_id) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$wpdb->delete(self::$db_table, array('employee_id' => $employee_id), array('%d'));
	}



	/**
	 * Lists the associated departments.
	 *
	 * Lists the ids of the associated departments, seperated by spaces.
	 *
	 * @since 1.0.0
	 *
	 * @return string A space-seperated list of the ids of the associated departments.
	 */
	public function to_spaced_list() {
		#return null;
		$str = "";
		foreach ($this->departments as $department) {
			$id = $department->get_id();
			$str .= "$id ";
		}
		return $str;
	}



	public function to_json() {
		$json = array();
		foreach ($this->departments as $department) {
			$temp[] = array();
			$temp['id'] = $department->get_id();
			$temp['department'] = $department->get_department();
			$json[] = $temp;
		}
		return $json;
	}

}
