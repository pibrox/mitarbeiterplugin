<?php
/**
 * Public-Display: Shortcode
 *
 * This file adds the shortcode to insert an employee-list anywhere where a shortcode can be placed.
 *
 * @package BTZ\Customized
 * @subpackages EmployeeList
 * @since 1.0.0
 */
namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the Wordpress-backend.
defined('ABSPATH') or die('Unauthorized!');


function translate($key) {
	$locale = get_locale() ?: 'en_US';
	$frontEndBaseDir = str_replace('includes', 'language', __DIR__);
	$lang_file = $frontEndBaseDir . "/$locale.json";

	if (!file_exists($lang_file)) {
		$lang_file = $lang_file . "/en_US.json";
	}
	$translations = json_decode(file_get_contents($lang_file), true);
	return isset($translations[$key]) ? $translations[$key] : $key;
}


/**
 * Registers the shortcode for the plugin.
 *
 * @since 1.0.0
 */
add_shortcode( 'employee_list', 'BTZ\Customized\EmployeeList\shortcode' );

/**
 * Renders the employee list for the frontend.
 *
 * This function generates the HTML for the employee list, including department and occupation
 * selection elements, a search field, and tiles for each employee. It enqueues the necessary
 * stylesheets and scripts if the plugin's base URL is defined.
 *
 * @return string The HTML output for the employee list.
 *
 * @since 1.0.0
 */
function shortcode($attributes) {
	if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		return 'Employee-List won\'t render correctly in Elementor Edit Mode. Please switch to Preview Mode to see the Plugin in Action.';
	}

	if (defined('BTZC_EL_BASE_URL')) {
		wp_enqueue_style('btz_customized_employee_list_frontend_stylesheet', BTZC_EL_BASE_URL . 'public/css/public.css');
		wp_enqueue_script('btz_employee_list_jquery', BTZC_EL_BASE_URL . 'public/js/jquery-3.7.1.min.js');
		wp_enqueue_script_module('btz_customized_employee_list_frontend_javascript', BTZC_EL_BASE_URL . 'public/js/employee-list.js');
	}

	$attributes = shortcode_atts(array('department' => 0, 'occupation' => 0), $attributes, 'employee_list');
	$department_id = intval($attributes['department']);
	$occupation_id = intval($attributes['occupation']);

	$departments = $department_id == 0 ? Department::get_all() : Department::get_by_id($department_id);
	$occupations = $occupation_id == 0 ? Occupation::get_all() : Occupation::get_by_id($occupation_id);

	if ($department_id > 0) {
		$employees = Employee::get_by_department_id( $department_id );
	} else if ($occupation_id > 0) {
		$employees = Employee::get_by_occupation_id( $occupation_id );
	} else {
		$employees = Employee::get_all();
	}

	$output  = '<div id="btzc-el-frontend-container">';
	$output .= '  <div id="btzc-el-frontend-employee-list">';
	$output .= '    <div id="btzc-el-frontend-employee-list-head">';
	$output .= $department_id == 0 ? get_department_selection_element($departments) : get_department_selection_element($departments, false);
	$output .= $occupation_id == 0 ? get_occupation_selection_element($occupations) : get_occupation_selection_element($occupations, false);

	if ($department_id == 0 && $occupation_id == 0) {
		$output .= '      <input id="btzc-el-frontend-search-field" class="btzc-el-frontend-search-field" type="text" placeholder=' . translate('search_placeholder'). '>';
	} else {
		$output .= '      <input id="btzc-el-frontend-search-field" class="btzc-el-frontend-search-field-reduced" type="text" placeholder=' . translate('search_placeholder'). '>';
	}
	$output .= '    </div>';
	$output .= '    <div id="btzc-el-frontend-employee-list-body">';
	foreach ($employees as $employee) {
		$output .=  get_employee_tile($employee);
	}
	$output .= '    </div>';
	$output .= '  </div>';
	$output .= '</div>';
	return $output;
}


/**
 * Generates an HTML select element for department selection.
 *
 * @param array $departments An array of department objects, where each object provides methods to retrieve its ID and name.
 *
 * @return string The HTML markup for the department selection dropdown.
 *
 * @since 1.0.0
 */
function get_department_selection_element($departments, $visible = true) {
	if ($visible) {
		$output  = '<select id="btzc-el-frontend-department-select">';
	} else {
		$output  = '<select id="btzc-el-frontend-department-select" hidden>';
	}
	$output .= '  <option value="0">' . translate('all_departments') . '</option>';
	foreach ($departments as $department) {
		$output .= '  <option value="' . $department->get_id() . '">' . $department->get_department() . '</option>';
	}
	$output .= '</select>';
	return $output;
}


/**
 * Generates an HTML select element for occupation selection.
 *
 * @param array $occupations An array of occupation objects, where each object provides methods to retrieve its ID and name.
 *
 * @return string The HTML markup for the occupation selection dropdown.
 *
 * @since 1.0.0
 */
function get_occupation_selection_element($occupations, $visible = true) {
	if ($visible) {
		$output  = '<select id="btzc-el-frontend-occupation-select">';
	} else {
		$output  = '<select id="btzc-el-frontend-occupation-select" hidden>';
	}
	$output .= '  <option value="0">' . translate('all_occupations') . '</option>';
	foreach ($occupations as $occupation) {
		$output .= '  <option value="' . $occupation->get_id() . '">' . $occupation->get_occupation() . '</option>';
	}
	$output .= '</select>';
	return $output;
}


