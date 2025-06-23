<?php
/**
 * BTZ Customized - Employee List
 *
 * @package     BTZ\Customized
 * @subpackage  EmployeeList
 * @author      M. Großhäuser
 * @copyright   2025 M. Großhäuser
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: BTZ Customized - Employee List
 * Description: A search- and filter list for the employees of BTZ Cologne
 * Version:     2.1.0
 * Author:      M. Großhäuser
 * Author URI:  https://www.github.com/markus-grosshaeuser
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the Wordpress-Back-End.
defined('ABSPATH') or die('Unauthorized!');


/**
 * Base-URL of the plug-in to permit file-accesses throughout the other plug-in-files.
 *
 * @since 1.0.0
 */
define('BTZC_EL_BASE_URL', plugin_dir_url(__FILE__));

/**
 * Path to the includes-directory which contains all php-files of the plug-in.
 *
 * @since 1.0.0
 */
define('BTZC_EL_INCLUDES', plugin_dir_path(__FILE__) . 'includes/');






/**********************************************************
 *                 REQUIRED FILE INCLUDE                  *
 **********************************************************/

/**
 *  Contains the code for interacting with the Wordpress-Database.
 */
require_once ABSPATH . 'wp-admin/includes/upgrade.php';


/**
 * Abstract base-class for all persistable entities like employees, departments and occupations.
 */
require_once BTZC_EL_INCLUDES . 'abstract-persistable.php';

/**
 * Abstract base-class for the singleton design-pattern; used to guarantee all gui-objects are instantiated only once.
 */
require_once BTZC_EL_INCLUDES . 'abstract-singleton.php';

/**
 * Class for the instantiation of employees-representing objects.
 * Extends class Persistable.
 */
require_once BTZC_EL_INCLUDES . 'class-employee.php';

/**
 * Class for custom media (employee photo) handling.
 */
require_once BTZC_EL_INCLUDES . 'class-gallery.php';

/**
 * Class for the instantiation of department-representing objects.
 * Extends class Persistable
 */
require_once BTZC_EL_INCLUDES . 'class-department.php';

/**
 * Class for the instantiation of occupation-representing objects.
 * Extends class Persistable
 */
require_once BTZC_EL_INCLUDES . 'class-occupation.php';

/**
 * Class for handling the n-to-n-relation between employees and departments.
 */
require_once BTZC_EL_INCLUDES . 'class-employee-department.php';

/**
 * Class for handling the n-to-n-relation between employees and occupations.
 */
require_once BTZC_EL_INCLUDES . 'class-employee-occupation.php';

/**
 * GUI: Base administrative page for the Wordpress-Back-End; orchestrating the gui-tabs for employees, the gallery etc.
 * Extends class Singleton for there is supposed to be only one instance in existence at any time.
 */
require_once BTZC_EL_INCLUDES . 'class-admin-page.php';

/**
 * GUI: Employee-Tab-Page to work with the employee-data-set.
 * Extends class Singleton for there is supposed to be only one instance in existence at any time.
 */
require_once BTZC_EL_INCLUDES . 'class-employees-tab.php';

/**
 * GUI: Gallery-Tab-Page to work with the employee-photos.
 * Extends class Singleton for there is supposed to be only one instance in existence at any time.
 */
require_once BTZC_EL_INCLUDES . 'class-gallery-tab.php';

/**
 * GUI: Departments-Tab-Page to work with the department-data-set.
 * Extends class Singleton for there is supposed to be only one instance in existence at any time.
 */
require_once BTZC_EL_INCLUDES . 'class-departments-tab.php';

/**
 * GUI: Occupation-Tab-Page to work with the occupation-data-set.
 * Extends class Singleton for there is supposed to be only one instance in existence at any time.
 */
require_once BTZC_EL_INCLUDES . 'class-occupations-tab.php';

/**
 * Shortcode to include the employee-list anywhere on the site where a shortcode can be inserted.
 */
require_once BTZC_EL_INCLUDES . 'shortcode.php';

/**
 * Shortcode for a self-service-form
 */
require_once BTZC_EL_INCLUDES . 'self-service-form.php';






/**********************************************************
 *                     INITIALIZATION                     *
 **********************************************************/
