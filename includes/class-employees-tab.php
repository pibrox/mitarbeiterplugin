<?php
/**
 * Represents the Employees Tab section as part of the WordPress Back-End-GUI.
 *
 * This class is utilized to render and manage the employees tab functionality
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


class Employee_Tab extends Singleton {
	/**
	 * Displays the content for the department tab section.
	 *
	 * @return void This method outputs HTML content directly.
	 *
	 * @since 1.0.0
	 */
    public function show(){
	    ?>
            <div id="btzc-el-employee-tab-base-container">
                <div id="btzc-el-employee-tab-alphabetic-index-container-left" class="btzc-el-employee-tab-alphabetic-index-container"></div>
                <div id="btzc-el-employee-tab-alphabetic-index-container-right" class="btzc-el-employee-tab-alphabetic-index-container"></div>
                <div id="btzc-el-employee-tab-inner-container">
                    <div id="btzc-el-employee-table-filters-container">
                        <label for="btzc-el-employee-tab-department-selection"></label>
                        <select id="btzc-el-employee-tab-department-selection"></select>
                        <label for="btzc-el-employee-tab-occupation-selection"></label>
                        <select id="btzc-el-employee-tab-occupation-selection"></select>
                        <label for="btzc-el-employee-tab-search-field"></label>
                        <input type="text" class="btzc-v2-basic-text-field" id="btzc-el-employee-tab-search-field">
                        
                        <!-- PIN-VERSAND FUNKTION -->
                        <div id="btzc-el-pin-send-container" style="margin-left: 20px; display: inline-flex; align-items: center; gap: 10px;">
                            <label for="btzc-el-pin-send-email">PIN versenden an:</label>
                            <input type="email" class="btzc-v2-basic-text-field" id="btzc-el-pin-send-email" placeholder="mitarbeiter@email.com" style="width: 200px;">
                            <button type="button" class="btzc-v2-basic-button btzc-v2-standard-button" id="btzc-el-pin-send-button">PIN senden</button>
                            <button type="button" class="btzc-v2-basic-button" id="btzc-el-email-test-button" style="background: #ffa500;">E-Mail testen</button>
                        </div>
                    </div>
                    <div class="btzc-v2-tile-table-container">
                        <div class="btzc-v2-tile-table" id="btzc-el-employee-tile-table"></div>
                    </div>
                </div>
            </div>
	    <?php
    }
}