import {localized} from "../../language/backend-dictionary.mjs";

jQuery(function($) {

    const tileTable = $('#btzc-el-occupation-tile-table');

    $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: { action: 'btzc_el_occupation_data', },
        success: function(response) {
            if (response.success) {
                const occupations = response.data;
                createAddNewOccupationTile().then(addNewTile => {
                    tileTable.append(addNewTile);
                    occupations.forEach(occupation => {
                        createOccupationTile(occupation).then(tile => {
                            tileTable.append(tile);
                        })
                    })
                })
            }
        }
    })




    function createAddNewOccupationTile() {
        return Promise.all([
            localized('Occupation (gendered)'),
            localized('Male form'),
            localized('Female form'),
            localized('Diverse form'),
            localized('Save'),
            localized('Cancel')
        ]).then(([occupation, maleForm, femaleForm, diverseForm, saveButton, cancelButton]) => {
            let tileHTML = `<div class="btzc-el-occupation-tab-add-new-dataset-tile" data-id="0">
              <div class="btzc-el-occupation-row">
                <p class="occupation occupation-generic" contenteditable="true"></p>
                <p class="occupation occupation-male-form" contenteditable="true"></p>
              </div>
              <div class="btzc-el-occupation-row">
                <p class="occupation occupation-female-form" contenteditable="true"></p>
                <p class="occupation occupation-diverse-form" contenteditable="true"></p>
              </div>
              <div class="btzc-el-occupation-tab-add-new-dataset-footer">
                <button class="btzc-v2-basic-button btzc-v2-standard-button" id="new-occupation-tab-button-save"></button>
                <button class="btzc-v2-basic-button btzc-v2-delete-button btzc-el-cancel-button" id="new-occupation-tab-button-cancel"></button>
              </div>
            </div>`
            const tile = $.parseHTML(tileHTML)[0];
            $(tile).find('.occupation-generic').text(occupation);
            $(tile).find('.occupation-male-form').text(maleForm);
            $(tile).find('.occupation-female-form').text(femaleForm);
            $(tile).find('.occupation-diverse-form').text(diverseForm);
            $(tile).find('#new-occupation-tab-button-save').text(saveButton);
            $(tile).find('#new-occupation-tab-button-cancel').text(cancelButton);
            $(tile).find('.btzc-el-occupation-tab-add-new-dataset-footer').slideUp(1, function (){});
            return tile;
        })
    }


    function createOccupationTile(data) {
        return Promise.all([
            localized('Male form'),
            localized('Female form'),
            localized('Diverse form'),
            localized('Save'),
            localized('Cancel'),
            localized('Delete')
        ]).then(([maleForm, femaleForm, diverseForm, saveButton, cancelButton, deleteButton]) =>{
            const id = data['id'].toString();
            const occupation = data['occupation'];
            const occupationMaleForm = data['male_form'];
            const occupationFemaleForm = data['female_form'];
            const occupationDiverseForm = data['diverse_form'];

            let html = '';
            html += '<div class="btzc-el-occupation-tab-tile" data-id="' + id + '">'
            html += '<div class="btzc-el-occupation-row">';
            html += '  <p class="occupation occupation-generic" contenteditable="true">' + occupation + '</p>';
            if (occupationMaleForm === '') {
                html += '  <p class="occupation occupation-male-form btzc-el-occupation-undefined-form" contenteditable="true">' + maleForm + '</p>';
            } else {
                html += '  <p class="occupation occupation-male-form" contenteditable="true">' + occupationMaleForm + '</p>';
            }
            html += '</div>';
            html += '<div class="btzc-el-occupation-row">';
            if (occupationFemaleForm === '') {
                html += '  <p class="occupation occupation-female-form btzc-el-occupation-undefined-form" contenteditable="true">' + femaleForm + '</p>';
            } else {
                html += '  <p class="occupation occupation-female-form" contenteditable="true">' + occupationFemaleForm + '</p>';
            }
            if (occupationDiverseForm === '') {
                html += '  <p class="occupation occupation-diverse-form btzc-el-occupation-undefined-form" contenteditable="true">' + diverseForm + '</p>';
            } else {
                html += '  <p class="occupation occupation-diverse-form" contenteditable="true">' + occupationDiverseForm + '</p>';
            }
            html += '</div>';
            html += '  <div class="btzc-el-occupation-tab-tile-footer">'
            html += '    <button class="btzc-v2-basic-button btzc-v2-standard-button" id="occupation-tab-button-save"></button>';
            html += '    <button class="btzc-v2-basic-button btzc-v2-delete-button btzc-el-cancel-button" id="occupation-tab-button-cancel"></button>';
            html += '    <button class="btzc-v2-basic-button btzc-v2-delete-button" id="occupation-tab-button-delete"></button>';
            html += '  </div>'
            html += '</div>'
            const tile = $.parseHTML(html)[0];
            $(tile).find('#occupation-tab-button-save').text(saveButton);
            $(tile).find('#occupation-tab-button-cancel').text(cancelButton);
            $(tile).find('#occupation-tab-button-delete').text(deleteButton);
            $(tile).find('.btzc-el-occupation-tab-tile-footer').slideUp(1, function (){});
            return tile;
        })
    }




    tileTable.on('click', '.btzc-el-occupation-tab-add-new-dataset-tile', function () {
        $(this).css('background-color', getComputedStyle(document.body).getPropertyValue('--btzc-v2-table-element-bg'));
        $(this).css('color', getComputedStyle(document.body).getPropertyValue('--btzc-v2-table-color'));
        $(this).find('.btzc-el-occupation-tab-add-new-dataset-footer').slideDown(250, function (){});
    })


    tileTable.on('click', '.btzc-el-occupation-tab-tile', function () {
        $(this).find('.btzc-el-occupation-tab-tile-footer').slideDown(250, function (){});
    })




    tileTable.on('click', '#new-occupation-tab-button-save', function () {
        const tile = $(this).parent().parent();
        saveOccupation(tile, $, createOccupationTile, tileTable, createAddNewOccupationTile);
    })


    tileTable.on('click', '#occupation-tab-button-save', function () {
        const tile = $(this).parent().parent();
        saveOccupation(tile, $, createOccupationTile, tileTable, createAddNewOccupationTile);
    })


    function saveOccupation(tile, $, createOccupationTile, tileTable, createAddNewOccupationTile) {
        Promise.all([
            localized('Male form'),
            localized('Female form'),
            localized('Diverse form')
        ]).then(([maleForm, femaleForm, diverseForm]) => {
            const id = tile.data('id');
            const occupation = tile.find('.occupation-generic').text();
            const occupation_male = tile.find('.occupation-male-form').text();
            const occupation_female = tile.find('.occupation-female-form').text();
            const occupation_diverse = tile.find('.occupation-diverse-form').text();
            const formData = new FormData();

            formData.append('action', 'btzc_el_persist_occupation');
            formData.append('occupation_id', id);
            formData.append('occupation', occupation);
            formData.append('occupation_male_form', occupation_male !== maleForm ? occupation_male : '');
            formData.append('occupation_female_form', occupation_female !== femaleForm ? occupation_female : '');
            formData.append('occupation_diverse_form', occupation_diverse !== diverseForm ? occupation_diverse : '');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        $('.btzc-el-occupation-tab-add-new-dataset-tile').remove()
                        createOccupationTile(response.data).then(newTile => {
                            $(newTile).slideUp(1, function () {});
                            $(tile).remove();
                            const newOccupation = $(newTile).find('.occupation').text();
                            let inserted = false;
                            for (let i = 0; i < tileTable.children().length; i++) {
                                const occ = $(tileTable.children()[i]).find('.occupation').text();
                                if (occ.toLowerCase() > newOccupation.toLowerCase()) {
                                    if (i === 0) {
                                        tileTable.prepend(newTile);
                                    } else {
                                        $(tileTable.children()[i]).before(newTile);
                                    }
                                    inserted = true;
                                    $(newTile).slideDown(500, function () {
                                    });
                                    break;
                                }
                            }
                            if (!inserted) {
                                tileTable.append(newTile);
                                $(newTile).slideDown(500, function () {
                                });
                            }
                            createAddNewOccupationTile().then(addNewTile => {
                                tileTable.prepend(addNewTile);
                            })
                        })
                    }
                }
            })
        });
    }




    tileTable.on('click', '#new-occupation-tab-button-cancel', function () {
        const tile = $(this).parent().parent();
        createAddNewOccupationTile().then(newTile => {
            $(tile).find('.btzc-el-occupation-tab-add-new-dataset-footer').slideUp(250, function (){
                tile.replaceWith(newTile);
            });
        })
    })


    tileTable.on('click', '#occupation-tab-button-cancel', function () {
        const tile = $(this).parent().parent();
        const id = tile.data('id');
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {action: 'btzc_el_occupation_single_dataset', occupation_id: id},
            success: function (response) {
                if (response.success) {
                    $(tile).find('.btzc-el-occupation-tab-tile-footer').slideUp(250, function (){
                        createOccupationTile(response.data).then(newTile => {
                            tile.replaceWith(newTile);
                        })
                    });
                }
            }
        })
    })




    tileTable.on('click', '#occupation-tab-button-delete', function (e) {
        e.preventDefault();
        const tile = $(this).parent().parent();
        Promise.all([
            localized('Confirm deletion'),
            localized('Do you really want to delete this position?'),
            localized('This action CANNOT be undone!'),
            localized('Cancel'),
            localized('Delete')
        ]).then(([dialogTitle, confirmMessage, warningMessage, cancelButton, deleteButton]) => {
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
                        {  text: deleteButton,
                            class: 'btzc-v2-basic-button btzc-v2-delete-button',
                            click: function() {
                                deleteOccupation(tile);
                                $(this).dialog('close');
                            }},
                        {  text: cancelButton,
                            class: 'btzc-v2-basic-button btzc-v2-standard-button',
                            click: function() {
                                $(this).dialog('close');
                            }}
                    ]
                });
            $deleteConfirmDialog.dialog('open');
        })


        function deleteOccupation(occupationTile) {
            const formData = new FormData();
            formData.append('action', 'btzc_el_delete_occupation');
            formData.append('occupation_id', occupationTile.data('id'));

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        $(occupationTile).slideUp(250, function (){
                            $(occupationTile).remove();
                        })
                    }
                }
            })
        }
    })




    tileTable.on('focusout', '.occupation-generic', function () {
        if ($(this).text() === '') {
            localized('Occupation (gendered)').then(occupation => {
                $(this).prop('innerHTML', occupation);
                $(this)[0].classList.add('btzc-el-occupation-undefined-form');
            })
        }
    })


    tileTable.on('focusout', '.occupation-male-form', function () {
        if ($(this).text() === '') {
            localized('Male form').then(maleForm => {
                $(this).prop('innerHTML', maleForm);
                $(this)[0].classList.add('btzc-el-occupation-undefined-form');
            })
        }
    })


    tileTable.on('focusout', '.occupation-female-form', function () {
        if ($(this).text() === '') {
            localized('Female form').then(femaleForm => {
                $(this).prop('innerHTML', femaleForm);
                $(this)[0].classList.add('btzc-el-occupation-undefined-form');
            })
        }
    })


    tileTable.on('focusout', '.occupation-diverse-form', function () {
        if ($(this).text() === '') {
            localized('Diverse form').then(diverseForm => {
                $(this).prop('innerHTML', diverseForm);
                $(this)[0].classList.add('btzc-el-occupation-undefined-form');
            })
        }
    })




    tileTable.on('focus', 'p', function (e) {
        if ($(this)[0].classList.contains('btzc-el-occupation-undefined-form')) {
            $(this).empty();
            $(this)[0].classList.remove('btzc-el-occupation-undefined-form');
        }
        if ($(this)[0].isContentEditable) {
            window.getSelection().selectAllChildren(e.target)
            $(this).parent().find('.btzc-el-occupation-tab-tile-footer').slideDown(250, function (){});
        }
    })




    tileTable.on('keydown', '.occupation', function (e) {
        const tile = $(this).parent().parent();
        if (e.key === 'Enter') {
            e.preventDefault();
            localized('Occupation (gendered)').then(occupation => {
                const requiredFieldText = $(tile).find('.occupation-generic').text();
                if (requiredFieldText !== ''  &&  requiredFieldText !== occupation) {
                    $($(tile).find('.btzc-v2-standard-button')).trigger('click');
                }
            })
        }
        if (e.key === 'Escape') {
            $($(tile).find('.btzc-el-cancel-button')).trigger('click');
        }
    })

})