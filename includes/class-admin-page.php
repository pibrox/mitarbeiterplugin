<?php
/**
 * GUI: Admin-Page
 *
 * The Admin_Page class is the base class of the back-end GUI. This class manages all GUI tabs,
 * including their navigation and display. It includes various scripts and creates key components
 * of the admin interface such as the tab-based navigation system.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */

namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the Wordpress-backend.
defined('ABSPATH') or die('Unauthorized!');


class Admin_Page extends Singleton {
	/**
	 * The default navigation target. Initialized to the employee-tab-page and used by the admin-page-scripts.mjs-file.
	 *
	 * @var string The navigation-target; one of btzc-el-employee-tab, btzc-el-gallery-tab, btzc-el-departments-tab
	 *              or btzc-el-occupations-tab.
     *
     * @since 1.0.0
	 */
	private $default_tab = 'btzc-el-employee-tab';


	/**
	 * Displays the main user interface for the BTZ Mitarbeitenden-Verzeichnis (Employee Directory).
	 * This method enqueues necessary scripts, styles, and sets up the HTML structure for the
	 * tabs and their respective content. Additionally, it handles navigation to the default tab
	 * and script initialization for specific tab functionality.
	 *
	 * @return void
     *
     * @since 1.0.0
	 */
    public function show() {
        ?>
            <div class="wrap">
                <div class="btzc-v2-page-header">
                    <h1 class="btzc-admin-page-title-row"><strong id="btzc-admin-page-company-name" contenteditable="true"></strong><span id="btzc-admin-page-title"></span></h1>
                    <div class="btzc-v2-tab-bar">
                        <div class="btzc-v2-tab-bar-links-container">
                            <button class="btzc-v2-tab-link" id="btzc-el-employee-tab"></button>
                            <button class="btzc-v2-tab-link" id="btzc-el-gallery-tab"></button>
                            <button class="btzc-v2-tab-link" id="btzc-el-departments-tab"></button>
                            <button class="btzc-v2-tab-link" id="btzc-el-occupations-tab"></button>
                        </div>
                        <div class="btzc-v2-tab-bar-right-outer-info-container">
                            <button id="btzc-el-button-shortcode-to-clipboard" class="btzc-v2-image-button-clipboard"></button>
                        </div>
                    </div>
                </div>
                <div class="btzc-v2-tab-container">
                    <div class="btzc-v2-tab" id="btzc-el-employees"> <?php Employee_Tab::get_instance()->show();?> </div>
                    <div class="btzc-v2-tab" id="btzc-el-gallery"> <?php Gallery_Tab::get_instance()->show();?> </div>
                    <div class="btzc-v2-tab" id="btzc-el-departments"> <?php Departments_Tab::get_instance()->show();?> </div>
                    <div class="btzc-v2-tab" id="btzc-el-occupations"> <?php Occupations_Tab::get_instance()->show();?> </div>
                </div>
            </div>
        <?php

	    # Enqueue scripts to handle media and to use jQuery-dialogs.
	    wp_enqueue_media();
	    wp_enqueue_script( 'jquery-ui-dialog' );
	    wp_enqueue_style( 'wp-jquery-ui-dialog' );

        # Enqueue the jQuery-scripts for the overall admin-page.
        wp_enqueue_script_module('btz_customized_employee_list_admin_page', BTZC_EL_BASE_URL . 'admin/js/admin-page-scripts.mjs');

        # Enqueue the jQuery-scripts for the tab-pages.
        wp_enqueue_script_module('btz_employee_list_employee_tab',   BTZC_EL_BASE_URL . 'admin/js/employee-tab-scripts.mjs');
	    wp_enqueue_script_module('btz_employee_list_gallery_tab',    BTZC_EL_BASE_URL . 'admin/js/gallery-tab-scripts.mjs');
	    wp_enqueue_script_module('btz_employee_list_department_tab', BTZC_EL_BASE_URL . 'admin/js/department-tab-scripts.mjs');
	    wp_enqueue_script_module('btz_employee_list_occupation_tab', BTZC_EL_BASE_URL . 'admin/js/occupation-tab-scripts.mjs');
    }

}
