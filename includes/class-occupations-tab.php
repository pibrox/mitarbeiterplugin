<?php
/**
 * Class Occupations_Tab
 *
 * Represents the occupations-tab-page functionality for the WordPress back-end.
 *
 * This class is responsible for rendering the occupations tab as part of the WordPress administrative GUI.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */

namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the Wordpress-backend.
defined('ABSPATH') or die('Unauthorized!');


class Occupations_Tab extends Singleton {
	/**
	 * Renders the base container and tile table for occupation list display.
	 *
	 * @return void This method outputs HTML directly and does not return a value.
     *
     * @since 1.0.0
	 */
    public function show() {
        ?>
        	<div id="btzc-el-occupation-list-base-container">
                <div class="btzc-v2-tile-table" id="btzc-el-occupation-tile-table">

                </div>
            </div>
        <?php 
    }
}