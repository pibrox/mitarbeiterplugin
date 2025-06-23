import {localized} from "../../language/backend-dictionary.mjs";

jQuery(function($) {
    const employeeTileTable = $('#btzc-el-employee-tile-table');
    const backendDepartmentsSelection = $('#btzc-el-employee-tab-department-selection');
    const backendOccupationsSelection = $('#btzc-el-employee-tab-occupation-selection');
    const backendSearchField = $('#btzc-el-employee-tab-search-field')


    const alphabet = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];

    const AsciiReplacement = {'ä':'ae', 'Ä':'Ae', 'ö':'oe', 'Ö':'Oe', 'ü':'ue', 'Ü':'Ue', 'ß':'ss'};






/**********************************************************
*                                                         *
*                ALPHABETIC INDEX BUTTONS                 *
*                                                         *
**********************************************************/

    alphabet.forEach((letter, index) => {
        const button = $('<button class="btzc-v2-basic-button btzc-v2-standard-button btzc-el-alphabetic-index-button">' + letter + '</button>');
        if (index % 2 === 0) {
            $('#btzc-el-employee-tab-alphabetic-index-container-left').append(button);
        } else {
            $('#btzc-el-employee-tab-alphabetic-index-container-right').append(button);
        }
    });


    $('.btzc-el-alphabetic-index-button').on('click', function() {
        const startingLetter = $(this).prop('innerHTML');
        employeeTileTable.children().each((index, employee) => {
            if (! employee.classList.contains('btzc-el-employee-tab-add-new-dataset-tile')) {
                const lastName = $(employee).find('.btzc-el-employee-tab-tile-last-name').prop('innerHTML');
                const lastNameFirstLetter = lastName.charAt(0).toUpperCase();
                if (lastNameFirstLetter === startingLetter   ||   lastNameFirstLetter > startingLetter) {
                    const top = Math.round(employee.getBoundingClientRect().top);
                    const pageHeaderBottom = $('.btzc-v2-page-header')[0].getBoundingClientRect().bottom;
                    window.scrollBy({top: top - pageHeaderBottom, behavior: 'smooth'});
                    return false;
                }
            }
        })
    })






/**********************************************************
*                                                         *
*                     DATA RETRIEVAL                      *
*                                                         *
**********************************************************/

    $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: { action: 'btzc_el_employee_data', },
        success: function(response) {
            createAddNewEmployeeBackEndTile().then(function(tile) {
                employeeTileTable.prepend(tile);
                if (response.success) {
                    let employees = response.data;
                    employees.forEach((employee) => {
                        createEmployeeBackEndTile(employee).then(function(tile) {
                            employeeTileTable.append(tile);
                            $(tile).find('.btzc-el-employee-tab-tile-footer').slideUp(0);
                        })
                    })
                }
            })
        }
    })


    $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: { action: 'btzc_el_department_data', },
        success: function(response) {
            if (response.success) {
                Promise.all([localized('All departments'), localized('Department(s)')]).then(function(translated) {
                    let departments = response.data;
                    fillSelectElement(translated[0], response.data, 'department', backendDepartmentsSelection);
                    const departmentsTable = $('#btzc-el-employee-departments');
                    const selection = $('<select id="btzc-el-employee-departments-selection" multiple></select>');
                    departments.forEach(department => {
                        selection.append($('<option value="' + department['id'] + '">' + department['department'] + '</option>'))
                    })
                    departmentsTable.append($('<label for="btzc-el-employee-departments-selection">' + translated[1] + '</label>'));
                    departmentsTable.append(selection);
                });
            }
        }
    })


    $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: { action: 'btzc_el_occupation_data', },
        success: function(response) {
            if (response.success) {
                Promise.all([localized('All occupations'), localized('Occupation(s)')]).then(function(translated) {
                    let occupations = response.data;
                    fillSelectElement(translated[0], response.data, 'occupation', backendOccupationsSelection);
                    const occupationsTable = $('#btzc-el-employee-occupations');
                    const selection = $('<select id="btzc-el-employee-occupations-selection" multiple></select>');
                    occupations.forEach(occupation => {
                        selection.append($('<option value="' + occupation['id'] + '">' + occupation['occupation'] + '</option>'))
                    })
                    occupationsTable.append($('<label for="btzc-el-employee-occupations-selection">' + translated[1] + '</label>'));
                    occupationsTable.append(selection);
                })
            }
        }
    })


    function fillSelectElement(first, jsonData, key, selectElement) {
        let element = $('<option></option>');
        element.val(0);
        element.text(first);
        selectElement.append(element);

        jsonData.forEach((item) => {
            let element = $('<option></option>');
            element.val(item['id']);
            element.text(item[key]);
            selectElement.append(element);
        })
    }






