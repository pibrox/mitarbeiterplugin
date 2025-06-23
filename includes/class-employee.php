<?php
/**
 * Data-Entities: Employees
 *
 * This file contains the Employee-Class
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */
namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the Wordpress-backend.
defined('ABSPATH') or die('Unauthorized!');


/**
 * Data-Entity representing an employee.
 *
 * The Employee-class consists of static methods for interaction with the database and
 * constructs objects which capsule all information regarding an employee.
 *
 * @since 1.0.0
 *
 * @see Persistable
 */
class Employee extends Persistable {
	/**
	 * The name of the database-table.
	 *
	 * @since 1.0.0
	 * @var string The name of the database-table where all information regarding an employee are stored.
	 */
	public static $db_table = "btz_employee_list_employees";


	/**
	 * The employee-id.
	 *
	 * @since 1.0.0
	 * @var int The id-property to store the database-id of an employee.
	 */
	private $id;

	/**
	 * The first name of the employee.
	 *
	 * @since 1.0.0
	 * @var string The first-name-property to store the first name of an employee.
	 */
	private $first_name;

	/**
	 * The last name of the employee.
	 *
	 * @since 1.0.0
	 * @var string The last-name-property to store the last name of an employee.
	 */
	private $last_name;

	/**
	 * The room or office number of the employee.
	 *
	 * @since 1.0.0
	 * @var string The room-number-property to store the room or office number of an employee.
	 */
	private $room_number;

	/**
	 * The phone number of the employee.
	 *
	 * @since 1.0.0
	 * @var string The phone-number-property to store the phone number of an employee (as unformatted string).
	 */
	private $phone_number;

	/**
	 * The email-address of the employee.
	 *
	 * @since 1.0.0
	 * @var string The email-address-property to store the email-address of an employee.
	 */
	private $email_address;

	/**
	 * The photo of the employee.
	 *
	 * @since 1.0.0
	 * @var string The image-url-property to store the URL of a photo of an employee.
	 */
	private $image_url;

	/**
	 * The gender of the employee.
	 *
	 * @since 1.0.0
	 * @var string The gender-property to store the gender of an employee.
	 */
	private $gender;

	/**
	 * Additional information about the employee.
	 *
	 * @since 2.0.0
	 * @var string The information-property to store additional information about an employee.
	 */
	private $information;


	/**
	 * The departments an employee belongs to.
	 *
	 * @since 1.0.0
	 * @var Employee_Department An object representing the 1-to-n-relationship between an employee and the department(s).
	 */
	private $departments;

	/**
	 * The occupations an employee belongs to.
	 *
	 * @since 1.0.0
	 * @var Employee_Occupation An object representing the 1-to-n-relationship between an employee and the occupation(s).
	 */
	private $occupations;






	/**
	 * A getter-method for the id-property.
	 *
	 * @since 1.0.0
	 *
	 * @return int The id-property of the employee.
	 */
	public function get_id() {
		return $this->id;
	}

	public function get_first_name() {
		return $this->first_name;
	}

	public function get_last_name() {
		return $this->last_name;
	}

	public function get_room_number() {
		return $this->room_number;
	}

	public function get_phone_number() {
		return $this->phone_number;
	}

	public function get_email_address() {
		return $this->email_address;
	}

	public function get_image_url() {
		return $this->image_url;
	}

	public function get_gender() {
		return $this->gender;
	}

	public function get_information() {
		return $this->information;
	}

	public function get_departments() {
		return $this->departments;
	}

	public function get_occupations() {
		return $this->occupations;
	}

