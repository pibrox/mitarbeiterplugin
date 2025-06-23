<?php

namespace BTZ\Customized\EmployeeList;

defined("ABSPATH") or die ("Unauthorized!");

add_action('btzc_el_reset_pin', 'BTZ\Customized\EmployeeList\handle_pin_reset_request');
add_action('btzc_el_persist_data', 'BTZ\Customized\EmployeeList\handle_self_service_form_submit');

add_shortcode("employee_list_ssf", 'BTZ\Customized\EmployeeList\self_service_form');

/**
 * Generates and processes the Employee-Self-Service form based on input attributes and user interactions.
 *
 * @param array $attributes An array of attributes passed to the shortcode, including 'init_id'.
 *
 * @return string The HTML content of the self-service form or a related message, determined by the request context.
 */
function self_service_form($attributes) {
	if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		return 'Employee-List Self-Service-Form won\'t render correctly in Elementor Edit Mode. Please switch to Preview Mode.';
	}

    defined ('BTZC_EL_BASE_URL') or die ('');
    wp_enqueue_style('btz_customized_employee_list_frontend_stylesheet', BTZC_EL_BASE_URL . 'public/css/public.css');
    wp_enqueue_script('btz_employee_list_jquery', BTZC_EL_BASE_URL . 'public/js/jquery-3.7.1.min.js');
	wp_enqueue_script('btz_customized_employee_list_ssf_sign_on', BTZC_EL_BASE_URL . 'public/js/employee-ssf.js');


    $attributes = shortcode_atts(array('init_id' => 1234), $attributes, 'employee_list');

    if ($_GET['init_id'] == $attributes['init_id']  &&  !isset($_POST['employee_id'])) {
        return get_self_service_form_html();
    }

    if (isset($_POST['btzc-el-edit-data-username']) && isset($_POST['btzc-el-edit-data-pin'])) {
	    return handle_log_in_attempt();
    }

	if (isset($_POST['btzc-el-reset-pin-username'])) {
		handle_pin_reset_request();
	}

	if (isset($_POST['employee_id'])) {
		handle_self_service_form_submit();
		unset($_POST['employee_id']);
	}

    return get_edit_data_log_in_html();
}


/**
 * Generates the HTML markup for the Employee-Self-Service form.
 *
 * @param object|null $employee An optional employee object containing pre-filled data for the form fields.
 * If null, the form will be generated with default or empty values.
 *
 * @return string The HTML content for the self-service form, including input fields, dropdowns, and controls.
 */