/**********************************************************
*                                                         *
*                BACKEND SEARCH AND FILTER                *
*                                                         *
**********************************************************/
    localized('Search').then(function(translated) {
        backendSearchField.attr('placeholder', translated);
    });

    backendSearchField.on('focus', function() {
        $(backendDepartmentsSelection).prop('value', '0').change();
        $(backendOccupationsSelection).prop('value', '0').change();
    })


    backendSearchField.on('input', function(e) {
        const searchString = e.target.value;
        employeeTileTable.children().each((index, child) => {
            if (! $(child)[0].classList.contains('btzc-el-employee-tab-add-new-dataset-tile')) {
                const lastName = $(child).find('.btzc-el-employee-tab-tile-last-name').text();
                const firsName = $(child).find('.btzc-el-employee-tab-tile-first-name').text();
                if (firsName.toLowerCase().includes(searchString.toLowerCase())  ||  lastName.toLowerCase().includes(searchString.toLowerCase())) {
                    $(child).slideDown(250, function () {});
                } else {
                    $(child).slideUp(250, function () {});
                }
            }
        })
    })


    backendDepartmentsSelection.on('change', function() {
        $(backendSearchField).prop('value', '');
        const selectedDepartment = Number.parseInt($(this).find(':selected').val());
        const selectedOccupation = Number.parseInt($('#btzc-el-employee-tab-occupation-selection').find(':selected').val());
        filterEmployeeTiles(selectedDepartment, selectedOccupation);
    })


    backendOccupationsSelection.on('change', function() {
        $(backendSearchField).prop('value', '');
        const selectedDepartment = Number.parseInt($('#btzc-el-employee-tab-department-selection').find(':selected').val());
        const selectedOccupation = Number.parseInt($(this).find(':selected').val());
        filterEmployeeTiles(selectedDepartment, selectedOccupation);
    })


    function filterEmployeeTiles(department, occupation) {
        if (department === 0  &&  occupation === 0) {
            expandAll()
        } else if (department !== 0  &&  occupation === 0) {
            filterForDepartmentsOnly(department);
        } else if (department === 0  &&  occupation !== 0) {
            filterForOccupationsOnly(occupation);
        } else {
            filterForDepartmentsAndOccupations(department, occupation);
        }


        function expandAll() {
            employeeTileTable.children().each((index, employee) => {
                if (! employee.classList.contains('btzc-el-employee-tab-add-new-dataset-tile')) {
                    $(employee).slideDown(400, function() {});
                }
            })
        }
        function filterForDepartmentsOnly(department) {
            employeeTileTable.children().each((index, employee) => {
                if (! employee.classList.contains('btzc-el-employee-tab-add-new-dataset-tile')) {
                    const data = $(employee).find('.btzc-el-employee-tab-tile-departments-section').data('array');
                    if (!(data.includes(department))) {
                        $(employee).slideUp(400, function () {
                        });
                    } else {
                        $(employee).slideDown(400, function () {
                        });
                    }
                }
            })
        }

        function filterForOccupationsOnly(occupation) {
            employeeTileTable.children().each((index, employee) => {
                if (! employee.classList.contains('btzc-el-employee-tab-add-new-dataset-tile')) {
                    const data = $(employee).find('.btzc-el-employee-tab-tile-occupations-section').data('array');
                    if (!(data.includes(occupation))) {
                        $(employee).slideUp(400, function () {
                        });
                    } else {
                        $(employee).slideDown(400, function () {
                        });
                    }
                }
            })
        }

        function filterForDepartmentsAndOccupations(department, occupation) {
            employeeTileTable.children().each((index, employee) => {
                if (! employee.classList.contains('btzc-el-employee-tab-add-new-dataset-tile')) {
                    const depData = $(employee).find('.btzc-el-employee-tab-tile-departments-section').data('array');
                    const occData = $(employee).find('.btzc-el-employee-tab-tile-occupations-section').data('array');
                    if (!(depData.includes(department) && occData.includes(occupation))) {
                        $(employee).slideUp(400, function () {
                        });
                    } else {
                        $(employee).slideDown(400, function () {
                        });
                    }
                }
            })
        }
    }






