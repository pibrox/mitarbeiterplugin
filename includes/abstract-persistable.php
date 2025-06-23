<?php
/**
 * Abstract base classes: Persistable
 *
 * An abstract class representing an entity that can be persisted to a database.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */

namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the WordPress-backend.
defined('ABSPATH') or die('Unauthorized!');


abstract class Persistable {
	/**
	 * Returns the numeric id of the persistable entity.
	 *
	 * @return int The numeric if of the persistable entity.
	 *
	 * @since 1.0.0
	 */
	abstract protected function get_id();

	/**
	 * Returns the name of the database-table where the entity is to be persisted.
	 *
	 * @return string The name of the database-table where the entity is to be persisted.
	 *
	 * @since 1.0.0
	 */
	abstract protected function get_db_table_name();

	/** Returns a representation of the entity as an associative array.
	 *
	 * @return array Associative-Array-Representation of the entity.
	 *
	 * @since 1.0.0
	 */
	abstract protected function to_associative_array();


	/**
	 * Persist the entity either by updating an existing table row or by creating a new one.
	 *
	 * @return int The id of the created or updated resource.
	 *
	 * @since 1.0.0
	 */
	public function persist() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;

		if ($this->get_id()   &&   $this->get_id() > 0) {
			$wpdb->update($this->get_db_table_name(), $this->to_associative_array(), array( 'id' => $this->get_id()));
			return $this->get_id();
		} else {
			$wpdb->insert($this->get_db_table_name(), $this->to_associative_array());
			return $wpdb->insert_id;
		}
	}

}