function get_self_service_form_html($employee = null) {
    $html  = '<div id="btzc-el-self-service-form-container">';
    $html .= '<div id="btzc-el-self-service-form-main">';
    $html .= '    <div id ="btzc-el-self-service-name-row">';
	$html .= '        <input type="hidden" class="btzc-el-ssf-textfield" id="btzc-el-self-service-employee-id" value="' . ($employee != null ? $employee->get_id() : '0') . '" />';
    $html .= '        <input type="text" class="btzc-el-ssf-textfield" id="btzc-el-self-service-firstname" placeholder="Vorname" value="' . ($employee != null ? $employee->get_first_name() : '') . '" />';
    $html .= '        <input type="text" class="btzc-el-ssf-textfield" id="btzc-el-self-service-lastname" placeholder="Nachname" value="'. ($employee != null ? $employee->get_last_name() : '') .'" />';
    $html .= '    </div>';
    $html .= '    <div id = "btzc-el-self-service-gender-row">';
    $html .= '        <label for="btzc-el-self-service-gender-selection">Geschlecht</label>';
    $html .= '        <select id="btzc-el-self-service-gender-selection">';
    $html .= '            <option value="undefined">Keine Auswahl</option>';
    $html .= '            <option value="male" ' . ($employee != null && $employee->get_gender() == 'male' ? 'selected="selected"' : '') . '">männlich</option>';
    $html .= '            <option value="female" ' . ($employee != null && $employee->get_gender() == 'female' ? 'selected="selected"' : '') .' >weiblich</option>';
    $html .= '            <option value="diverse" ' . ($employee != null && $employee->get_gender() == 'diverse' ? 'selected="selected"' : '') . '>divers</option>';
    $html .= '        </select>';
    $html .= '    </div>';
    $html .= '    <div id = "btzc-el-self-service-department-container">';
    $html .= '        <div id = "btzc-el-self-service-department-label-container">';
    $html .= '            <label for="btzc-el-self-service-department-selection">Bereich(e)</label>';
    $html .= '        </div>';
    $html .= '        <div id = "btzc-el-self-service-department-selection-container">';
    $html .=            $employee != null ? getDepartmentSelectElement($employee->get_departments()->get_departments()) : getDepartmentSelectElement();
    $html .= '        </div>';
    $html .= '    </div>';
    $html .= '    <div id = "btzc-el-self-service-occupation-container">';
    $html .= '        <div id = "btzc-el-self-service-occupation-label-container">';
    $html .= '            <label for="btzc-el-self-service-occupation-selection">Position(en)</label>';
    $html .= '        </div>';
    $html .= '        <div id = "btzc-el-self-service-occupation-selection-container">';
    $html .=            $employee != null ? getOccupationSelectElement($employee->get_occupations()->get_occupations()) : getOccupationSelectElement();
    $html .= '        </div>';
    $html .= '    </div>';
    $html .= '    <div id = "btzc-el-self-service-phone-and-room-row">';
    $html .= '        <div id = "btzc-el-self-service-phone-container">';
    $html .= '            <label for="btzc-el-self-service-phone-number" hidden>Telefonnummer</label>';
    $html .= '            <input type="text" class="btzc-el-ssf-textfield" id="btzc-el-self-service-phone-number" placeholder="Telefonnummer" value="' . ($employee != null ? $employee->get_phone_number() : '') . '" />';
    $html .= '        </div>';
    $html .= '        <div id = "btzc-el-self-service-room-container">';
    $html .= '            <label for="btzc-el-self-service-room-number" hidden>Raumnummer</label>';
    $html .= '            <input type="text" class="btzc-el-ssf-textfield" id="btzc-el-self-service-room-number" placeholder="Raumnummer" value="' . ($employee != null ? $employee->get_room_number() : '') . '" />';
    $html .= '        </div>';
    $html .= '    </div>';
    $html .= '    <div id = "btzc-el-self-service-email-row">';
    $html .= '        <label for="btzc-el-self-service-email-address" hidden>E-Mail-Adresse</label>';
    $html .= '        <input type="text" class="btzc-el-ssf-textfield" id="btzc-el-self-service-email-address" placeholder="E-Mail-Adresse" value="' . ($employee != null ? $employee->get_email_address() : '') .'" />';
    $html .= '    </div>';
    $html .= '    <div id = "btzc-el-self-service-info-row">';
    $html .= '        <label for="btzc-el-self-service-info">Zusätzliche Informationen:</label>';
    $html .= '        <input type="text" class="btzc-el-ssf-textfield" id="btzc-el-self-service-info" placeholder="Vorsicht, wenn ich noch keinen Kaffee hatte! o.ä.  ;-)" value="' . ($employee != null ? $employee->get_information() : '') . '" />';
    $html .= '    </div>';
    $html .= '</div>';
    $html .= '<div id="btzc-el-self-service-form-secondary">';
    $html .= '    <div id = "btzc-el-self-service-image-container">';
    $html .= '        <img id="btzc-el-self-service-form-image" src="' . ($employee != null ? $employee->get_image_url() : BTZC_EL_BASE_URL . "public/images/profile_placeholder.png") . '" alt="Bild" data-default="' . BTZC_EL_BASE_URL . "public/images/profile_placeholder.png" . '" />';
    $html .= '        <input type="file" name="btzc-el-employee-photo-upload" id="btzc-el-self-service-form-image-file" accept="image/*" hidden />';
    $html .= '    </div>';
    $html .= '    <div id = "btzc-el-self-service-controls-container">';
    $html .= '        <input type="button" class="btzc-v2-basic-button btzc-v2-standard-button" id="btzc-el-self-service-button-fileupload" value="Bild hochladen" />';
	$html .= '        <input type="button" class="btzc-v2-basic-button btzc-v2-standard-button" id="btzc-el-self-service-button-template" value="Platzhalter wählen" />';
    $html .= '    </div>';
    $html .= '</div>';
    $html .= '</div>';
	if ($employee == null   ||   ! username_exists(explode( "@", $employee->get_email_address() )[0])) {
		$html .= '<div id="btzc-el-self-service-wordpress-account-container">';
		$html .= '	<div id="btzc-el-self-service-wordpress-account-request-container">';
		$html .= '		<input type="checkbox" id="btzc-el-self-service-wordpress-account-checkbox" />';
		$html .= '		<label for="btzc-el-self-service-wordpress-account-checkbox">Ja, ich möchte Zugangsdaten für das BTZ-Wiki um eigene Beiträge veröffentlichen zu können.</label>';
		$html .= '	</div>';
		$html .= '	<div id="btzc-el-self-service-wordpress-account-data-container">';
		$html .= '        <input type="text" id="btzc-el-self-service-username" readonly placeholder="Benutzername" value="' . ( $employee != null ? explode( "@", $employee->get_email_address() )[0] : '' ) . '" />';
		$html .= '        <input type="password" id="btzc-el-self-service-password" placeholder="Passwort" />';
		$html .= '	</div>';
		$html .= '</div>';
	}
	$html .= '<div id="btzc-el-self-service-form-submit-container">';
	$html .= '	<input type="button" class="btzc-v2-basic-button btzc-v2-standard-button" id="btzc-el-self-service-button-submit" value="Daten speichern" />';
	$html .= '</div>';
    return $html;
}



