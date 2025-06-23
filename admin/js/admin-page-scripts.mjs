import {localized} from '../../language/backend-dictionary.mjs';

jQuery(function ($) {

	const pageTitleRow = $('.btzc-admin-page-title-row');
	const companyName = $('#btzc-admin-page-company-name');
	const pageTitle = $('#btzc-admin-page-title');

	const menuTabEmployeesTab = $('#btzc-el-employee-tab');
	const menuTabGalleryTab = $('#btzc-el-gallery-tab');
	const menuTabDepartmentsTab = $('#btzc-el-departments-tab');
	const menuTabOccupationsTab = $('#btzc-el-occupations-tab');

	let tabOnDisplay = 'btzc-el-employees';
	let scrollPosition = {
		'btzc-el-employees': 0,
		'btzc-el-gallery': 0,
		'btzc-el-departments': 0,
		'btzc-el-occupations': 0
	};

	(async () => pageTitle.prop('innerHTML', ' ' + await localized('Employee Register')))();


	(async() => menuTabEmployeesTab.prop('innerHTML', await localized('Employees')))();
	(async() => menuTabGalleryTab.prop('innerHTML', await localized('Gallery')))();
	(async() => menuTabDepartmentsTab.prop('innerHTML', await localized('Departments')))();
	(async() => menuTabOccupationsTab.prop('innerHTML', await localized('Occupations')))();


	menuTabEmployeesTab.on('click', function () {
		scrollPosition[tabOnDisplay] = window.scrollY;
		openPage('btzc-el-employees', this);
		tabOnDisplay = 'btzc-el-employees';
		window.scrollTo(0, scrollPosition[tabOnDisplay]);
	})

	menuTabGalleryTab.on('click', function () {
		scrollPosition[tabOnDisplay] = window.scrollY;
		openPage('btzc-el-gallery', this);
		tabOnDisplay = 'btzc-el-gallery';
		window.scrollTo(0, scrollPosition[tabOnDisplay]);
	})

	menuTabDepartmentsTab.on('click', function () {
		scrollPosition[tabOnDisplay] = window.scrollY;
		openPage('btzc-el-departments', this);
		tabOnDisplay = 'btzc-el-departments';
		window.scrollTo(0, scrollPosition[tabOnDisplay]);
	})

	menuTabOccupationsTab.on('click', function () {
		scrollPosition[tabOnDisplay] = window.scrollY;
		openPage('btzc-el-occupations', this);
		tabOnDisplay = 'btzc-el-occupations';
		window.scrollTo(0, scrollPosition[tabOnDisplay]);
	})


	/**
	 * Opens a specified tab page and updates the associated tab links' appearance.
	 *
	 * @param {string} pageName - The ID of the tab content element to display.
	 * @param {HTMLElement} htmlElement - The tab link element associated with the tab to be displayed.
	 *
	 * @return {void}
	 *
	 * @since 1.0.0
	 */
	function openPage(pageName, htmlElement) {
		let i, tabContent, tabLinks;
		// Hide all tab-pages.
		tabContent = document.getElementsByClassName("btzc-v2-tab");
		for (i = 0; i < tabContent.length; i++) {
			tabContent[i].style.display = "none";
		}

		// Reset all tab-links to unselected.
		tabLinks = document.getElementsByClassName("btzc-v2-tab-link");
		for (i = 0; i < tabLinks.length; i++) {
			tabLinks[i].style.backgroundColor = "transparent";
			tabLinks[i].style.color = getComputedStyle(document.body).getPropertyValue('--btzc-v2-tab-bar-color')
		}

		// Display the selected tab-page
		document.getElementById(pageName).style.display = "block";

		// Mark the associated tab-links as selected
		htmlElement.style.backgroundColor = getComputedStyle(document.body).getPropertyValue('--btzc-v2-tab-selected-bg');
		htmlElement.style.color = getComputedStyle(document.body).getPropertyValue('--btzc-v2-tab-selected-color');
	}


	function loadCompanyName() {
		$.ajax({
			url: ajaxurl,
			type: 'GET',
			data: {action: 'btzc_el_get_company_name'},
			success: function (response) {
				if (response.success) {
					companyName.prop('innerHTML', response.data);
				}
			}
		})
	}


	pageTitleRow.on('click', function () {
		companyName.trigger('focus');
	})


	companyName.on('focus', function (e) {
		window.getSelection().selectAllChildren(e.target);
	})


	companyName.on('focusout', function () {
		const formData = new FormData();
		formData.append('action', 'btzc_el_update_company_name');
		formData.append('company_name', companyName.prop('innerHTML'));
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			contentType: false,
			processData: false,
			success: function (response) {
			},
			error: function (response) {
			}
		})
	})


	companyName.on('keydown', function (e) {
		if (e.key === 'Enter') {
			e.preventDefault();
			companyName.trigger('focusout');
			companyName.trigger('blur');
		}
		if (e.key === 'Escape') {
			e.preventDefault();
			loadCompanyName()
			companyName.trigger('blur');
		}
	})



	$('#btzc-el-button-shortcode-to-clipboard').on('click', function() {
		$.ajax({
			url: ajaxurl,
			type: 'GET',
			data: { action: 'btzc_el_department_data'},
			success: function(response) {
				if (response.success) {
					Promise.all([
						localized('Shortcode selection'),
						localized('Shortcode copied to clipboard'),
						localized('Unable to copy shortcode to clipboard'),
						localized('No limitations'),
						localized('Limit to department'),
					]).then(([shortCodeSelection, shortcodeCopied, shortcodeCopyError, noLimitations, limitedToDepartment]) => {
						const departments = response.data;
						let dialogHTML = '';
						dialogHTML += `<div id="department-selection-dialog" title="${shortCodeSelection}">`;
						dialogHTML += '  <div class="btzc-v2-basic-dialog-body">';
						dialogHTML += `<div class="department-selection-dialog-row unchecked-row" data-id="0"><p>${noLimitations}</p></div><br>`
						dialogHTML += `<div><p>${limitedToDepartment}</p></div>`;
						departments.forEach(department => {
							dialogHTML += `<div class="department-selection-dialog-row unchecked-row" data-id="${department['id']}"><p>${department['department']}</p></div>`
						});
						dialogHTML += '  </div>';
						dialogHTML += '</div>';

						$('body').append(dialogHTML);
						let departmentSelectionDialog = $('#department-selection-dialog');
						departmentSelectionDialog.dialog({
							autoOpen: true,
							width: 500,
							height: 500,
							modal: true,
							dialogClass: 'btzc-v2-basic-dialog btzc-el-employee-tab-attribute-selection-dialog',
							open: function () {
								$('.department-selection-dialog-row').on('click', function() {
									const id = $(this).data('id');
									let shortcode = id === 0 ? '[employee_list]' : `[employee_list department="${id}"]`;
									if (navigator.clipboard) {
										navigator.clipboard.writeText(shortcode).then(() => {
											showToastMessage(shortcodeCopied, 2000)
										});
									} else {
										alert(shortcodeCopyError + '\n' + shortcode);
									}
									departmentSelectionDialog.remove()
								})
							}, close: function () {
								departmentSelectionDialog.remove()
							}
						})
					})
				}
			}
		})
	})

	function showToastMessage(message, duration = 3000) {
		const toast = $('<div class="btzc-v2-toast-message"></div>');
		toast.text(message);

		$('body').append(toast);

		toast.fadeIn(500).css('opacity', 1);
		setTimeout(function () {
			toast.fadeOut(1000, function () {
				$(this).remove();
			});
		}, duration);
	}




	loadCompanyName();

	menuTabEmployeesTab.trigger('click');
})