/**********************************************************
*                                                         *
*                     EMPLOYEE TILES                      *
*                                                         *
**********************************************************/

    function createAddNewEmployeeBackEndTile() {
        return localized('Add new employee').then(function(translated) {
            let html = '<div id="btzc-el-employee-tab-add-new-dataset-tile" class="btzc-el-employee-tab-tile btzc-el-employee-tab-add-new-dataset-tile">';
            html += '<p>' + translated + '</p>';
            html += '</div>';
            return $.parseHTML(html)[0];
        })
    }


    function createEmployeeBackEndTile(json) {
        const departmentData = getDepartments()
        const occupationData = getOccupations()
        return Promise.all([
            localized('genders'),
            localized('Gender'),
            localized('Departments'),
            localized('Occupations'),
            localized('Phone'),
            localized('Office'),
            localized('E-Mail'),
            localized('Information'),
            localized('Save'),
            localized('Cancel'),
            localized('Delete'),
            getGenderSelectionElement()
        ]).then(([genders, genderText, departmentText, occupationText, phoneText, officeText, emailText, informationText, saveText, cancelText, deleteText, genderSelectionElement]) => {
            let html = '';
            html += '<div class="btzc-el-employee-tab-tile" data-id="' + json['id'] + '">';
            html += '  <div class="btzc-el-employee-tab-tile-image-container">';
            html += '    <img id="btzc-el-employee-tab-tile-image" alt="Image" src="' + json['image_url'] + '">';
            html += '  </div>';
            html += '  <div class="btzc-el-employee-tab-tile-data-container">';
            html += '    <div class="btzc-el-employee-tab-tile-name-row">';
            html += '      <p class="btzc-el-employee-tab-tile-first-name" contenteditable="true">' + json['first_name'] + '</p>';
            html += '      <p class="btzc-el-employee-tab-tile-last-name" contenteditable="true">' + json['last_name'] + '</p>';
            html += '    </div>';
            html += '    <div class="btzc-el-employee-tab-tile-gender-row">';
            html += '      <p class="btzc-el-tile-row-label">' + genderText + ': </p>';
            html += '      <p class="btzc-el-tile-row-value btzc-el-employee-tab-tile-gender">' + genders[json['gender']] +'</p>';
            html += '      <div class="btzc-el-employee-tab-tile-gender-selection" hidden>';
            html +=          genderSelectionElement;
            html += '      </div>';
            html += '    </div>';
            html += '    <div class="btzc-el-employee-tab-tile-department-occupation-section">';
            html += '      <div class="btzc-el-employee-tab-tile-departments-section" >';
            html +=          departmentData[0] ? departmentData[0] : '<p>' + departmentText + '</p>';
            html += '      </div>';
            html += '      <div class="btzc-el-employee-tab-tile-occupations-section">';
            html +=          occupationData[0] ? occupationData[0] : '<p>' + occupationText + '</p>';
            html += '      </div>';
            html += '    </div>';
            html += '    <div class="btzc-el-employee-tab-tile-contact-information">';
            html += '      <div class="btzc-el-employee-tab-tile-phone-and-room-row">';
            html += '        <div class="btzc-el-employee-tab-tile-phone-number-element">';
            html += '          <p class="btzc-el-tile-row-label">' + phoneText + ': </p>';
            html += '          <p class="btzc-el-tile-row-value btzc-el-employee-tab-tile-phone-number" contenteditable="true"> ' + json['phone_number'] + '</p>';
            html += '        </div>';
            html += '        <div class="btzc-el-employee-tab-tile-room-number-element">';
            html += '          <p class="btzc-el-tile-row-label">' + officeText + ': </p>';
            html += '          <p class="btzc-el-tile-row-value btzc-el-employee-tab-tile-room-number" contenteditable="true">' + json['room_number'] + '</p>';
            html += '        </div>';
            html += '      </div>';
            html += '      <div class="btzc-el-employee-tab-tile-email-row">';
            html += '        <p class="btzc-el-tile-row-label">' + emailText+ ': </p>';
            html += '        <p class="btzc-el-tile-row-value btzc-el-employee-tab-tile-email" contenteditable="true">' + json['email_address'] + '</p>';
            html += '      </div>';
            html += '    </div>';
            html += '    <div class="btzc-el-employee-tab-tile-additional-information-row">'
            html += '      <p class="btzc-el-tile-row-label">' + informationText + ': </p>';
            html += '      <p class="btzc-el-tile-row-value btzc-el-employee-tab-tile-additional-information" contenteditable="true">' + json['information'] + '<p>';
            html += '    </div>'
            html += '    <div class="btzc-el-employee-tab-tile-footer">';
            html += '      <button class="btzc-v2-basic-button btzc-v2-standard-button" id="employee-tab-button-save">' + saveText + '</button>';
            html += '      <button class="btzc-v2-basic-button btzc-v2-delete-button" id="employee-tab-button-cancel">' + cancelText + '</button>';
            html += '      <button class="btzc-v2-basic-button btzc-v2-delete-button" id="employee-tab-button-delete">' + deleteText + '</button>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';
            const tile = $.parseHTML(html)[0];
            $(tile).find('.btzc-el-employee-tab-tile-departments-section').data('array', JSON.stringify(departmentData[1]));
            $(tile).find('.btzc-el-employee-tab-tile-occupations-section').data('array', JSON.stringify(occupationData[1]));
            return tile;
        })


        function getGenderSelectionElement() {
            return localized('genders').then(function(genders) {
                let html  = '<select>';
                html += '  <option value="male">' + genders['male'] + '</option>';
                html += '  <option value="female">' + genders['female'] + '</option>';
                html += '  <option value="diverse">' + genders['diverse'] + '</option>';
                html += '  <option value="undefined">' + genders['undefined'] + '</option>';
                html += '</select>';
                return html;
            });
        }

        function getDepartments() {
            let departmentsIdList = [];
            let departmentsHTMLList = '';
            json['departments'].forEach((department) => {
                departmentsIdList.push(Number.parseInt(department['id']));
                departmentsHTMLList += '    <p>' + department['department'] + '</p>';
            })
            return [departmentsHTMLList, departmentsIdList];
        }

        function getOccupations() {
            let occupationsIdList = [];
            let occupationsHTMLList = '';
            json['occupations'].forEach((occupation) => {
                occupationsIdList.push(Number.parseInt(occupation['id']));
                occupationsHTMLList += '    <p>' + getGenderedOccupation(json['gender'], occupation) + '</p>';
            })
            return [occupationsHTMLList, occupationsIdList];
        }

        function getGenderedOccupation(gender, occupation) {
            if (gender === 'male') {
                return occupation['male-form'];
            } else if (gender === 'female') {
                return occupation['female-form'];
            } else if (gender === 'diverse') {
                return occupation['diverse-form'];
            } else {
                return occupation['occupation'];
            }
        }
    }