/**
 * Handles a PIN reset request by validating a submitted username, matching it with existing employee records,
 * generating a new PIN, and sending an email with the new PIN to the employee.
 *
 * @return void Outputs a JSON response indicating success or failure of the PIN reset process.
 */
function handle_pin_reset_request() {
	$employees = Employee::get_all();
	$raw_username = sanitize_text_field($_POST['btzc-el-reset-pin-username']);
	$username_from_post = explode("@", $raw_username)[0];
	foreach ($employees as $employee) {
		$username_from_db = explode("@", $employee->get_email_address())[0];
		if (strtolower($username_from_db) == strtolower($username_from_post)) {
			$success = send_pin_email($employee->get_first_name(), $employee->get_last_name(), $employee->get_email_address(), $employee->generate_new_ssf_pin());
			if ($success) {
				wp_send_json_success();
			} else {
				wp_send_json_error();
			}
		}
	}
}


/**
 * Processes the submission of the Employee Self-Service form by handling and saving
 * employee details, including personal information, department and occupation associations,
 * and optional WordPress user account creation.
 *
 * @return void This function does not return a value. It sends a JSON response indicating success or failure
 *              based on the processing and operations performed.
 */
function handle_self_service_form_submit() {
	$employee_id = sanitize_text_field($_POST['employee_id']);
	$first_name = sanitize_text_field($_POST['first_name']);
	$last_name = sanitize_text_field($_POST['last_name']);
	$gender = sanitize_text_field($_POST['gender']);
	$departments = explode(' ', $_POST['departments']);
	$occupations = explode(' ', $_POST['occupations']);
	$phone_number = sanitize_text_field($_POST['phone_number']);
	$room_number = sanitize_text_field($_POST['room_number']);
	$email_address = sanitize_text_field($_POST['email_address']);
	$information = sanitize_text_field($_POST['information']);
	$wordpress_username = sanitize_text_field($_POST['wp_username']);
	$wordpress_password = sanitize_text_field($_POST['wp_password']);

	$image_url = Gallery::upload_images($first_name, $last_name);
	if (empty($image_url)) {
		if (Employee::get_by_id($employee_id)) {
			$image_url = array(Employee::get_by_id($employee_id)->get_image_url());
		} else {
			$upload_dir = wp_get_upload_dir();
			$upload_url = $upload_dir['baseurl'] . '/employee_images';
			$image_url = array( $upload_url . '/profile_placeholder.png' );
		}
	}

	$employee = new Employee($employee_id, $first_name, $last_name, $room_number, $phone_number, $email_address, $image_url[0], $gender, $information);
	$id = $employee->persist();

	Employee_Department::clear_department_associations($id);
	foreach ($departments as $department) {
		Employee_Department::register($id, $department);
	}

	Employee_Occupation::clear_occupation_associations($id);
	foreach ($occupations as $occupation) {
		Employee_Occupation::register($id, $occupation);
	}

	if ($wordpress_username != null && $wordpress_password != null) {
		$user = array(
			'user_pass' => $wordpress_password,
			'user_login' => $wordpress_username,
			'user_email' => $email_address,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'role' => 'author'
		);
		wp_insert_user($user);
	}

	if ($employee_id == $id) {
		wp_send_json_success();
	} else {
		$pin = Employee::get_by_id( $id )->generate_new_ssf_pin();
		$success = send_pin_email($first_name, $last_name, $email_address, $pin);
		if ($success) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}
}



