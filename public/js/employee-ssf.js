jQuery(/**
 * Initializes and manages various interactions and functionality for a self-service form, including department/occupation management,
 * image upload, backend data persistence, and other form-related features.

 * @param {jQuery} $ - A reference to the jQuery object for DOM manipulation and event handling.
 */
function($) {
    const emailRegex = /^([a-zA-Z0-9_.\-+])+@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    let image = $('#btzc-el-self-service-form-image');
    let imageFileUpload = $('#btzc-el-self-service-form-image-file');
    let imageUploadButton = $('#btzc-el-self-service-button-fileupload');
    let imageTemplateButton = $('#btzc-el-self-service-button-template');
    let placeholderImageUrl = image.data('default');

    let departmentContainer = $('#btzc-el-self-service-department-selection-container');
    let occupationContainer = $('#btzc-el-self-service-occupation-selection-container');

    let wordpressAccessCheckbox = $('#btzc-el-self-service-wordpress-account-checkbox');
    let wordpressAccessContainer = $('#btzc-el-self-service-wordpress-account-data-container');
    let wordpressAccessUsername = $('#btzc-el-self-service-username');
    let wordpressAccessPassword = $('#btzc-el-self-service-password');

    let saveDataButton = $('#btzc-el-self-service-button-submit');

    let forgotPin = $('#btzc-el-edit-data-button-forgot-pin');
    let usernameInput = $('#btzc-el-edit-data-username');
    let passwordInput = $('#btzc-el-edit-data-password');


    /**
     * Sends a request to the backend to reset the user's PIN.'
     */
    forgotPin.on('click', function () {
        if (usernameInput.val() === '') {
            if (! usernameInput.hasClass('is-invalid')) {
                usernameInput.addClass('is-invalid');
                $('#btzc-el-edit-data-info-no-username').show()
                usernameInput.trigger('focus');
            }
            return;
        }
        let username = usernameInput.val().toString();
        if (username.includes('@')) {
            username = username.split('@')[0];
        }

        const formData = new FormData();
        formData.append('action', 'btzc_el_reset_pin');
        formData.append('btzc-el-reset-pin-username', username);
        $.ajax ({
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function () {
                alert("Ihr neuer Pin wurde an Ihre E-Mail Adresse gesendet.")
                if (usernameInput.hasClass('is-invalid')) {
                    usernameInput.removeClass('is-invalid');
                    $('#btzc-el-edit-data-info-no-username').hide()
                    passwordInput.trigger('focus');
                }
            },
            error: function () {
                alert("Es ist ein interner Fehler aufgetreten.\nBitte wenden Sie sich an das Wiki-Team.")
            }
        });
    })






    wordpressAccessContainer.slideUp(0);


    /**
     * Adds a new department selection element when the user clicks the "+" button.
     */
    departmentContainer.on('input', '.btzc-el-self-service-department-selection-element', function () {
        if (this === departmentContainer.children().last().get(0)) {
            departmentContainer.append(departmentContainer.children().last().clone())
        } else {
            this.value === 'undefined'? $(this).remove(): '';
        }
    })


    /**
     * Adds a new occupation selection element when the user clicks the "+" button.
     */
    occupationContainer.on('input', '.btzc-el-self-service-occupation-selection-element', function () {
        if (this === occupationContainer.children().last().get(0)) {
            occupationContainer.append(occupationContainer.children().last().clone())
        } else {
            this.value === 'undefined'? $(this).remove(): '';
        }
    })


    /**
     * Opens the file upload dialog when the user clicks the upload button.
     */
    imageUploadButton.on('click', function () {
        imageFileUpload.trigger('click');
    })


    /**
     * Uploads the selected file to the server and displays it in the image element.
     */
    imageFileUpload.on('change', function () {
        const filesize = this.files[0].size;
        if (filesize < 5 * 1024 * 1024) {
            image.attr('src', URL.createObjectURL(this.files[0]));
        } else {
            alert("Die Bilddatei darf eine größe von 5MB nicht übersteigen!")
            this.val(null)
        }
    })


    /**
     * Resets the image element to the default placeholder image.
     */
    imageTemplateButton.on('click', function (e) {
         imageFileUpload.val(null)
        image.attr('src', placeholderImageUrl);
    })


    /**
     * Shows or hides the wordpress access data container when the checkbox is toggled.
     */
    wordpressAccessCheckbox.on('change', function () {
        if (wordpressAccessCheckbox.is(':checked')) {
            wordpressAccessContainer.slideDown(500);
            $('#btzc-el-self-service-password').trigger('focus');
        } else {
            wordpressAccessContainer.slideUp(500);
        }
    })


    /**
     * Sends a request to the backend to save the provided data.
     */
    saveDataButton.on('click', function () {
        let firstName = $('#btzc-el-self-service-firstname');
        let lastName = $('#btzc-el-self-service-lastname');
        let email = $('#btzc-el-self-service-email-address');

        let validEmail  = emailRegex.test(email.val().toLowerCase());

        if (firstName.val() === '' ||  lastName.val() === ''  ||  !validEmail) {
            if (firstName.val() === '') {
                firstName.addClass('is-invalid')
            } else {
                firstName.removeClass('is-invalid');
            }
            if (lastName.val() === '') {
                lastName.addClass('is-invalid')
            }
            else {
                lastName.removeClass('is-invalid');
            }
            if (!validEmail) {
                email.addClass('is-invalid');
            } else {
                email.removeClass('is-invalid');
            }
            return
        }

        const formData = new FormData();
        formData.append('action', 'btzc_el_persist_data');
        formData.append('employee_id', $('#btzc-el-self-service-employee-id').val());
        formData.append('first_name', firstName.val());
        formData.append('last_name', lastName.val());
        formData.append('gender', $('#btzc-el-self-service-gender-selection').find(":selected").val());
        formData.append('phone_number', $('#btzc-el-self-service-phone-number').val());
        formData.append('room_number', $('#btzc-el-self-service-room-number').val());
        formData.append('email_address', email.val().toLowerCase());
        formData.append('information', $('#btzc-el-self-service-info').val());
        formData.append('wp_username',  wordpressAccessCheckbox.is(':checked')? wordpressAccessUsername.val(): '');
        formData.append('wp_password', wordpressAccessCheckbox.is(':checked')? wordpressAccessPassword.val(): '');

        let department = $('#btzc-el-self-service-department-selection-container').children().map(function () {
            return $(this).val();
        }).get().join(' ');
        formData.append('departments', department.replace('undefined', '').trim())

        let occupation = $('#btzc-el-self-service-occupation-selection-container').children().map(function () {
            return $(this).val();
        }).get().join(' ');
        formData.append('occupations', occupation.replace('undefined', '').trim())

        if (imageFileUpload.val()) {
            for (const file of imageFileUpload.prop('files')) {
                formData.append('btzc-el-employee-photo-upload[]', file);
            }
        }

        $.ajax ({
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function () {
                alert('Ihre Daten wurden erfolgreich gespeichert.')
                const url = window.location.pathname;
                const args = new URLSearchParams(window.location.search);
                args.delete('init_id')
                window.location.href = url + '?' + args.toString();
            }
        });
    })


    /**
     * Updates the wordpress username field based on the provided email address.
     */
    $('#btzc-el-self-service-email-address').on('input', function () {
        let email = $(this).val();
        let wordpressUsername = $('#btzc-el-self-service-username')
        if (email.includes('@')) {
            wordpressUsername.val(email.split('@')[0].toLowerCase());
        } else {
            wordpressUsername.val('');
        }
    })
})