/**
 * Creates database tables on plugin-activation.
 *
 * Creates a settings-table and calls the create_db_table-functions of all classes that need to
 * store data in the Wordpress-database.
 *
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'BTZ\Customized\EmployeeList\create_tables' );
function create_tables() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$sql = "CREATE TABLE IF NOT EXISTS btz_employee_list_settings (
    	setting VARCHAR(255) NOT NULL PRIMARY KEY ,
    	value VARCHAR(255) NOT NULL
    ) {$wpdb->get_charset_collate()};";
	dbDelta($sql);
	$sql = "INSERT IGNORE INTO btz_employee_list_settings (setting, value) VALUES ('company_name', 'Company Name');";
	$wpdb->query($sql);

    Employee::create_db_table();
    Department::create_db_table();
    Occupation::create_db_table();
    Employee_Department::create_db_table();
    Employee_Occupation::create_db_table();
}


/**
 * Enqueues stylesheets.
 *
 * Enqueues the common btz-customized-stylesheet and the specific plug-in-stylesheet for the Wordpress-backend-pages.
 *
 * @since 1.0.0
 */
add_action('admin_enqueue_scripts', 'BTZ\Customized\EmployeeList\enqueue_stylesheets' );
function enqueue_stylesheets() {
    wp_enqueue_style('btz_customized_employee_list_btz_customized_stylesheet', plugins_url('admin/css/btz-customized-v2.css' ,__FILE__));
	wp_enqueue_style('btz_customized_employee_list_employee_list_stylesheet', plugins_url('admin/css/employee-list.css' ,__FILE__));
}






/**********************************************************
 *                    BACKEND CREATION                    *
 **********************************************************/

/**
 * Creates Menu and Submenu.
 *
 * Checks whether the main-menu already exists or needs to be created. In any case, the submenu for the plugin is
 * added to the main menu.
 *
 * @since 1.0.0
 */
add_action('admin_menu' , 'BTZ\Customized\EmployeeList\wordpress_menu');
function wordpress_menu(){
	# check permissions to create menu-entries.
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }
    # Slug and title for the main-menu need to be the same for all plug-ins belonging to the btz-customized plug-in-suite.
    $main_menu_slug = 'btz_customized';
    $main_menu_title = 'BTZ Customized';

    $sub_menu_slug = 'btz_employee_list';
    $sub_menu_title = 'BTZ Mitarbeitende';

    $drop_submenu_page = false;

	# Conditional creation of the main menu
    if ( ! menu_exists($main_menu_title) ) {
        $drop_submenu_page = true;
    	add_menu_page(
        	$main_menu_title,
            $main_menu_title,
            'administrator',
            $main_menu_slug,
            'show_btz_customized',
            'dashicons-smiley',
            25
            );
    }

	# Add the submenu-page for the plug-in to the btz-customized main-menu.
   	add_submenu_page(
     	$main_menu_slug,
        $sub_menu_title,
        $sub_menu_title,
        'administrator',
        $sub_menu_slug,
        'BTZ\Customized\EmployeeList\show',
   	    10
        );

	# In case the main menu had to be created, its submenu page is removed for it is not supposed to hold any content.
   	if ($drop_submenu_page) {
        remove_submenu_page('btz_customized', 'btz_customized');
   	}



}


/**
 * Checks the existence of a menu.
 *
 * Checks whether the menu with the given title exists in the menu-bar.
 *
 * @param $title    string  The title of the menu.
 *
 * @return bool The menu with the given title either exists (true) or it doesn't (false).
 */
function menu_exists($title) {
	global $menu;
	foreach ($menu as $item) {
		if ( $item[0] === $title) {
			return true;
		}
	}
	return false;
}


/**
 * Loads the GUI.
 *
 * Callback for the add_submenu_page-function (see above).
 * Calls the show()-function from the single instance of the Admin-Page.
 *
 * @return void
 */
function show() {
    Admin_Page::get_instance()->show();
}






/**********************************************************
*                       AJAX-HOOKS                        *
**********************************************************/


/**
 * Ajax-hook for retrieving the system-language.
 */
add_action('wp_ajax_btzc_el_get_system_language', 'BTZ\Customized\EmployeeList\get_system_language');
function get_system_language() {
	wp_send_json_success(get_locale());
}

/**
 * Ajax-hook for retrieving the company-name from the database.
 */
add_action('wp_ajax_btzc_el_get_company_name', 'BTZ\Customized\EmployeeList\get_company_name');
function get_company_name() {
	global $wpdb;
	$sql = "SELECT value FROM btz_employee_list_settings WHERE setting = 'company_name';";
	$result = $wpdb->get_var($sql);
	wp_send_json_success($result);
}