/**
 * Handles the login attempt for the Employee-Self-Service form.
 * Validates user credentials by comparing submitted username and PIN
 * against stored employee data and hashes.
 *
 * @return string The HTML content for the self-service form upon successful login,
 *                or the login form with an error message upon failure.
 */
function handle_log_in_attempt() {
	$employees = Employee::get_all();
	$raw_username = sanitize_text_field($_POST['btzc-el-edit-data-username']);
	$username_from_post = explode("@", $raw_username)[0];
	$pin_from_post = sanitize_text_field($_POST['btzc-el-edit-data-pin']);
	foreach ($employees as $employee) {
		$username_from_db = explode("@", $employee->get_email_address())[0];
		if (strtolower($username_from_db) == strtolower($username_from_post)) {

			$pin_from_db = $employee->get_ssf_pin_hash();
			if ($pin_from_db != null && password_verify($pin_from_post, $pin_from_db)) {
				return get_self_service_form_html( $employee );
			}
		}
	}
	return get_edit_data_log_in_html(true);
}


/**
 * Generates the HTML content for the login form used in the Edit Data section.
 *
 * @param bool $invalid Optional parameter to indicate whether the form should display validation errors.
 *        Defaults to false.
 *
 * @return string The generated HTML content of the login form.
 */
function get_edit_data_log_in_html($invalid = false) {
	$html  = '<form method="POST">';
	$html .= '	<div id="btzc-el-edit-data-form-login-container">';
	$html .= '  <div id="btzc-el-edit-data-form-login-elements">';
    $html .= '        <input type="text" '. ($invalid ? 'class="is-invalid"' : ''). ' name="btzc-el-edit-data-username" id="btzc-el-edit-data-username" placeholder="Benutzername oder E-Mail" />';
    $html .= '        <input type="password" '. ($invalid ? 'class="is-invalid"' : ''). ' name="btzc-el-edit-data-pin" id="btzc-el-edit-data-pin" placeholder="PIN" />';
    $html .= '        <input type="submit" class="btzc-v2-basic-button btzc-v2-standard-button" id="btzc-el-edit-data-button-login" value="Anmelden" />';
	$html .= '        <div class="invalid-feedback" id="btzc-el-edit-data-info-no-username" hidden>Bitte geben Sie Ihren<br>Benutzernamen ein!</div>';
	$html .= '        <a href="javascript:void(0);" id="btzc-el-edit-data-button-forgot-pin">Neuen Pin per E-Mail erhalten.</a>';
	$html .= '	</div>';
	$html .= '	</div>';
	$html .= '</form>';
	return $html;
}


/**
 * Generates an HTML select element for department selection, optionally including pre-selected departments.
 *
 * @param array $selected An array of pre-selected department objects. Each object should have methods get_id() and get_department().
 *                        If empty, the returned HTML includes a single dropdown with all departments.
 *
 * @return string The HTML markup for one or more department selection elements.
 */