/**********************************************************
*                                                         *
*        CLICK-LISTENER FOR EMPLOYEE TILE ELEMENTS        *
*                                                         *
**********************************************************/

    employeeTileTable.on('click', '#btzc-el-employee-tab-add-new-dataset-tile', function () {
        const tile = $(this);
        const data = {
            first_name: '[VORNAME]',
            last_name: '[NACHNAME]',
            image_url: '',
            room_number: '[ROOM]',
            phone_number: '[TELEFON]',
            email_address: '[E-MAIL]',
            information: '[INFORMATION]',
            gender: 'undefined',
            departments: [],
            occupations: []
        };

        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: { action: 'btzc_el_employee_single_dataset', employee_id: '0'},
            success: function(response) {
                if (response.success) {
                    Promise.all([
                        localized('[FIRST NAME]'),
                        localized('[LAST NAME]'),
                        localized('[OFFICE NUMBER]'),
                        localized('[PHONE NUMBER]'),
                        localized(['[EMAIL@EXAMPLE.COM]']),
                        localized('[ADDITIONAL INFORMATION]')
                    ]).then(([firstNameText, lastNameText, officeText, phoneText, emailText, informationText]) => {
                        response.data['first_name'] = firstNameText;
                        response.data['last_name'] = lastNameText;
                        response.data['room_number'] = officeText;
                        response.data['phone_number'] = phoneText;
                        response.data['email_address'] = emailText;
                        response.data['information'] = informationText;
                        createEmployeeBackEndTile(response.data).then(addNewTile => {
                            $(addNewTile).slideUp(1, function () {})
                            $(addNewTile).find('#employee-tab-button-delete').remove();
                            $(tile).slideUp(300, function () {
                                $(tile).replaceWith(addNewTile);
                                $(addNewTile).slideDown(500, function () {})
                            })
                        })
                    })
                }
            }
        })
    })


    employeeTileTable.on('click', '.btzc-el-employee-tab-tile', function (e) {
        e.stopPropagation();
        $(this).find('.btzc-el-employee-tab-tile-footer').slideDown(250, function () {});
    })




    employeeTileTable.on('click', '.btzc-el-employee-tab-tile-departments-section', function () {
        const departmentsSection = $(this);
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: { action: 'btzc_el_department_data'},
            success: function(response) {
                if (response.success) {
                    localized('Departments (multiple selection possible)').then(translated => {
                        const departments = response.data;
                        let dataArray = departmentsSection.data('array');
                        const associatedDepartments = dataArray.length > 0 ? [].concat(JSON.parse(dataArray)) : [];

                        let dialogHTML = '';
                        dialogHTML += '<div id="department-selection-dialog" title="' + translated + '">';
                        dialogHTML += '  <div class="btzc-v2-basic-dialog-body">';
                        departments.forEach(department => {
                            if (associatedDepartments.includes(JSON.parse(department['id']))) {
                                dialogHTML += '<div class="department-selection-dialog-row checked-row" data-id="' + department['id'] + '">';
                            } else {
                                dialogHTML += '<div class="department-selection-dialog-row unchecked-row" data-id="' + department['id'] + '">';
                            }
                            dialogHTML += '<p>' + department['department'] + '</p>';
                            dialogHTML += '</div>';
                        });
                        dialogHTML += '  </div>';
                        dialogHTML += '</div>';

                        $('body').append(dialogHTML);

                        $('#department-selection-dialog').dialog({
                            autoOpen: true,
                            width: 500,
                            height: 500,
                            modal: true,
                            dialogClass: 'btzc-v2-basic-dialog btzc-el-employee-tab-attribute-selection-dialog',
                            open: function () {
                                $('.department-selection-dialog-row').on('click', function() {
                                    $(this).toggleClass('checked-row');
                                    $(this).toggleClass('unchecked-row');
                                    const id = $(this).data('id');
                                    if (associatedDepartments.includes(id)) {
                                        const index = associatedDepartments.indexOf(id);
                                        associatedDepartments.splice(index, 1);
                                    } else {
                                        associatedDepartments.push(JSON.parse(id));
                                    }
                                    departmentsSection.data('array', JSON.stringify(associatedDepartments));
                                    departmentsSection.children().each((index, department) => {department.remove()})
                                    if (associatedDepartments.length === 0) {
                                        departmentsSection.append($('<p>Bereiche</p>'))
                                    } else {
                                        associatedDepartments.forEach(department => {
                                            departmentsSection.append($('<p>' + (departments.find(item => Number.parseInt(item.id) === Number.parseInt(department)))['department'] + '</p>'))
                                        })
                                    }
                                })
                            }, close: function () {
                                $('#department-selection-dialog').remove()
                            }
                        })
                    })
                }
            }
        })
    })


    employeeTileTable.on('click', '.btzc-el-employee-tab-tile-occupations-section', function () {
        const occupationsSection = $(this);
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: { action: 'btzc_el_occupation_data'},
            success: function(response) {
                if (response.success) {
                    localized('Occupations (multiple selection possible)').then(translated => {
                        const occupations = response.data;
                        let dataArray = occupationsSection.data('array');
                        const associatedOccupations = dataArray.length > 0 ? [].concat(JSON.parse(dataArray)) : [];

                        let dialogHTML = '';
                        dialogHTML += '<div id="occupations-selection-dialog" title="' + translated + '">';
                        dialogHTML += '  <div class="btzc-v2-basic-dialog-body">';
                        occupations.forEach(occupation => {
                            if (associatedOccupations.includes(JSON.parse(occupation['id']))) {
                                dialogHTML += '<div class="occupation-selection-dialog-row checked-row" data-id="' + occupation['id'] + '">';
                            } else {
                                dialogHTML += '<div class="occupation-selection-dialog-row unchecked-row" data-id="' + occupation['id'] + '">';
                            }
                            dialogHTML += '<p>' + occupation['occupation'] + '</p>';
                            dialogHTML += '</div>';
                        });
                        dialogHTML += '  </div>';
                        dialogHTML += '</div>';

                        $('body').append(dialogHTML);

                        $('#occupations-selection-dialog').dialog({
                            autoOpen: true,
                            width: 500,
                            height: 500,
                            modal: true,
                            dialogClass: 'btzc-v2-basic-dialog btzc-el-employee-tab-attribute-selection-dialog',
                            open: function () {
                                $('.occupation-selection-dialog-row').on('click', function() {
                                    $(this).toggleClass('checked-row');
                                    $(this).toggleClass('unchecked-row');
                                    const id = $(this).data('id');
                                    if (associatedOccupations.includes(id)) {
                                        const index = associatedOccupations.indexOf(id);
                                        associatedOccupations.splice(index, 1);
                                    } else {
                                        associatedOccupations.push(JSON.parse(id));
                                    }
                                    occupationsSection.data('array', JSON.stringify(associatedOccupations));
                                    occupationsSection.children().each((index, department) => {department.remove()})
                                    if (associatedOccupations.length === 0) {
                                        occupationsSection.append($('<p>Positionen</p>'))
                                    } else {
                                        associatedOccupations.forEach(occupation => {
                                            occupationsSection.append($('<p>' + (occupations.find(item => item.id === occupation.toString()))['occupation'] + '</p>'))
                                        })
                                    }
                                })
                            }, close: function () {
                                $('#occupations-selection-dialog').remove()
                            }
                        })
                    })
                }
            }
        })
    })


    employeeTileTable.on('click', '#btzc-el-employee-tab-tile-image', function() {
        const imagePreview = $(this);
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'btzc_el_get_employee_image_urls'
            },
            success: function(response) {
                if (response.success) {
                    Promise.all([localized('Please select a photo'), localized('Search')]).then(([dialogTitle, searchPlaceholder]) => {
                        const images = response.data;
                        let dialogHtml ='';
                        dialogHtml += '<div id="btzc-el-employee-tab-photo-selection-dialog" title="' + dialogTitle + '">';
                        dialogHtml += '  <div class="btzc-v2-basic-dialog-sticky-header">';
                        dialogHtml += '    <input type="text" id="image-dialog-search" class="btzc-v2-basic-text-field" placeholder="' + searchPlaceholder + '">';
                        dialogHtml += '  </div>';
                        dialogHtml += '  <div class="btzc-v2-basic-dialog-body">';
                        images.forEach(function(image) {
                            let fileName = image.substring(image.lastIndexOf('/') + 1);
                            fileName = fileName.substring(0, fileName.lastIndexOf('.'));
                            dialogHtml += '<div class="btzc-el-employee-tab-photo-selection-dialog-tile">'
                            dialogHtml += '<img class="btzc-el-employee-tab-photo-selection-dialog-thumbnail" src="' + image + '" alt="" />';
                            dialogHtml += '<p style="text-align: center">' + fileName + '</p>'
                            dialogHtml += '</div>'
                        });
                        dialogHtml += '  </div>';
                        dialogHtml += '</div>';

                        $('body').append(dialogHtml);
                        $('#image-dialog-search').on('keyup', searchInDialog);

                        $('#btzc-el-employee-tab-photo-selection-dialog').dialog({
                            autoOpen: true,
                            width: 715,
                            height: 500,
                            modal: true,
                            dialogClass: 'btzc-v2-basic-dialog btzc-el-employee-tab-photo-selection-dialog',
                            open: function() {
                                $('.btzc-el-employee-tab-photo-selection-dialog-thumbnail').on('click', function() {
                                    const imageURL = $(this).attr('src');
                                    imagePreview.attr('src', imageURL);
                                    $('#btzc-el-employee-tab-photo-selection-dialog').dialog('close');
                                });
                            },
                            close: function() {
                                $('#btzc-el-employee-tab-photo-selection-dialog').remove();
                            }
                        });
                    })

                }
            }
        });

        function searchInDialog() {
            let searchTerm = $(this).val();
            searchTerm = searchTerm.replace(/[äÄöÖüÜß]/g, c => AsciiReplacement[c]);
            if (searchTerm !== $(this).val) {
                $(this).val(searchTerm);
            }
            searchTerm = searchTerm.toLowerCase();
            $('.btzc-el-employee-tab-photo-selection-dialog-tile').each(function() {
                const tileText = $(this).find('p').text().toLowerCase();
                if (tileText.includes(searchTerm)) {
                    $(this).fadeIn(250);
                } else {
                    $(this).fadeOut(250);
                }
            })
        }
    })


    employeeTileTable.on('click', '.btzc-el-employee-tab-tile-gender', function () {
        let gender = $(this).text();
        $(this).hide();
        const genderSelection = $(this).parent().find('.btzc-el-employee-tab-tile-gender-selection');
        genderSelection.show();
        localized('genders').then(function(genders) {
            genderSelection.find('select').val(Object.keys(genders).find(key => genders[key] === gender));
            genderSelection.find('select').trigger('focus');
        })
    })






