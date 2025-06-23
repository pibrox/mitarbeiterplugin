<?php
/**
 * Data-Entities: Employee-Occupation-Relationship
 *
 * This file contains the Employee-Occupation-Class.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */
namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the Wordpress-backend.
defined('ABSPATH') or die('Unauthorized!');


/**
 * Data-Entity representing the Employee-Occupation-Relationship.
 *
 * The Employee-Occupation-Class realizes the 1-to-n-relationship between an employee and
 * the occupations.
 * The class consists of static methods for interaction with the database and
 * constructs objects which capsule all necessary information to represent this relationship.
 */
class Employee_Occupation {
	/**
	 * The name of the database-table.
	 *
	 * @since 1.0.0
	 * @var string The name of the database-table where all information regarding the
	 *              employee-occupation-relationship are stored.
	 */
	public static $db_table = "btz_employee_list_employee_occupations";

	/**
	 * The employee-id.
	 *
	 * @since 1.0.0
	 * @var int The employee-id-property to store the database-id of an employee.
	 */
	private $employee_id;

	/**
	 * The occupation-ids.
	 *
	 * @since 1.0.0
	 * @var array The ids of all occupations the employee belongs to.
	 */
	private $occupations = [];


	/**
	 * A getter-method for the employee_id-property.
	 *
	 * @since 1.0.0
	 *
	 * @return int The employee_id-property of the employee-occupation-relationship.
	 */
	public function get_employee_id() {
		return $this->employee_id;
	}

	/**
	 * A getter-method for the occupation-id-property.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array with the ids of all occupations associated with this employee.
	 */
	public function get_occupations() {
		return $this->occupations;
	}


	/**
	 * Primary constructor.
	 *
	 * The primary constructor to create new employee-occupation-objects. It is called with an
	 * employee-id and requests the occupation-ids belonging to this employee from the database.
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
		$occupation_ids = $wpdb->get_results($sql);

		foreach ($occupation_ids as $occupation_id) {
			$this->occupations[] = Occupation::get_by_id($occupation_id->occupation_id);
		}
	}


	/**
	 * Creates the database-table.
	 *
	 * Creates the database-table to store the n-to-n relation between employees and
	 * occupations in the WordPress-Database.
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
		$occupation_table = Occupation::$db_table;

		$sql = "CREATE TABLE IF NOT EXISTS $table (
    		employee_id INT NOT NULL, -- Foreign Key
    		occupation_id INT NOT NULL, -- Foreign Key
    		FOREIGN KEY (employee_id) REFERENCES $employee_table(id) ON DELETE CASCADE,
    		FOREIGN KEY (occupation_id) REFERENCES $occupation_table(id) ON DELETE CASCADE,
            PRIMARY KEY (employee_id, occupation_id)
        ) {$wpdb->get_charset_collate()};";
		dbDelta($sql);
	}







	/**
	 * Registers an employee-occupation-relationship.
	 *
	 * Registers a relationship between an employee and an occupation.
	 *
	 * @since 1.0.0
	 *
	 * @param $employee_id int The numeric employee-id used for this association.
	 * @param $occupation_id int The numeric occupation-id used for this association.
	 *
	 * @return void
	 */
	public static function register($employee_id, $occupation_id) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$data = array('employee_id' => $employee_id, 'occupation_id' => $occupation_id);
		$wpdb->insert(self::$db_table, $data);
	}




	/**
	 * Remove an employee.
	 *
	 * Remove an employee from the known employee-occupation-associations.
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
	public static function clear_occupation_associations($employee_id) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$wpdb->delete(self::$db_table, array('employee_id' => $employee_id,), array('%d',));
	}




	/**
	 * Lists the associated occupations.
	 *
	 * Lists the ids of the associated occupations, seperated by spaces.
	 *
	 * @since 1.0.0
	 *
	 * @return string A space-seperated list of the ids of the associated occupations.
	 */
	public function to_spaced_list() {
		$str = "";
		foreach ($this->occupations as $occupation) {
			$id = $occupation->get_id();
			$str .= "$id ";
		}
		return $str;
	}



	public function to_json() {
		$json = array();
		foreach ($this->occupations as $occupation) {
			$temp[] = array();
			$temp['id'] = $occupation->get_id();
			$temp['occupation'] = $occupation->get_occupation();
			$temp['male-form'] = $occupation->get_male_form();
			$temp['female-form'] = $occupation->get_female_form();
			$temp['diverse-form'] = $occupation->get_diverse_form();
			$json[] = $temp;
		}
		return $json;
	}

}
