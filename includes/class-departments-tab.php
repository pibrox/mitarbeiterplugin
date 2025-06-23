<?php
/**
 * Represents the Departments Tab section as part of the WordPress Back-End-GUI.
 *
 * This class is utilized to render and manage the departments tab functionality
 * within the admin area of the WordPress interface.
 *
 * Extends the Singleton design pattern to ensure a single instance.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */

namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the WordPress-backend.
defined('ABSPATH') or die('Unauthorized!');


class Departments_Tab extends Singleton {
	/**
	 * Displays the content for the department tab section.
	 *
	 * @return void This method outputs HTML content directly.
     *
     * @since 1.0.0
	 */
    public function show() {
        ?>
            <div id="btzc-el-department-tab-base-container">
                <div class="btzc-v2-tile-table-container">
                    <div class="btzc-v2-tile-table" id="btzc-el-department-tile-table"></div>
                </div>
            </div>
		<?php 
    }
}
