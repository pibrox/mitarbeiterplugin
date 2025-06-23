<?php
/**
 * Plugin uninstall code
 *
 * Deletes the employee-images and the plugins database tables on plugin deletion.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 *
 * @since 1.0.0
 */
namespace BTZ\Customized\EmployeeList;

#Drop database tables ONLY when called during the uninstall-procedure.
defined('WP_UNINSTALL_PLUGIN') or die('Unauthorized!');

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS btz_employee_list_employee_departments");
$wpdb->query("DROP TABLE IF EXISTS btz_employee_list_employee_occupations");
$wpdb->query("DROP TABLE IF EXISTS btz_employee_list_departments");
$wpdb->query("DROP TABLE IF EXISTS btz_employee_list_occupations");
$wpdb->query("DROP TABLE IF EXISTS btz_employee_list_employees");
$wpdb->query("DROP TABLE IF EXISTS btz_employee_list_settings");


/**
 * Recursively deletes a directory and its contents.
 *
 * This function removes all files and subdirectories within the specified directory
 * and then deletes the directory itself.
 *
 * @param string $dir_path The path of the directory to be deleted.
 *
 * @return bool Returns true if the directory was successfully deleted; otherwise, false.
 *
 * @since 2.0.0
 */
function delete_directory($dir_path) {
    if (!is_dir($dir_path)) {
        return false;
    }

    $files = array_diff(scandir($dir_path), ['.', '..']);

    foreach ($files as $file) {
        $file_path = $dir_path . DIRECTORY_SEPARATOR . $file;

        if (is_dir($file_path)) {
            delete_directory($file_path);
        } else {
            unlink($file_path);
        }
    }

    return rmdir($dir_path);
}


$upload_dir = wp_upload_dir();
$employee_images_dir = trailingslashit($upload_dir['basedir']) . 'employee_images';
if (is_dir($employee_images_dir)) {
    delete_directory($employee_images_dir);
}