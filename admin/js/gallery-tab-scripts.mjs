/**
 * Backend
 *
 * This file contains the jQuery-code for the gallery tab (backend).
 *
 * @since 2.0.0
 */

import {localized} from "../../language/backend-dictionary.mjs";

jQuery(function($) {
    const ASCII = {'ä':'ae', 'Ä':'Ae', 'ö':'oe', 'Ö':'Oe', 'ü':'ue', 'Ü':'Ue', 'ß':'ss'};

    const container = $('#btzc-el-gallery-image-container');
    const deleteButton = $('#btzc-el-employee-photo-delete-selected');
    const selectAllButton = $('#btzc-el-employee-photo-select-all');
    const searchField = $('#btzc-el-employee-photo-search');
    const uploadButton = $('#btzc-el-employee-photo-add-new');
    const fileInput = $('#btzc-el-employee-photo-upload');

    (async() => deleteButton.prop('innerHTML', await localized('Delete')))();
    (async() => selectAllButton.prop('innerHTML', await localized('Select all')))();
    (async() => uploadButton.prop('innerHTML', await localized('Upload images')))();
    (async() => searchField.prop('placeholder', await localized('Search')))();

    $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: { action: 'btzc_el_get_employee_image_urls', },
        success: function(response) {
            if (response.success) {
                const imageURLs = response.data;
                imageURLs.forEach(imageURL => {
                    const imageTile = createGalleryImageTile(imageURL);
                    container.append(imageTile);
                })

            }
        }
    })


    function createGalleryImageTile(url) {
        let html = '';
        html += '<div class="btzc-el-gallery-photo-tile">'
        html += '  <div class="btzc-el-gallery-photo-tile-header">';
        html += '    <p>' + url.substring(url.lastIndexOf('/') + 1) + '</p>';
        html += '  </div>';
        html += '  <div class="btzc_el_gallery-photo-tile-body">';
        html += '    <img src="' + url + '" alt="Bild">';
        html += '  </div>';
        html += '</div>';
        return $.parseHTML(html)[0];
    }




    container.on('click', '.btzc-el-gallery-photo-tile', function () {
        Promise.all([localized('Select all'), localized('Deselect all')]).then(([selectAll, deselectAll]) =>{
            if ($(this)[0].classList.contains('btzc-el-gallery-photo-tile-ready-to-remove')) {
                $(this)[0].classList.remove('btzc-el-gallery-photo-tile-ready-to-remove');
            } else {
                $(this)[0].classList.add('btzc-el-gallery-photo-tile-ready-to-remove');
            }
            if ($('.btzc-el-gallery-photo-tile-ready-to-remove').length > 0) {
                $('#btzc-el-employee-photo-delete-selected').prop('disabled', false);
                $('#btzc-el-employee-photo-select-all').text(deselectAll)
            } else {
                $('#btzc-el-employee-photo-delete-selected').prop('disabled', true);
                $('#btzc-el-employee-photo-select-all').text(selectAll)
            }
        });
    })




     deleteButton.on('click', function(e) {
         e.preventDefault();
         Promise.all([
             localized('Confirm deletion'),
             localized('Do you really want to delete the selected photo(s)?'),
             localized('This action CANNOT be undone!'),
             localized('Delete'),
             localized('Cancel')
         ]).then(([dialogTitle, confirmMessage, warningMessage, deleteButton, cancelButton]) =>{
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
                         {  text:deleteButton,
                             class: 'btzc-v2-basic-button btzc-v2-delete-button',
                             click: function() {
                                 deleteImages();
                                 $(this).dialog('close');
                                 $(selectAllButton).trigger('click');
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


         function deleteImages() {
             const selection = container.find('.btzc-el-gallery-photo-tile-ready-to-remove');
             for (let i = 0; i < selection.length; i++) {
                 const imageTile = $(selection[i]);
                 const imageURL = imageTile.find('img').attr('src');
                 const formData = new FormData();
                 formData.append('action', 'btzc_el_delete_employee_image');
                 formData.append('image_url', imageURL);
                 $.ajax({
                     url: ajaxurl,
                     type: 'POST',
                     data: formData,
                     contentType: false,
                     processData: false,
                     success: function (response) {
                         if (response.success) {
                             imageTile.fadeOut(250, function () {imageTile.remove();})
                             deleteButton.prop('disabled', true);
                         }
                     }
                 });
             }
         }
    });




    uploadButton.on('click', function () {
        fileInput.trigger('click');
    })


    fileInput.on('change', function () {
        const files = this.files;
        if (files.length === 0) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'btzc_el_upload_employee_image');
        for (let i = 0; i < files.length; i++) {
            formData.append('btzc-el-employee-photo-upload[]', files[i]);
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    const imageURLs = response.data;
                    imageURLs.forEach(imageURL => {
                        const imageTile = createGalleryImageTile(imageURL);
                        $(imageTile).fadeOut(0);
                        const newFileName = imageURL.substring(imageURL.lastIndexOf('/') + 1);
                        let inserted = false;
                        for (let i = 0  ;  i < container.children().length  ;  i++) {
                            const fileName = $(container.children()[i]).find('p').text();
                            if (fileName.toLowerCase() > newFileName.toLowerCase()) {
                                if (i === 0) {
                                    container.prepend(imageTile);
                                } else {
                                    $(container.children()[i]).before(imageTile);
                                }
                                inserted = true;
                                break;
                            }
                        }
                        if (!inserted) {
                            container.append(imageTile);
                        }
                        $(imageTile).fadeIn(250);
                    })
                }
            }
        });
    })




    selectAllButton.on('click', function () {
        Promise.all([
            localized('Select all'),
            localized('Deselect all')
        ]).then(([selectAll, deselectAll]) =>{
            const selectable = container.find('.btzc-el-gallery-photo-tile');
            if ($('.btzc-el-gallery-photo-tile-ready-to-remove').length > 0) {
                for (let i = 0  ;  i < selectable.length; i++) {
                    $(selectable[i])[0].classList.remove('btzc-el-gallery-photo-tile-ready-to-remove');
                    $(this).text(selectAll);
                    $('#btzc-el-employee-photo-delete-selected').prop('disabled', true);
                }
            } else {
                for (let i = 0; i < selectable.length; i++) {
                    $(selectable[i])[0].classList.add('btzc-el-gallery-photo-tile-ready-to-remove');
                    $(this).text(deselectAll);
                    $('#btzc-el-employee-photo-delete-selected').prop('disabled', false);
                }
            }
        })
    })




    searchField.on('keyup', function () {
        let searchTerm = $(this).val();
        searchTerm = searchTerm.replace(/[äÄöÖüÜß]/g, c => ASCII[c]);
        if (searchTerm !== $(this).val) {
            $(this).val(searchTerm);
        }
        searchTerm = searchTerm.toLowerCase();
        container.children().each(function (index, child) {
            const imageName = $(child).find('p').text();
            if (imageName.toLowerCase().includes(searchTerm.toLowerCase())) {
                $(child).fadeIn(250);
            } else {
                $(child).fadeOut(250);
            }
        })
    })

})