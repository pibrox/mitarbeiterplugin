<?php
/**
 * Data-Entities: Occupations
 *
 * This file contains the Occupation-Class
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the Wordpress-backend.
defined('ABSPATH') or die('Unauthorized!');


/**
 * Data-Entity representing an occupation.
 *
 * The Occupation-class consists of static methods for interaction with the database and
 * constructs objects which capsule all information regarding an occupation.
 *
 * @since 1.0.0
 *
 * @see Persistable
 */
class Occupation extends Persistable {
	/**
	 * The name of the database-table.
	 *
	 * @since 1.0.0
	 * @var string The name of the database-table where all information regarding an occupation are stored.
	 */
	public static $db_table = "btz_employee_list_occupations";

	/**
	 * The occupation-id.
	 *
	 * @since 1.0.0
	 * @var int The id-property to store the database-id of an occupation.
	 */
	private $id;

	/**
	 * The occupation-name.
	 *
	 * @since 1.0.0
	 * @var int The occupation-property to store the human-readable identifier of an occupation.
	 */
	private $occupation;

	/**
	 * The male form of the occupation.
	 *
	 * @since 1.0.0
	 * @var int The occupation-male-form-property to store the male-gendered identifier of an occupation.
	 */
	private $occupation_male_form;

	/**
	 * The female form of the occupation.
	 *
	 * @since 1.0.0
	 * @var int The occupation-female-form-property to store the female-gendered identifier of an occupation.
	 */
	private $occupation_female_form;

	/**
	 * The diverse form of the occupation.
	 *
	 * @since 1.0.0
	 * @var int The occupation-diverse-form-property to store the diverse-gendered identifier of an occupation.
	 */
	private $occupation_diverse_form;


	/**
	 * A getter-method for the id-property.
	 *
	 * @since 1.0.0
	 *
	 * @return int The id-property of the occupation.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * A getter-method for the occupation-property.
	 *
	 * @since 1.0.0
	 *
	 * @return int The occupation-property of the occupation.
	 */
	public function get_occupation() {
		return $this->occupation;
	}

	/**
	 * A getter-method for the occupation-male-form-property.
	 *
	 * @since 1.0.0
	 *
	 * @return int The occupation-male-form-property of the occupation.
	 */
	public function get_male_form() {
		return ($this->occupation_male_form ?: $this->occupation);
	}

	/**
	 * A getter-method for the occupation-female-form-property.
	 *
	 * @since 1.0.0
	 *
	 * @return int The occupation-female-form-property of the occupation.
	 */
	public function get_female_form() {
		return ($this->occupation_female_form ?: $this->occupation);
	}

	/**
	 * A getter-method for the occupation-diverse-form-property.
	 *
	 * @since 1.0.0
	 *
	 * @return int The occupation-diverse-form-property of the occupation.
	 */
	public function get_diverse_form() {
		return ($this->occupation_diverse_form ?: $this->occupation);
	}


	/**
	 * A getter-method for the database-table-name.
	 *
	 * Overrode getter-method from the Persistable-class to abstract the database-access.
	 *
	 * @since 1.0.0
	 * @see Persistable
	 *
	 * @return string The name of the database-table used to store the occupation-data.
	 */
	protected function get_db_table_name() {
		return self::$db_table;
	}


	/**
	 * Primary constructor.
	 *
	 * The primary constructor to create new occupation-objects.
	 *
	 * @since 1.0.0
	 *
	 * @param $id int The database-id of the occupation.
	 * @param $occupation string The human-readable identifier of the occupation.
	 * @param $male_form string The male-gendered form of the occupation.
	 * @param $female_form string The female-gendered form of the occupation.
	 * @param $diverse_form string The diverse-gendered form of the occupation.
	 */
	public function __construct($id, $occupation, $male_form, $female_form, $diverse_form) {
		$this->id = $id;
		$this->occupation = $occupation;
		$this->occupation_male_form = $male_form;
		$this->occupation_female_form = $female_form;
		$this->occupation_diverse_form = $diverse_form;
	}