/**********************************************************
*                                                         *
*        CLICK-LISTENER FOR EMPLOYEE TILE LABELS          *
*                                                         *
**********************************************************/

    employeeTileTable.on('click', '.btzc-el-employee-tab-tile-gender-row .btzc-el-tile-row-label', function () {
        $(this).parent().find('.btzc-el-employee-tab-tile-gender').trigger('click');
    })

    employeeTileTable.on('click', '.btzc-el-employee-tab-tile-phone-number-element .btzc-el-tile-row-label', function () {
        $(this).parent().find('.btzc-el-employee-tab-tile-phone-number').trigger('focus');
    })

    employeeTileTable.on('click', '.btzc-el-employee-tab-tile-room-number-element .btzc-el-tile-row-label', function () {
        $(this).parent().find('.btzc-el-employee-tab-tile-room-number').trigger('focus');
    })

    employeeTileTable.on('click', '.btzc-el-employee-tab-tile-email-row .btzc-el-tile-row-label', function () {
        $(this).parent().find('.btzc-el-employee-tab-tile-email').trigger('focus');
    })

    employeeTileTable.on('click', '.btzc-el-employee-tab-tile-additional-information-row .btzc-el-tile-row-label', function () {
        $(this).parent().find('.btzc-el-employee-tab-tile-additional-information').trigger('focus');
    })