/**
 * Generates an HTML tile for displaying information about an employee.
 *
 * @param object $employee An employee object containing methods to retrieve various details such as name,
 *                          departments, occupations, image URL, phone number, room number, email address,
 *                          and additional information.
 *
 * @return string The HTML markup for the employee tile, including sections for the employee's image, name,
 *                          departments, occupations, contact details, and optional additional information.
 *
 * @since 1.0.0
 */
function get_employee_tile($employee) {
	$first_name = $employee->get_first_name() === translate('[FIRST NAME]') ? '' : $employee->get_first_name();
	$last_name = $employee->get_last_name() === translate('LAST NAME') ? '' : $employee->get_last_name();
	$department_list = $employee->get_departments()->to_spaced_list();
	$occupation_list = $employee->get_occupations()->to_spaced_list();
	$image_url = $employee->get_image_url();
	$phone_number = $employee->get_phone_number() === translate('[PHONE]') ? '' : $employee->get_phone_number() . '';
	$room_number = $employee->get_room_number() === translate('[ROOM]') ? '' : $employee->get_room_number() . '';
	$email_address = $employee->get_email_address() === translate('EMAIL@EXAMPLE.COM') ? '' : $employee->get_email_address();
	$additional_information = $employee->get_information() === translate('[ADDITIONAL INFORMATION]') || strtolower($employee->get_information()) === 'null'? '' : $employee->get_information();

	$output  = '<div class="btzc-el-frontend-employee-tile" data-firstname="' . $first_name . '" data-lastname="' . $last_name . '" data-departments="' . $department_list . '" data-occupations="' . $occupation_list . '">';
	$output .= '  <div class="btz-el-frontend-employee-tile-header">';
	$output .= '  </div>';
	$output .= '  <div class="btzc-el-frontend-employee-tile-body">';
	$output .= '    <div class="btzc-el-frontend-employee-tile-body-image-container">';
	$output .= '      <img src="' . $image_url . '">';
	$output .= '    </div>';
	$output .= '    <div class="btzc-el-frontend-employee-tile-body-data-container">';
	$output .= '      <div class="btzc-el-frontend-employee-tile-name-row">';
	$output .= '        <p>' . $first_name . '<strong> ' . $last_name . '</strong></p>';
	$output .= '      </div>';
	$output .= '      <div class="btzc-el-frontend-employee-tile-departments-section">';
	$output .=          get_employee_departments($employee);
	$output .= '      </div>';
	$output .= '      <div class="btzc-el-frontend-employee-tile-occupations-section">';
	$output .=          get_employee_occupations($employee);
	$output .= '      </div>';
	$output .= '      <div class="btzc-el-frontend-employee-tile-phone-and-room-row">';
	$output .= '        <p class="btzc-el-employee-frontend-tile-phone">Tel.: ' . $phone_number . '</p>';
	$output .= '        <p class="btzc-el-employee-frontend-tile-room">Raum: ' . $room_number . '</p>';
	$output .= '      </div>';
	$output .= '      <div class="btzc-el-frontend-employee-tile-email-row">';
	if ($email_address === '') {
		$output .= translate('no_email');
	} else {
		$output .= '<a href="mailto:' . $email_address . '">' . $email_address . '</a>';
	}
	$output .= '      </div>';
	$output .= '    </div>';
	$output .= '  </div>';
	if ($additional_information) {
		$output .= '  <div class="btzc-el-frontend-employee-tile-footer">';
		$output .= '    <div class="btzc-el-frontend-additional-information">' . $additional_information . '</div>';
		$output .= '  </div>';
	}
	$output .= '</div>';
	return $output;
}


/**
 * Retrieves and formats the departments associated with a given employee.
 *
 * @param object $employee An employee object that provides access to its associated departments.
 *
 * @return string The formatted output of the employee's departments as a string of HTML paragraphs.
 *
 * @since 1.0.0
 */
function get_employee_departments($employee) {
	$output = '';
	foreach ($employee->get_departments()->get_departments() as $department) {
		$output .= '<p>' . $department->get_department() . '</p>';
	}
	return $output;
}


/**
 * Generates HTML markup displaying the occupations of an employee, formatted based on their gender.
 *
 * @param object $employee An employee object that provides methods to retrieve the employee's gender
 *                         and their associated occupations.
 *
 * @return string The HTML markup containing the list of occupations with gender-specific formatting.
 *
 * @since 1.0.0
 */
function get_employee_occupations($employee) {
	$employee_gender = $employee->get_gender();
	$output = ' ';
	foreach ($employee->get_occupations()->get_occupations() as $occupation) {
		switch($employee_gender) {
			case 'male':
				$output .= '<p>' . $occupation->get_male_form() . '</p>';
				break;
			case 'female':
				$output .= '<p>' . $occupation->get_female_form() . '</p>';
				break;
			case 'diverse':
				$output .= '<p>' . $occupation->get_diverse_form() . '</p>';
				break;
			default:
				$output .= '<p>' . $occupation->get_occupation() . '</p>';
				break;
		}
	}
	return $output;
}