/**
 * Ajax-hook for updating the company-name.
 */
add_action('wp_ajax_btzc_el_update_company_name', 'BTZ\Customized\EmployeeList\update_company_name');
function update_company_name() {
	global $wpdb;
	$new_company_name = sanitize_text_field($_POST['company_name']);
	$sql = "UPDATE btz_employee_list_settings SET value = '{$new_company_name}' WHERE setting = 'company_name';";
	$wpdb->query($sql);
}




/**
 * Ajax-hook for loading all employee-datasets.
 */
add_action('wp_ajax_btzc_el_employee_data', 'BTZ\Customized\EmployeeList\get_employee_data');
function get_employee_data() {
	Employee::ajax_get_all();
}

/**
 * Ajax-hook for loading a single employee-dataset.
 */
add_action('wp_ajax_btzc_el_employee_single_dataset', 'BTZ\Customized\EmployeeList\get_employee_single_dataset');
function get_employee_single_dataset() {
	Employee::ajax_get_by_id($_GET['employee_id']);
}

/**
 * Ajax-hook for deleting a single employee-dataset.
 */
add_action('wp_ajax_btzc_el_delete_employee', 'BTZ\Customized\EmployeeList\delete_employee');
function delete_employee() {
	Employee::ajax_delete_employee($_POST['employee_id']);
}

/**
 * Ajax-hook for persisting an employee-dataset.
 */
add_action('wp_ajax_btzc_el_persist_employee', 'BTZ\Customized\EmployeeList\persist_employee');
function persist_employee() {
	Employee::ajax_persist_employee($_POST);
}




/**
 * Ajax-hook for loading all department-datasets.
 */
add_action('wp_ajax_btzc_el_department_data', 'BTZ\Customized\EmployeeList\get_department_data');
function get_department_data() {
	Department::ajax_get_all();
}

/**
 * Ajax-hook for loading a single department-dataset.
 */
add_action('wp_ajax_btzc_el_department_single_dataset', 'BTZ\Customized\EmployeeList\get_department_single_dataset');
function get_department_single_dataset() {
	Department::ajax_get_by_id($_GET['department_id']);
}

/**
 * Ajax-hook for deleting a single department-dataset.
 */
add_action('wp_ajax_btzc_el_delete_department', 'BTZ\Customized\EmployeeList\delete_department');
function delete_department() {
	Department::ajax_delete_department($_POST['department_id']);
}

/**
 * Ajax-hook for persisting a department-dataset.
 */
add_action('wp_ajax_btzc_el_persist_department', 'BTZ\Customized\EmployeeList\persist_department');
function persist_department() {
	Department::ajax_persist_department($_POST);
}




/**
 * Ajax-hook for loading all occupation-datasets.
 */
add_action('wp_ajax_btzc_el_occupation_data', 'BTZ\Customized\EmployeeList\get_occupation_data');
function get_occupation_data() {
	Occupation::ajax_get_all();
}

/**
 * Ajax-hook for loading a single occupation-datasets.
 */
add_action('wp_ajax_btzc_el_occupation_single_dataset', 'BTZ\Customized\EmployeeList\get_occupation_single_dataset');
function get_occupation_single_dataset() {
	Occupation::ajax_get_by_id($_GET['occupation_id']);
}

/**
 * Ajax-hook for deleting a single occupation-dataset.
 */
add_action('wp_ajax_btzc_el_delete_occupation', 'BTZ\Customized\EmployeeList\delete_occupation');
function delete_occupation() {
	Occupation::ajax_delete_occupation($_POST['occupation_id']);
}

/**
 * Ajax-hook for persisting an occupation-datasets.
 */
add_action('wp_ajax_btzc_el_persist_occupation', 'BTZ\Customized\EmployeeList\persist_occupation');
function persist_occupation() {
	Occupation::ajax_persist_occupation($_POST);
}




/**
 * Ajax-hook for loading all urls of employee images.
 */
add_action('wp_ajax_btzc_el_get_employee_image_urls', 'BTZ\Customized\EmployeeList\get_employee_image_urls');
function get_employee_image_urls() {
	Gallery::ajax_get_image_urls();
}

/**
 * Ajax-hook for deleting an employee image.
 */
add_action('wp_ajax_btzc_el_delete_employee_image', 'BTZ\Customized\EmployeeList\delete_employee_image');
function delete_employee_image() {
	Gallery::ajax_delete_image($_POST['image_url']);
}