/**********************************************************
*                                                         *
*      FOCUS-OUT-LISTENER FOR EMPLOYEE TILE ELEMENTS      *
*                                                         *
**********************************************************/

    employeeTileTable.on('focusout', '.btzc-el-employee-tab-tile-first-name', function () {
        if ($(this).text().length === 0) {
            localized('[FIRST NAME]').then(translated => {
                $(this).text(translated);
            })
        }
    })

    employeeTileTable.on('focusout', '.btzc-el-employee-tab-tile-last-name', function () {
        if ($(this).text().length === 0) {
            localized('[LAST NAME]').then(translated => {
                $(this).text(translated);
            })
        }
    })

    employeeTileTable.on('focusout', '.btzc-el-employee-tab-tile-phone-number', function () {
        if ($(this).text().length === 0) {
            localized('[PHONE NUMBER]').then(translated => {
                $(this).text(translated);
            })
        }
    })

    employeeTileTable.on('focusout', '.btzc-el-employee-tab-tile-room-number', function () {
        if ($(this).text().length === 0) {
            localized('[OFFICE NUMBER]').then(translated => {
                $(this).text(translated);
            })
        }
    })

    employeeTileTable.on('focusout', '.btzc-el-employee-tab-tile-email', function () {
        if ($(this).text().length === 0) {
            localized('[EMAIL@EXAMPLE.COM]').then(translated => {
                $(this).text(translated);
            })
        }
    })

    employeeTileTable.on('focusout', '.btzc-el-employee-tab-tile-additional-information', function () {
        if ($(this).text().length === 0) {
            localized('[ADDITIONAL INFORMATION]').then(translated => {
                $(this).text(translated);
            })
        }
    })

    employeeTileTable.on('focusout', '.btzc-el-employee-tab-tile-gender-selection', function () {
        $(this).hide();
        const genderLabel = $(this).parent().find('.btzc-el-employee-tab-tile-gender')
        genderLabel.text($(this).find(':selected').text());
        genderLabel.show();
    })






