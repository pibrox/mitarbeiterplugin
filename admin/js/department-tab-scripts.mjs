import {localized} from "../../language/backend-dictionary.mjs";
/**
 * Backend
 *
 * This file contains the jQuery-code for the department tab (backend).
 *
 * @since 2.0.0
 */
jQuery(function($) {

    /**
     * tileTable is a jQuery object representing the HTML element with the ID 'btzc-el-department-tile-table'.
     * This object provides methods to manipulate, traverse, and interact with the DOM element.
     *
     * @since 2.0.0
     */
    const tileTable = $('#btzc-el-department-tile-table');


    createAddNewDepartmentTile().then(tile => {
        tileTable.prepend(tile);
    })
    /**
     * Loads the departments and generates a list.
     */
    $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: { action: 'btzc_el_department_data', },
        success: function(response) {
            if (response.success) {
                const departments = response.data;
                departments.forEach(department => {
                    const tile = createDepartmentTile(department);
                    tileTable.append(tile);
                })
            }
       }
   })


    /**
     * Creates and returns a new department tile element for adding a new department.
     * The returned HTML element includes editable content and buttons for saving or canceling the addition of a new department.
     *
     * @return The DOM element representing the new department tile.
     *
     * @since 2.0.0
     */
    function createAddNewDepartmentTile() {
        return Promise.all([
            localized('Add new department'),
            localized('Save'),
            localized('Cancel')
        ]).then(translations => {
            let html = `<div class="btzc-el-department-tab-add-new-dataset-tile" data-id="0">
              <p class="department" contenteditable="true"></p>
              <div class="btzc-el-department-tab-add-new-dataset-tile-footer">
                <button class="btzc-v2-basic-button btzc-v2-standard-button" id="new-department-tab-button-save"></button>
                <button class="btzc-v2-basic-button btzc-v2-delete-button btzc-el-cancel-button" id="new-department-tab-button-cancel"></button>
              </div>
            </div>`;
            const tile = $.parseHTML(html)[0];
            $(tile).find('.btzc-el-department-tab-add-new-dataset-tile-footer').slideUp(0);
            $(tile).find('.department').prop('innerHTML', translations[0]);
            $(tile).find('#new-department-tab-button-save').prop('innerHTML', translations[1]);
            $(tile).find('#new-department-tab-button-cancel').prop('innerHTML', translations[2]);
            return tile;
        });
    }


    /**
     * Creates a department tile element with the provided data.
     *
     * @param {Object} data - The data object used to create the department tile.
     * @param {string|number} data.id - The unique identifier for the department tile.
     * @param {string} data.department - The department name or label to display in the tile.
     *
     * @return {Element | Text | Comment | Document | DocumentFragment} The constructed HTML element representing a department tile.
     *
     * @since 2.0.0
     */
    function createDepartmentTile(data) {
        const id = data['id'].toString();
        const department = data['department'];

        let html = `<div class="btzc-el-department-tab-tile" data-id="">
              <p class="department" contenteditable="true"></p>
              <div class="btzc-el-department-tab-tile-footer">
                <button class="btzc-v2-basic-button btzc-v2-standard-button" id="department-tab-button-save"></button>
                <button class="btzc-v2-basic-button btzc-v2-delete-button btzc-el-cancel-button" id="department-tab-button-cancel"></button>
                <button class="btzc-v2-basic-button btzc-v2-delete-button" id="department-tab-button-delete"></button>
              </div>
            </div>`;

        const tile = $.parseHTML(html)[0];
        $(tile).find('.btzc-el-department-tab-tile-footer').slideUp(0);
        $(tile).data('id', id);
        $(tile).find('.department').prop('innerHTML', department);

        Promise.all([localized('Save'), localized('Cancel'), localized('Delete')]).then(translations => {
            $(tile).find('#department-tab-button-save').prop('innerHTML', translations[0]);
            $(tile).find('#department-tab-button-cancel').prop('innerHTML', translations[1]);
            $(tile).find('#department-tab-button-delete').prop('innerHTML', translations[2]);
        })

        return tile;
    }


    /**
     * Click-listener for the add-new-dataset-tile. The add-new-dataset-tile is replaced by an empty dataset-tile.
     *
     * @return void
     *
     * @since 2.0.0
     */
    tileTable.on('click', '.btzc-el-department-tab-add-new-dataset-tile', function () {
        $(this).css('background-color', getComputedStyle(document.body).getPropertyValue('--btzc-v2-table-element-bg'));
        $(this).css('color', getComputedStyle(document.body).getPropertyValue('--btzc-v2-table-color'));
        $(this).find('.btzc-el-department-tab-add-new-dataset-tile-footer').slideDown(250);
    })


    /**
     * Click-listener for the department tiles. In edit-mode the tile-footer, containing save-, cancel- and
     * delete-buttons is displayed.
     *
     * @since 2.0.0
     */
    tileTable.on('click', '.btzc-el-department-tab-tile', function () {
        $(this).find('.btzc-el-department-tab-tile-footer').slideDown(250);
    })


    /**
     * Click-listener for the NEW department-tab save-button. Calls the saveDepartment-method.
     *
     * @since 2.0.0
     */
    tileTable.on('click', '#new-department-tab-button-save', function () {
        const tile = $(this).parent().parent();
        saveDepartment(tile, $, createDepartmentTile, tileTable, createAddNewDepartmentTile);
    })


    /**
     * Click-listener for the department-tab save-button. Calls the saveDepartment-method.
     *
     * @since 2.0.0
     */
    tileTable.on('click', '#department-tab-button-save', function () {
        const tile = $(this).parent().parent();
        saveDepartment(tile, $, createDepartmentTile, tileTable, createAddNewDepartmentTile);
    })


    /**
     * Saves the department data by sending an AJAX request to persist the department information
     * and updates the UI with the new department data.
     *
     * @param {Object} tile - The tile element containing department data.
     * @param {Object} $ - The jQuery library instance.
     * @param {Function} createDepartmentTile - Function to create a new department tile DOM element.
     * @param {Object} tileTable - The DOM element representing the table or container for department tiles.
     * @param {Function} createAddNewDepartmentTile - Function to generate the "Add New Department" tile.
     *
     * @return {void} This function does not return a value.
     *
     * @since 2.0.0
     */
    function saveDepartment(tile, $, createDepartmentTile, tileTable, createAddNewDepartmentTile) {
        const id = tile.data('id');
        const department = tile.find('.department').text();
        const formData = new FormData();
        formData.append('action', 'btzc_el_persist_department');
        formData.append('department_id', id);
        formData.append('department_name', department);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    $('.btzc-el-department-tab-add-new-dataset-tile').remove()
                    let newTile = createDepartmentTile(response.data);
                    $(newTile).slideUp(0);
                    $(tile).remove();
                    const newDepartment = $(newTile).find('.department').text();
                    let inserted = false;
                    for (let i = 0; i < tileTable.children().length; i++) {
                        const dep = $(tileTable.children()[i]).find('.department').text();
                        if (dep.toLowerCase() > newDepartment.toLowerCase()) {
                            if (i === 0) {
                                tileTable.prepend(newTile);
                            } else {
                                $(tileTable.children()[i]).before(newTile);
                            }
                            inserted = true;
                            $(newTile).slideDown(500);
                            break;
                        }
                    }
                    if (!inserted) {
                        tileTable.append(newTile);
                        $(newTile).slideDown(500);
                    }
                    createAddNewDepartmentTile().then(tile => {
                        tileTable.prepend(tile);
                    })
                }
            }
        })
    }


    /**
     * Click-listener for the new department-tab cancel-button. Replaces the (altered) department-tab with a new one.
     *
     * @since 2.0.0
     */
    tileTable.on('click', '#new-department-tab-button-cancel', function () {
        const tile = $(this).parent().parent();
        const newTile = createAddNewDepartmentTile();
        $(tile).find('.btzc-el-department-tab-add-new-dataset-tile-footer').slideUp(250, function (){
            tile.replaceWith(newTile);
        });
    })


    /**
     * Click-listener for the department-tab cancel-button. Replaces the (altered) department-tab with a new on
     * created from the original data (retrieved by an AJAX request).
     *
     * @since 2.0.0
     */
    tileTable.on('click', '#department-tab-button-cancel', function () {
        const tile = $(this).parent().parent();
        const id = tile.data('id');
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {action: 'btzc_el_department_single_dataset', department_id: id},
            success: function (response) {
                if (response.success) {
                    $(tile).find('.btzc-el-department-tab-tile-footer').slideUp(250, function (){
                        const newTile = createDepartmentTile(response.data);
                        tile.replaceWith(newTile);
                    });
                }
            }
        })
    })


    /**
     * Click-listener for the department-tab delete-button. Opens a confirm-delete-dialog. In case the user
     * confirms deletion, an AJAX request for deletion of the department is sent.
     *
     * @since 2.0.0
     */
    tileTable.on('click', '#department-tab-button-delete', function (e) {
        e.preventDefault();
        const tile = $(this).parent().parent();
        const $deleteConfirmDialog = $('<div></div>').attr('id', 'delete-confirm-dialog');
        Promise.all([
            localized('Confirm deletion'),
            localized('Do you really want to delete this department?'),
            localized('This action CANNOT be undone!'),
            localized('Delete'),
            localized('Cancel')
        ]).then(([dialogTitle, confirmMessage, warningMessage, deleteText, cancelText]) => {
            $deleteConfirmDialog.append($('<p></p>').text(confirmMessage))
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
                                deleteDepartment(tile);
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
        });
    });


    /**
     * Deletes a department based on the provided department tile.
     *
     * @param {jQuery} departmentTile - A jQuery object representing the department tile to be deleted. It should
     *                                  contain a data attribute `id` with the department's identifier.

     * @return {void} This function does not return a value, but performs an AJAX call to delete the department
     *                  and updates the UI accordingly.
     *
     * @since 2.0.0
     */
    function deleteDepartment(departmentTile) {
        const formData = new FormData();
        formData.append('action', 'btzc_el_delete_department');
        formData.append('department_id', departmentTile.data('id'));

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    $(departmentTile).slideUp(250, function (){
                        $(departmentTile).remove();
                    })
                }
            }
        })
    }


    /**
     * Focus-listener for the paragraph-elements selecting the text-content when focus is gained.
     *
     * @since 2.0.0
     */
    tileTable.on('focus', 'p', function (e) {
        if ($(this)[0].isContentEditable) {
            window.getSelection().selectAllChildren(e.target)
            $(this).parent().find('.btzc-el-department-tab-tile-footer').slideDown(250);
        }
    })

    tileTable.on('focusout', 'p', function () {
        const tile = $(this).parent();
        if ($(this)[0].isContentEditable) {
            localized('Add new department').then(translations => {
                const requiredFieldText = $(tile).find('.department');
                if (requiredFieldText.text() === ''   ||   requiredFieldText.text() === translations) {
                    requiredFieldText.text(translations);
                }
            })
        }
    })

    /**
     * Key-listener for the handling Enter (confirm) and Escape (cancel) actions while editing.
     *
     * @since 2.0.0
     */
    tileTable.on('keydown', '.department', function (e) {
        const tile = $(this).parent();
        if (e.key === 'Enter') {
            e.preventDefault();
            localized('Add new department').then(translations => {
                const requiredFieldText = $(tile).find('.department');
                if (requiredFieldText.text() === ''   ||   requiredFieldText.text() === translations) {
                    requiredFieldText.text(translations);
                }
                $($(tile).find('.btzc-v2-standard-button')).trigger('click');
            })
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            $($(tile).find('.btzc-el-cancel-button')).trigger('click');
        }
    })

})