<?php
/**
 * Abstract class implementing the Singleton design pattern.
 *
 * This class serves as a base for all classes that require only a single instance during the application's lifecycle.
 * By extending this class, derived classes ensure adherence to the singleton pattern, preventing multiple instances
 * from being created. The pattern is enforced through private final methods and managed via a shared instances-registry.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */

namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the Wordpress-backend.
defined('ABSPATH') or die('Unauthorized!');


abstract class Singleton {
	/**
	 * References to singleton-classes.
	 *
	 * @var array $instances An associative array for bookkeeping of zero or one reference(s) to instances of
	 *                          those classes extending this abstract class.
	 *
	 * @since 1.0.0
	 */
	private static $instances = [];


	/**
	 * Returns an instance of the class.
	 *
	 * Checks if an instance of the class is already created. If not, the class is instantiated (lazy
	 * instantiation). In any case, the singleton instance of the class is returned.
	 *
	 * @return self An instance (the only instance in existence) of the class.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance()
	{
		$class = get_called_class();
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class();
		}

		return self::$instances[$class];
	}


	/**
	 * Private final constructor.
	 *
	 * The constructor is private and final to prevent the creation of multiple instances.
	 * Because all extending classes don't need any initialisation-code to be executed, the constructor is empty.
	 *
	 * @since 1.0.0
	 */
	private final function __construct() { }

	/**
	 * Private final clone-function.
	 *
	 * The clone function is private and final to prevent the creation of multiple instances by cloning the
	 * one instance intentionally created.
	 *
	 * @since 1.0.0
	 *
	 * @return void Since no additional instances shall be created, this function return nothing.
	 */
	private final function __clone() { }

	/**
	 * Private final wakeup-function.
	 *
	 * The wakeup function is private and final to prevent the reconstruction of the objects resources.
	 *
	 * @since 1.0.0
	 *
	 * @return void The function returns nothing.
	 */
	private final function __wakeup() { }

	/**
	 * Shows the gui.
	 *
	 * In the context of this plug-in the singleton design pattern is exclusively used for those classes creating
	 * the web-pages belonging to the WordPress-Back-End-GUI. Therefor every extending class has to have a
	 * function that displays its particular part of the gui.
	 *
	 * @return void The function returns nothing.
	 */
	abstract public function show();

}