/**********************************************************
*                                                         *
*        CLICK-LISTENER FOR EMPLOYEE TILE BUTTONS         *
*                                                         *
**********************************************************/

    employeeTileTable.on('click', '#employee-tab-button-save', function () {
        const editTile = $(this).parent().parent().parent();
        const formData = new FormData();
        localized('genders').then(function(genders) {
            formData.append('action', 'btzc_el_persist_employee');
            formData.append('employee_id', editTile.data('id'));
            formData.append('first_name', editTile.find('.btzc-el-employee-tab-tile-first-name').text());
            formData.append('last_name', editTile.find('.btzc-el-employee-tab-tile-last-name').text());
            formData.append('gender', Object.keys(genders).find(key => genders[key] === editTile.find('.btzc-el-employee-tab-tile-gender').text()));
            formData.append('image_url', editTile.find('#btzc-el-employee-tab-tile-image').attr('src'));
            formData.append('phone_number', editTile.find('.btzc-el-employee-tab-tile-phone-number').text());
            formData.append('room_number', editTile.find('.btzc-el-employee-tab-tile-room-number').text());
            formData.append('email_address', editTile.find('.btzc-el-employee-tab-tile-email').text());
            formData.append('departments', editTile.find('.btzc-el-employee-tab-tile-departments-section').data('array'));
            formData.append('occupations', editTile.find('.btzc-el-employee-tab-tile-occupations-section').data('array'));
            formData.append('information', editTile.find('.btzc-el-employee-tab-tile-additional-information').text());

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        createEmployeeBackEndTile(response.data).then(newTile => {
                            $(newTile).find('.btzc-v2-config-form-foot').slideUp(1, function () {});
                            $(editTile).remove();
                            const newEmployeeLastName = $(newTile).find('.btzc-el-employee-tab-tile-last-name').text();
                            let inserted = false;
                            for (let i = 0  ;  i < employeeTileTable.children().length  ;  i++) {
                                const lastName = $(employeeTileTable.children()[i]).find('.btzc-el-employee-tab-tile-last-name').text();
                                if (lastName.toLowerCase() > newEmployeeLastName.toLowerCase()) {
                                    if (i === 0) {
                                        employeeTileTable.prepend(newTile);
                                    } else {
                                        $(employeeTileTable.children()[i]).before(newTile);
                                    }
                                    insertTile(newTile)
                                    inserted = true;
                                    break;
                                }
                            }
                            if (!inserted) {
                                employeeTileTable.append(newTile);
                                insertTile(newTile);
                            }
                        })
                    }
                }
            })
        })


        function insertTile(newTile) {
            if (! ($(employeeTileTable).children()[0]).classList.contains('btzc-el-employee-tab-add-new-dataset-tile')) {
                createAddNewEmployeeBackEndTile().then(tile => {
                    employeeTileTable.prepend(tile);
                })
            }
            const top = Math.round($(newTile)[0].getBoundingClientRect().top);
            const pageHeaderBottom = $('.btzc-v2-page-header')[0].getBoundingClientRect().bottom;
            $(newTile).find('.btzc-el-employee-tab-tile-footer').slideUp(250, function () {});
            $(newTile).fadeOut(0);
            window.scrollBy({top: top - pageHeaderBottom, behavior: 'smooth'});
            $(newTile).fadeIn(400,);
        }
    })


    employeeTileTable.on('click', '#employee-tab-button-cancel', function (e) {
        e.stopPropagation();
        const tile = $(this).parent().parent().parent();
        const id = tile.data('id');
        if (id === 0) {
            createAddNewEmployeeBackEndTile().then(newTile => {
                $(newTile).slideUp(0, function () {});
                $(tile).slideUp(400, function () {
                    $(tile).replaceWith(newTile);
                    $(newTile).slideDown(300, function () {});
                });
            })
        } else {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {action: 'btzc_el_employee_single_dataset', employee_id: id},
                success: function (response) {
                    if (response.success) {
                        createEmployeeBackEndTile(response.data).then(newTile => {
                            tile.replaceWith(newTile);
                            $(newTile).find('.btzc-el-employee-tab-tile-footer').slideUp(250, function () {});
                        })
                    }
                }
            })
        }
    })


    employeeTileTable.on('click', '#employee-tab-button-delete', function (e) {
        e.preventDefault()
        const tile = $(this).parent().parent().parent();

        Promise.all([
            localized('Confirm deletion'),
            localized('Do you really want to delete this employee?'),
            localized('This action CANNOT be undone!'),
            localized('Delete'),
            localized('Cancel')
        ]).then(([dialogTitle, confirmMessage, warningMessage, deleteText, cancelText]) => {
            const $deleteConfirmDialog = $('<div></div>')
                .attr('id', 'delete-confirm-dialog')
                .append($('<p></p>').text(confirmMessage))
                .append($('<p></p>').text(warningMessage))
                .appendTo('body')
                .dialog({
                    autoOpen: false,
                    modal: true,
                    title: dialogTitle,
                    dialogClass: 'btzc-v2-basic-dialog delete-confirm-dialog',
                    resizable: false,
                    width: 380,
                    buttons: [
                        {  text:deleteText,
                            class: 'btzc-v2-basic-button btzc-v2-delete-button',
                            click: function() {
                                deleteEmployee(tile);
                                $(this).dialog('close');
                            }},
                        {  text: cancelText,
                            class: 'btzc-v2-basic-button btzc-v2-standard-button',
                            click: function() {
                                $(this).dialog('close');
                            }}
                    ]
                });
            $deleteConfirmDialog.dialog('open');
        })


        function deleteEmployee(employeeTile) {
            const id = employeeTile.data('id');
            const formData = new FormData();
            formData.append('action', 'btzc_el_delete_employee');
            formData.append('employee_id', id);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        $(employeeTile).slideUp(400, function () {$(employeeTile).remove()});
                    }
                }
            })
        }
    })






