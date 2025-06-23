<?php
/**
 * GUI: Gallery-Tab-Page
 *
 * Encapsulates the functionality for rendering the Gallery Tab page within
 * the WordPress back-end interface. This class manages the display of the
 * elements allowing users to upload, search, and manage gallery images.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 1.0.0
 */
namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the WordPress-backend.
defined('ABSPATH') or die('Unauthorized!');

class Gallery_Tab extends Singleton {
	/**
	 * Encapsulates a file upload input along with the display of a sub-menu containing buttons for
     * managing employee photos and a search input. Also includes a container for displaying
     * uploaded gallery images.
	 *
	 * @return void
     *
     * @since 1.0.0
	 */
    public function show() {
        ?>
        <input type="file" id="btzc-el-employee-photo-upload" name="btzc-el-employee-photo-upload[]" accept="image/png, image/jpeg" multiple hidden>

        <div class="btzc-v2-tab-sub-menu">
            <button class="btzc-v2-basic-button btzc-v2-delete-button" id="btzc-el-employee-photo-delete-selected" disabled></button>
            <button class="btzc-v2-basic-button btzc-v2-standard-button" id="btzc-el-employee-photo-add-new"></button>
            <button class="btzc-v2-basic-button btzc-v2-standard-button" id="btzc-el-employee-photo-select-all"></button>
            <label for="btzc-el-employee-photo-search"></label>
            <input type="text" class="btzc-v2-basic-text-field btzc-v2-standard-text-field" id="btzc-el-employee-photo-search">
        </div>
        <div class="btzc-v2-tab-body-below-sub-menu">
            <div id="btzc-el-gallery-image-container">

            </div>
        </div>

        <?php 
    }

}