	public function get_ssf_pin_hash() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;
		$sql = "SELECT pin_hash FROM " . self::$db_table . " WHERE ID = '" . $this->id . "'";
		$result = $wpdb->get_row($sql, ARRAY_A);
		if (!$result) {
			return null;
		}
		return $result['pin_hash'];
	}



	/**
	 * A getter-method for the database-table-name.
	 *
	 * Overrode getter-method from the Persistable-class to abstract the database-access.
	 *
	 * @since 1.0.0
	 * @see Persistable
	 *
	 * @return string The name of the database-table used to store the employee-data.
	 */
	protected function get_db_table_name() {
		return self::$db_table;
	}


	/**
	 * Primary constructor.
	 *
	 * The primary constructor to create new employee-objects.
	 *
	 * @since 1.0.0
	 *
	 * @param $id int The database-id of the employee.
	 * @param $first_name string The first-name of the employee.
	 * @param $last_name string The last-name of the employee.
	 * @param $room_number string The room or office number of the employee.
	 * @param $phone_number string The phone-number of the employee.
	 * @param $email_address string The email-address of the employee.
	 * @param $image_url string The URL of the photo of the employee.
	 * @param $gender string The gender of the employee. Accepts 'male', 'female', 'diverse'.
	 * @param $information string Additional information about the employee.
	 */
	public function __construct($id, $first_name, $last_name, $room_number, $phone_number, $email_address, $image_url, $gender, $information) {
		$this->id = $id;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->room_number = $room_number;
		$this->phone_number = $phone_number;
		$this->email_address = $email_address;
		$this->image_url = $image_url;
		$this->gender = $gender;
		$this->information = $information;

		$this->departments = new Employee_Department($id);
		$this->occupations = new Employee_Occupation($id);
	}


	/**
	 * Creates a new employee-object from database-query-results.
	 *
	 * Helper-Method to create a new instance of this class right from the results of a database-query.
	 *
	 * @param $array array The associative array returned from a database-query ('column-name' => 'column-value').
	 *
	 * @return self A new instance of class Employee based on the query-result.
	 *@since 1.0.0
	 *
	 */
	public static function from_associative_array($array ) {
		return new self(
			$array['id'],
			$array['first_name'],
			$array['last_name'],
			$array['room_number'],
			$array['phone_number'],
			$array['email_address'],
			$array['image_url'],
			$array['gender'],
			$array['information']
		);
	}


	/**
	 * Creates an associative-array representation.
	 *
	 * Creates an associative-array representation of the employee-object.
	 * Overrode method from the abstract Persistable-class.
	 *
	 * @since 1.0.0
	 *
	 * @see Persistable
	 *
	 * @return array The associative-array representing the employee-object.
	 */
	protected function to_associative_array() {
		return array(
			'first_name' => $this->first_name,
			'last_name' => $this->last_name,
			'room_number' => $this->room_number,
			'phone_number' => $this->phone_number,
			'email_address' => $this->email_address,
			'image_url' => $this->image_url,
			'gender' => $this->gender,
			'information' => $this->information
		);
	}


	/**
	 * Creates the database-table.
	 *
	 * Creates the database-table to store employees in the WordPress-Database.
	 *
	 * @since 1.0.0
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @return void
	 */
	public static function create_db_table() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;
		$table = self::$db_table;

		$sql = "CREATE TABLE IF NOT EXISTS $table (
            id INT NOT NULL AUTO_INCREMENT,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            room_number VARCHAR(32),
            phone_number VARCHAR(32),
            email_address VARCHAR(255),
            image_url VARCHAR(255),
            gender VARCHAR(255),
            information VARCHAR(512),
            PRIMARY KEY (id)
        ) {$wpdb->get_charset_collate()};";
		dbDelta($sql);

		$column_exists = $wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM $table LIKE %s",'pin_hash'));

		if (empty($column_exists)) {
			$wpdb->query("ALTER TABLE $table ADD COLUMN pin_hash VARCHAR(255)");
		}
	}




	/**
	 * Returns all known employees.
	 *
	 * Returns all known employees as array.
	 *
	 * @since 1.0.0
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @return array|null An array with all known employees or null, if there are no employees in the database.
	 */
	public static function get_all(){
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;

		$sql = "SELECT * FROM " . self::$db_table . " ORDER BY last_name, first_name";
		$results = $wpdb->get_results($sql, ARRAY_A);

		if ($results) {
			$employees = [];
			foreach ($results as $employee) {
				$employees[] = self::from_associative_array($employee);
			}
			return $employees;
		}
		return null;
	}


	public static function get_by_department_id($id) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;

		$sql = "SELECT * FROM " . self::$db_table . " WHERE ID IN (SELECT employee_id FROM " . Employee_Department::$db_table . " WHERE department_id = '" . $id . "') ORDER BY last_name, first_name";
		$results = $wpdb->get_results($sql, ARRAY_A);
		$employees = [];
		if ($results) {
			foreach ($results as $employee) {
				$employees[] = self::from_associative_array($employee);
			}
			return $employees;
		}
		return null;
	}


	public static function get_by_occupation_id($id) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;

		$sql = "SELECT * FROM " . self::$db_table . " WHERE ID IN (SELECT employee_id FROM " . Employee_Occupation::$db_table . " WHERE occupation_id = '" . $id . "') ORDER BY last_name, first_name";
		$results = $wpdb->get_results($sql, ARRAY_A);
		$employees = [];
		if ($results) {
			foreach ($results as $employee) {
				$employees[] = self::from_associative_array($employee);
			}
			return $employees;
		}
		return null;
	}



    public static function ajax_get_all() {
		$employees = self::get_all();
	    $result = [];
	    if ($employees) {
	        foreach ($employees as $employee) {
		        $result[] = $employee->to_json();
	        }
	    }
	    wp_send_json_success($result);
    }



	/**
	 * Returns a single employee.
	 *
	 * Returns the employee with the given id, if it exists.
	 *
	 * @since 1.0.0
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @param $id int A numeric employee-id.
	 *
	 * @return Employee|null The employee with the given id or null, if an employee with that id doesn't exist.
	 */
	public static function get_by_id($id) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;

		$sql = "SELECT * FROM " . self::$db_table . " WHERE ID = '" . $id . "'";
		$employee = $wpdb->get_row($sql, ARRAY_A);
		if ( $employee ) {
			return self::from_associative_array($employee);
		} else {
			return null;
		}
	}



	public static function ajax_get_by_id($id) {
		if ($id == 0) {
			$employee = new self(
				0,
				'',
				'',
				'',
				'',
				'',
				BTZC_EL_BASE_URL . 'admin/images/icons/profile_placeholder.png',
				'undefined',
				''
			);
		} else {
			$employee = self::get_by_id($id);
		}

		wp_send_json_success($employee->to_json() ?: null);
	}



    private function to_json() {
        $temp = $this->to_associative_array();
        $temp['id'] = $this->id;
		$temp['departments'] = $this->departments->to_json();
		$temp['occupations'] = $this->occupations->to_json();
        return $temp;
    }



    public static function ajax_delete_employee($id) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;
		$wpdb->delete(self::$db_table, array('id' => $id));
		wp_send_json_success($id);
    }



    public static function ajax_persist_employee($post) {
		$employee = new self(
			sanitize_text_field($post['employee_id']),
			sanitize_text_field($post['first_name']),
			sanitize_text_field($post['last_name']),
			sanitize_text_field($post['room_number']),
			sanitize_text_field($post['phone_number']),
			sanitize_email($post['email_address']),
			sanitize_url($post['image_url']),
			sanitize_text_field($post['gender']),
			sanitize_text_field($post['information'])
		);
		$id = $employee->persist();

		$departmentList = sanitize_text_field($post['departments']);
		$departments = json_decode($departmentList, false);
		Employee_Department::clear_department_associations($id);
	    foreach ($departments as $department) {
			Employee_Department::register($id, $department);
		}

		$occupationList = sanitize_text_field($post['occupations']);
		$occupations = json_decode($occupationList, false);
		Employee_Occupation::clear_occupation_associations($id);
		foreach ($occupations as $occupation) {
			Employee_Occupation::register($id, $occupation);
		}

		$employee = self::get_by_id($id);
	    wp_send_json_success($employee->to_json());
    }


	public function generate_new_ssf_pin() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;

		$pin = rand(100000, 999999);
		$hashed_pin = password_hash($pin, PASSWORD_DEFAULT);
		$wpdb->update(self::$db_table, array('pin_hash' => $hashed_pin), array('id' => $this->id));
		return $pin;
	}
}