/**
 * Ajax-hook for uploading an employee images.
 */
add_action('wp_ajax_btzc_el_upload_employee_image', 'BTZ\Customized\EmployeeList\upload_employee_image');
function upload_employee_image() {
	Gallery::ajax_upload_images();
}

/**********************************************************
 *                   PIN VERSAND FUNKTION                *
 **********************************************************/

/**
 * AJAX-Handler für das Versenden einer PIN an eine E-Mail-Adresse mit Debugging.
 * 
 * @since 2.1.0
 */
add_action('wp_ajax_btzc_el_send_pin_to_email', 'BTZ\Customized\EmployeeList\send_pin_to_email');
function send_pin_to_email() {
    // Sicherheitsprüfung
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Keine Berechtigung']);
        return;
    }
    
    // E-Mail-Adresse validieren
    $email = sanitize_email($_POST['email']);
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Ungültige E-Mail-Adresse']);
        return;
    }
    
    // Debug: WordPress E-Mail-Konfiguration prüfen
    $admin_email = get_option('admin_email');
    $site_name = get_option('blogname');
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("E-Mail-Versand wird versucht:");
        error_log("Ziel-E-Mail: " . $email);
        error_log("WordPress Admin E-Mail: " . $admin_email);
        error_log("WordPress Site Name: " . $site_name);
    }
    
    // Mitarbeiter anhand E-Mail suchen
    $employees = Employee::get_all();
    $found_employee = null;
    
    foreach ($employees as $employee) {
        if (strtolower($employee->get_email_address()) === strtolower($email)) {
            $found_employee = $employee;
            break;
        }
    }
    
    if (!$found_employee) {
        wp_send_json_error(['message' => 'Kein Mitarbeiter mit dieser E-Mail-Adresse gefunden']);
        return;
    }
    
    // PIN generieren und versenden
    try {
        $pin = $found_employee->generate_new_ssf_pin();
        
        // E-Mail-Test durchführen
        require_once BTZC_EL_INCLUDES . 'self-service-form.php';
        
        // Zunächst Test-E-Mail senden
        $test_result = \BTZ\Customized\EmployeeList\test_email_functionality($email);
        
        if (!$test_result) {
            wp_send_json_error([
                'message' => 'E-Mail-System nicht konfiguriert. Bitte kontaktieren Sie den Administrator.',
                'debug_info' => 'WordPress E-Mail-Funktionalität ist nicht verfügbar'
            ]);
            return;
        }
        
        // PIN-E-Mail versenden
        $success = \BTZ\Customized\EmployeeList\send_pin_email(
            $found_employee->get_first_name(),
            $found_employee->get_last_name(),
            $found_employee->get_email_address(),
            $pin
        );
        
        if ($success) {
            wp_send_json_success([
                'message' => 'PIN erfolgreich an ' . $email . ' gesendet',
                'employee_name' => $found_employee->get_first_name() . ' ' . $found_employee->get_last_name(),
                'pin' => $pin // NUR für Debugging - in Produktion entfernen!
            ]);
        } else {
            // Detaillierte Fehleranalyse
            global $phpmailer;
            $error_details = '';
            if (isset($phpmailer) && $phpmailer->ErrorInfo) {
                $error_details = $phpmailer->ErrorInfo;
            }
            
            wp_send_json_error([
                'message' => 'E-Mail-Versand fehlgeschlagen',
                'debug_info' => $error_details,
                'suggestion' => 'Bitte prüfen Sie die WordPress E-Mail-Konfiguration oder installieren Sie ein SMTP-Plugin'
            ]);
        }
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Fehler beim Generieren der PIN: ' . $e->getMessage()]);
    }
}

/**
 * AJAX-Handler für E-Mail-Test-Funktionalität
 */
add_action('wp_ajax_btzc_el_test_email', 'BTZ\Customized\EmployeeList\test_email_ajax');
function test_email_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Keine Berechtigung']);
        return;
    }
    
    $email = sanitize_email($_POST['email']);
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Ungültige E-Mail-Adresse']);
        return;
    }
    
    require_once BTZC_EL_INCLUDES . 'self-service-form.php';
    $result = \BTZ\Customized\EmployeeList\test_email_functionality($email);
    
    if ($result) {
        wp_send_json_success(['message' => 'Test-E-Mail erfolgreich gesendet']);
    } else {
        wp_send_json_error(['message' => 'Test-E-Mail fehlgeschlagen']);
    }
}