	/**
	 * Creates a new occupation-object from database-query-results.
	 *
	 * Helper-Method to create a new instance of this class right from the results of a database-query.
	 *
	 * @param $array array The associative array returned from a database-query ('column-name' => 'column-value').
	 *
	 * @return self A new instance of class Occupation based on the query-result.
	 *@since 1.0.0
	 *
	 */
	public static function from_associative_array($array) {
		return new self(
			$array['id'],
			$array['occupation'],
			$array['male_form'],
			$array['female_form'],
			$array['diverse_form']
		);
	}


	/**
	 * Creates an associative-array representation.
	 *
	 * Creates an associative-array representation of the occupation-object.
	 * Overrode method from the abstract Persistable-class.
	 *
	 * @since 1.0.0
	 *
	 * @see Persistable
	 *
	 * @return array The associative-array representing the occupation-object.
	 */
	protected function to_associative_array() {
		return array(
			'occupation' => $this->occupation,
			'male_form' => $this->occupation_male_form,
			'female_form' => $this->occupation_female_form,
			'diverse_form' => $this->occupation_diverse_form
		);
	}


	/**
	 * Creates the database-table.
	 *
	 * Creates the database-table to store occupations in the WordPress-Database.
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

		$sql = "CREATE TABLE IF NOT EXISTS $table (
    		id INT NOT NULL AUTO_INCREMENT,
    		occupation VARCHAR(255) NOT NULL UNIQUE,
            male_form VARCHAR(255),
            female_form VARCHAR(255),
            diverse_form VARCHAR(255),
            PRIMARY KEY (id)
        ) {$wpdb->get_charset_collate()};";
		dbDelta($sql);
	}


	/**
	 * Returns all known occupations.
	 *
	 * Returns all known occupations as array.
	 *
	 * @since 1.0.0
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @return array|null An array with all known occupations; empty, if there are no occupations in the database.
	 */
	public static function get_all() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$sql = "SELECT * FROM " . self::$db_table . " ORDER BY occupation";
		$results = $wpdb->get_results($sql, ARRAY_A);
		$objects = [];
		if ($results) {
			foreach($results as $result) {
				$objects[] = self::from_associative_array($result);
			}
		}
		return $objects;
	}


    public static function ajax_get_all() {
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	    global $wpdb;

	    $sql = "SELECT * FROM " . self::$db_table . " ORDER BY occupation";
	    $results = $wpdb->get_results($sql, ARRAY_A);
	    wp_send_json_success($results ?: []);
    }

	/**
	 * Returns a single occupation.
	 *
	 * Returns the occupation with the given id, if it exists.
	 *
	 * @since 1.0.0
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @param $id int A numeric occupation-id.
	 *
	 * @return Occupation|null The occupation with the given id or null, if an occupation with that id doesn't exist.
	 */
	public static function get_by_id($id) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$sql = "SELECT * FROM " . self::$db_table . " WHERE id = " . $id;
		$result = $wpdb->get_row($sql, ARRAY_A);
		if ($result) {
			return self::from_associative_array($result);
		}
		return null;
	}


    public static function ajax_get_by_id($id) {
        $occupation = self::get_by_id(sanitize_text_field($id));
		$temp = $occupation->to_associative_array();
		$temp['id'] = $id;
        wp_send_json_success($temp);
    }




	/**
	 * Deletes an occupation.
	 *
	 * Deletes the occupation with the given id.
	 *
	 * @since 1.0.0
	 *
	 * @see wp-admin/includes/upgrade.php
	 * @global object $wpdb Object for interaction with the WordPress-Database.
	 *
	 * @param $id int The numeric id of the occupation.
	 *
	 * @return void
	 */
	public static function ajax_delete_occupation($id) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;
		$wpdb->delete(self::$db_table, array('id' => $id));
        wp_send_json_success($id);
	}

    public static function ajax_persist_occupation($post) {
        $occupation = new self(
                sanitize_text_field($post['occupation_id']),
                sanitize_text_field($post['occupation']),
                sanitize_text_field($post['occupation_male_form']),
                sanitize_text_field($post['occupation_female_form']),
                sanitize_text_field($post['occupation_diverse_form'])
        );
        $id = $occupation->persist();
        $new_occupation = self::get_by_id($id);
        $temp = $new_occupation->to_associative_array();
        $temp['id'] = $id;
        wp_send_json_success($temp);
    }
}