<?php
/**
 * The Gallery class provides methods for managing employee images within the WordPress environment.
 *
 * This class includes functionalities to retrieve, upload, and delete images stored in a dedicated "employee_images"
 * directory under the WordPress uploads folder. The methods in this class are designed to be used as AJAX handlers
 * for backend operations.
 *
 * @package BTZ\Customized
 * @subpackage EmployeeList
 * @since 2.0.0
 */

namespace BTZ\Customized\EmployeeList;

# This file is only accessible from the WordPress backend.
defined('ABSPATH') or die('Unauthorized!');

class Gallery {

	/**
	 * Retrieves URLs of images stored in the 'employee_images' directory and returns them in a JSON response.
	 *
	 * This method scans the specified 'employee_images' directory for image files, constructs their full URLs,
	 * and sends them back as a JSON array. It excludes directory references such as '.' and '..'.
	 *
	 * @return void Sends a JSON response with an array of image URLs on success.
	 *
	 * @since 2.0.0
	 */
	public static function ajax_get_image_urls() {
		$upload_dir = wp_upload_dir();
		$source_dir = $upload_dir['basedir'] . '/employee_images';
		$source_url = $upload_dir['baseurl'] . '/employee_images';

		$images = scandir($source_dir);
		$image_urls = [];
		foreach ($images as $image) {
			if ($image !== '.'   &&   $image !== '..') {
				$image_urls[] = $source_url . '/' . ($image);
			}
		}
		wp_send_json_success($image_urls);
	}


	/**
	 * Deletes a specified image file from the 'employee_images' directory and returns a JSON response with the result.
	 *
	 * This method removes the image file corresponding to the provided URL from the 'employee_images' directory.
	 * If the deletion is successful, it returns a success response with the image path. Otherwise, it returns
	 * an error response with the image path.
	 *
	 * @param string $image_url The URL of the image to be deleted.
	 *
	 * @return void Sends a JSON response indicating the success or failure of the file deletion.
	 *
	 * @since 2.0.0
	 */
	public static function ajax_delete_image($image_url) {
		$upload_dir = wp_upload_dir();
		$source_url = $upload_dir['baseurl'] . '/employee_images';
		$source_dir = $upload_dir['basedir'] . '/employee_images';
		$image = str_replace($source_url, '', sanitize_url($image_url));
		if (unlink($source_dir . $image)) {
			wp_send_json_success( $image );
		} else {
			wp_send_json_error( $image );
		}
	}


	/**
	 * Handles the AJAX image upload process, saves the files to the server, and returns their URLs as a JSON response.
	 *
	 * This method processes uploaded image files, validates their file types, and stores them in the 'employee_images'
	 * directory inside the WordPress uploads folder. If successful, it returns the URLs of the uploaded images.
	 * Unsupported file types or failed uploads are skipped. If no valid files are uploaded, an error response is returned.
	 *
	 * @return void Sends a JSON response containing an array of uploaded image URLs on success or an error message on failure.
	 *
	 * @since 2.0.0
	 */
	public static function ajax_upload_images() {
		if (empty($_FILES['btzc-el-employee-photo-upload'])) {
			wp_send_json_error(['message' => 'No file uploaded.']);
		}

		$uploaded_urls = self::upload_images();

		if (empty($uploaded_urls)) {
	        wp_send_json_error(['message' => 'Keine gültigen Dateien wurden hochgeladen.']);
        }
		wp_send_json_success($uploaded_urls);
	}

	public static function upload_images($first_name = "", $last_name = "") {
		if (empty($_FILES['btzc-el-employee-photo-upload'])) {
			return array();
		}

		$sub_dir = '/employee_images';
		$upload_dir = wp_get_upload_dir();
		$upload_path = $upload_dir['basedir'] . '/' . $sub_dir;
		$upload_url = $upload_dir['baseurl'] . '' . $sub_dir;

		if (!file_exists($upload_path)) {
			wp_mkdir_p($upload_path);
		}
		$allowed_file_types = ['image/jpeg', 'image/png', 'image/gif'];
		$uploaded_files = $_FILES['btzc-el-employee-photo-upload'];
		$uploaded_urls = [];

		foreach ($uploaded_files['name'] as $key => $value) {
			$file_type = $uploaded_files['type'][$key];
			if (!in_array($file_type, $allowed_file_types)  ||  $uploaded_files['size'][$key] > 5*1024*1024) {
				continue;
			}

			$file_name = sanitize_file_name($uploaded_files['name'][$key]);
			if ($first_name != "" && $last_name != "" ) {
				$file_name_components = explode(".", $file_name);
				$extension = end($file_name_components);
				$file_name = strtolower($last_name . "-" . $first_name . "." . $extension);
			}

			$file = [
				'name'     => $file_name,
				'type'     => $uploaded_files['type'][$key],
				'tmp_name' => $uploaded_files['tmp_name'][$key],
				'error'    => $uploaded_files['error'][$key],
				'size'     => $uploaded_files['size'][$key],
			];

			$destination = ['path' => $upload_path, 'url' => $upload_url];

			$upload_result = self::persist_image($file, $destination);

			if ($upload_result && isset($upload_result['url'])) {
				$uploaded_urls[] = $upload_result['url'];
			}
		}
		return $uploaded_urls;
	}

	/**
	 * Persists an uploaded image file to a specified destination.
	 *
	 * @param array $file An associative array containing the uploaded file information,
	 *                     which typically includes 'tmp_name' (temporary file location) and 'name' (original filename).
	 * @param array $destination An associative array specifying the destination for the uploaded file,
	 *                            including 'path' (destination directory path) and 'url' (base URL for access).
	 *
	 * @return array|false Returns an array with file path and URL information if the file is successfully moved,
	 *                     or false if the operation fails.
	 *
	 * @since 2.0.0
	 */
	private static function persist_image($file, $destination) {
		if (empty($file['tmp_name'])) {
			return false;
		}
		$filename = $file['name'];
		$uploaded_path = $destination['path'] . '/' . $filename;

		$move_result = move_uploaded_file($file['tmp_name'], $uploaded_path);

		return $move_result ? ['file' => $uploaded_path,'url'  => $destination['url'] . '/' . $filename] : false;
	}

}