function getDepartmentSelectElement($selected = Array()) {
    $departments = Department::get_all();
    $html = '';
    if (count($selected) == 0) {
        $html .= '<select class="btzc-el-self-service-department-selection-element">';
        $html .= '    <option value="undefined">...</option>';
        foreach ($departments as $department) {
            $html .= '    <option value="' . $department->get_id() . '">' . $department->get_department() . '</option>';
        }
        $html .= '</select>';
    } else {
        foreach ($selected as $selection) {
            $html .= '<select class="btzc-el-self-service-department-selection-element">';
            $html .= '    <option value="undefined">...</option>';
            foreach ($departments as $department) {
                if ($department->get_id() == $selection->get_id()) {
                    $html .= '    <option selected="selected" value="' . $department->get_id() . '">' . $department->get_department() . '</option>';
                } else {
                    $html .= '    <option value="' . $department->get_id() . '">' . $department->get_department() . '</option>';
                }
            }
            $html .= '</select>';
        }
	    $html .= '<select class="btzc-el-self-service-department-selection-element">';
	    $html .= '    <option value="undefined">...</option>';
	    foreach ($departments as $department) {
		    $html .= '    <option value="' . $department->get_id() . '">' . $department->get_department() . '</option>';
	    }
	    $html .= '</select>';
    }
    return $html;
}


/**
 * Generates HTML select elements for occupation selection, with optional pre-selected values.
 *
 * @param array $selected An array of selected occupation objects. If empty, generates a single select element without pre-selected values.
 *
 * @return string The HTML markup for one or more occupation selection dropdowns.
 */
function getOccupationSelectElement($selected = Array()) {
    $occupations = Occupation::get_all();
    $html = '';
    if (count($selected) == 0) {
        $html .= '<select class="btzc-el-self-service-occupation-selection-element">';
        $html .= '    <option value="undefined">...</option>';
        foreach ($occupations as $occupation) {
            $html .= '    <option value="' . $occupation->get_id() . '">' . $occupation->get_occupation() . '</option>';
        }
        $html .= '</select>';
    } else {
        foreach ($selected as $selection) {
            $html .= '<select class="btzc-el-self-service-occupation-selection-element">';
            $html .= '    <option value="undefined">...</option>';
            foreach ($occupations as $occupation) {
                if ($occupation->get_id() == $selection->get_id()) {
                    $html .= '    <option selected value="' . $occupation->get_id() . '">' . $occupation->get_occupation() . '</option>';
                } else {
                    $html .= '    <option value="' . $occupation->get_id() . '">' . $occupation->get_occupation() . '</option>';
                }
            }
            $html .= '</select>';
        }
	    $html .= '<select class="btzc-el-self-service-occupation-selection-element">';
	    $html .= '    <option value="undefined">...</option>';
	    foreach ($occupations as $occupation) {
		    $html .= '    <option value="' . $occupation->get_id() . '">' . $occupation->get_occupation() . '</option>';
	    }
	    $html .= '</select>';
    }
    return $html;
}


/**
 * Sends an email containing a PIN to the specified recipient.
 *
 * @param string $first_name The first name of the recipient.
 * @param string $last_name The last name of the recipient.
 * @param string $recipient The email address of the recipient.
 * @param string $pin The PIN to be sent in the email.
 *
 * @return bool True if the email was sent successfully, false otherwise.
 */
function send_pin_email($first_name, $last_name, $recipient, $pin) {
	$subject = 'Ihr neuer Wiki-Pin!';
	$message =
		'Hallo ' . $first_name. ' ' . $last_name . ',' . PHP_EOL . PHP_EOL .
		'Ihr neuer Wiki-Pin lautet: ' . $pin . PHP_EOL . PHP_EOL .
		'Mit freundlichen Grüßen' . PHP_EOL .
		'Das Wiki-Team';
	return wp_mail($recipient, $subject, $message);
}