/**********************************************************
*                                                         *
*                  VARIOUS FUNCTIONALITY                  *
*                                                         *
**********************************************************/

    employeeTileTable.on('keydown', '.btzc-el-employee-tab-tile', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $(e.target).trigger('focusout');
            $($(this).find('#employee-tab-button-save')).trigger('click');
        }
        if (e.key === 'Escape') {
            $($(this).find('#employee-tab-button-cancel')).trigger('click');
        }
    })


    employeeTileTable.on('focus', 'p', function (e) {
        if ($(this)[0].isContentEditable) {
            window.getSelection().selectAllChildren(e.target);
        }
    })


    // PIN-Versand-Funktionalität
    const pinSendEmail = $('#btzc-el-pin-send-email');
    const pinSendButton = $('#btzc-el-pin-send-button');
    
    // PIN senden Button-Handler
    pinSendButton.on('click', function() {
        const email = pinSendEmail.val().trim();
        
        // Validierung
        if (!email) {
            showToastMessage('Bitte geben Sie eine E-Mail-Adresse ein', 3000, 'error');
            pinSendEmail.focus();
            return;
        }
        
        if (!isValidEmail(email)) {
            showToastMessage('Bitte geben Sie eine gültige E-Mail-Adresse ein', 3000, 'error');
            pinSendEmail.focus();
            return;
        }
        
        // Button deaktivieren während des Versands
        pinSendButton.prop('disabled', true).text('Wird gesendet...');
        
        // AJAX-Anfrage
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'btzc_el_send_pin_to_email',
                email: email
            },
            success: function(response) {
                if (response.success) {
                    showToastMessage(
                        `PIN erfolgreich an ${response.data.employee_name} (${email}) gesendet!`, 
                        5000, 
                        'success'
                    );
                    pinSendEmail.val(''); // Feld leeren
                } else {
                    showToastMessage(response.data.message, 4000, 'error');
                }
            },
            error: function(xhr, status, error) {
                showToastMessage('Fehler beim Versenden der PIN: ' + error, 4000, 'error');
            },
            complete: function() {
                // Button wieder aktivieren
                pinSendButton.prop('disabled', false).text('PIN senden');
            }
        });
    });
    
    // Enter-Taste im E-Mail-Feld
    pinSendEmail.on('keypress', function(e) {
        if (e.which === 13) { // Enter-Taste
            pinSendButton.click();
        }
    });
    
    // E-Mail-Validierungsfunktion
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Toast-Nachrichten anzeigen (falls nicht vorhanden)
    function showToastMessage(message, duration = 3000, type = 'info') {
        // Entfernen vorhandener Toast-Nachrichten
        $('.btzc-toast-message').remove();
        
        // Toast-Element erstellen
        const toast = $(`
            <div class="btzc-toast-message btzc-toast-${type}" style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
                color: white;
                padding: 15px 20px;
                border-radius: 4px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                z-index: 10000;
                max-width: 400px;
                font-size: 14px;
                line-height: 1.4;
            ">
                ${message}
            </div>
        `);
        
        // Toast hinzufügen und animieren
        $('body').append(toast);
        toast.hide().fadeIn(300);
        
        // Nach der angegebenen Zeit ausblenden
        setTimeout(() => {
            toast.fadeOut(300, () => toast.remove());
        }, duration);
    }
})