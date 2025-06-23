let systemLanguage = '';

export async function getLanguage() {
    if (!systemLanguage) {
        systemLanguage = await retrieveSystemLanguage();
    }
    return systemLanguage;
}

export function retrieveSystemLanguage() {
    return new Promise((resolve) => {
        jQuery(function ($) {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: { action: 'btzc_el_get_system_language' },
                success: function (response) {
                    if (response.success) {
                        resolve(response.data);
                    } else {
                        resolve('en_US');
                    }
                },
                error: function () {
                    resolve('en_US');
                }
            });
        });
    });
}

export async function localized(word) {
    const language = await getLanguage();
    if (backendDictionary[word] && backendDictionary[word][language]) {
        return backendDictionary[word][language];
    }
    return word;
}

export const backendDictionary = {
    'Employee Register': {
        en_US: 'Employee Register',
        de_DE: 'Mitarbeitenden-Verzeichnis'
    },
    'Employees': {
        en_US: 'Employees',
        de_DE: 'Mitarbeitende'
    },
    'Gallery': {
        en_US: 'Gallery',
        de_DE: 'Galerie'
    },
    'Departments': {
        en_US: 'Departments',
        de_DE: 'Bereiche'
    },
    'Departments (multiple selection possible)': {
        en_US: 'Departments (multiple selection possible)',
        de_DE: 'Bereiche (Mehrfachauswahl möglich)'
    },
    'Occupations': {
        en_US: 'Occupations',
        de_DE: 'Positionen'
    },
    'Occupations (multiple selection possible)': {
        en_US: 'Occupations (multiple selection possible)',
        de_DE: 'Positionen (Mehrfachauswahl möglich)'
    },
    'Please select a photo': {
        en_US: 'Please select a photo',
        de_DE: 'Bitte eine Photo auswählen'
    },
    'Add new employee': {
        en_US: 'Add new employee',
        de_DE: 'Neuen Mitarbeitenden hinzufügen'
    },
    'Add new department': {
        en_US: 'Add new department',
        de_DE: 'Neues Bereich hinzufügen'
    },
    'Add new occupation': {
        en_US: 'Add new occupation',
        de_DE: 'Neue Position hinzufügen'
    },
    'Occupation (gendered)': {
        en_US: 'Occupation (gendered)',
        de_DE: 'Position (gegendert)'
    },
    'Male form': {
        en_US: 'Male form',
        de_DE: 'Männlicher Form'
    },
    'Female form': {
        en_US: 'Female form',
        de_DE: 'Weiblicher Form'
    },
    'Diverse form': {
        en_US: 'Diverse form',
        de_DE: 'Diverse Form'
    },
    'All departments': {
        en_US: 'All departments',
        de_DE: 'Alle Bereiche'
    },
    'All occupations': {
        en_US: 'All occupations',
        de_DE: 'Alle Positionen'
    },
    'Phone': {
        en_US: 'Phone',
        de_DE: 'Tel.'
    },
    'Email': {
        en_US: 'Email',
        de_DE: 'E-Mail'
    },
    'Office': {
        en_US: 'Office',
        de_DE: 'Büro'
    },
    'Information': {
        en_US: 'Information',
        de_DE: 'Info'
    },
    '[FIRST NAME]': {
        en_US: '[FIRST NAME]',
        de_DE: '[VORNAME]'
    },
    '[LAST NAME]': {
        en_US: '[LAST NAME]',
        de_DE: '[NACHNAME]'
    },
    '[PHONE NUMBER]': {
        en_US: '[PHONE NUMBER]',
        de_DE: '[TELEFON]'
    },
    '[OFFICE NUMBER]': {
        en_US: '[OFFICE NUMBER]',
        de_DE: '[RAUM]'
    },
    '[EMAIL@EXAMPLE.COM]': {
        en_US: '[EMAIL@EXAMPLE.COM]',
        de_DE: '[E-MAIL@EXAMPLE.COM]'
    },
    '[ADDITIONAL INFORMATION]': {
        en_US: '[ADDITIONAL INFORMATION]',
        de_DE: '[ZUSÄTZLICHE INFORMATIONEN]'
    },
    'Save': {
        en_US: 'Save',
        de_DE: 'Speichern'
    },
    'Cancel': {
        en_US: 'Cancel',
        de_DE: 'Abbrechen'
    },
    'Delete': {
        en_US: 'Delete',
        de_DE: 'Löschen'
    },
    'Search': {
        en_US: 'Search ...',
        de_DE: 'Suchen ...'
    },
    'Confirm deletion': {
        en_US: 'Confirm deletion',
        de_DE: 'Löschen bestätigen'
    },
    'Do you really want to delete this employee?': {
        en_US: 'Do you really want to delete this employee?',
        de_DE: 'Möchten Sie diesen Mitarbeitenden wirklich Löschen?'
    },
    'Do you really want to delete this department?': {
        en_US: 'Do you really want to delete this department?',
        de_DE: 'Wollen Sie diesen Bereich wirklich Löschen?'
    },
    'Do you really want to delete this occupation?': {
        en_US: 'Do you really want to delete this occupation?',
        de_De: 'Möchten Sie diese Position wirklich Löschen?'
    },
    'Do you really want to delete the selected photo(s)?': {
        en_US: 'Do you really want to delete the selected photo(s)?',
        de_DE: 'Möchten Sie die ausgewählten Bilder wirklich Löschen?'
    },
    'This action CANNOT be undone!': {
        en_US: 'This action CANNOT be undone!',
        de_DE: 'Diese Aktion kann NICHT rückgängig gemacht werden!'
    },
    'Gender': {
        en_US: 'Gender',
        de_DE: 'Geschlecht'
    },
    'male': {
        en_US: 'male',
        de_DE: 'männlich'
    },
    'female': {
        en_US: 'female',
        de_DE: 'weiblich'
    },
    'diverse': {
        en_US: 'diverse',
        de_DE: 'divers'
    },
    'undefined': {
        en_US: 'select',
        de_DE: 'Auswahl'
    },
    'genders': {
        en_US: {male: 'male', female: 'female', diverse: 'diverse', undefined: 'select'},
        de_DE: {male: 'männlich', female: 'weiblich', diverse: 'divers', undefined: 'Auswahl'}
    },
    'Select all': {
        en_US: 'Select All',
        de_DE: 'Alles Auswählen'
    },
    'Deselect all': {
        en_US: 'Deselect All',
        de_DE: 'Auswahl aufheben'
    },
    'Upload images': {
        en_US: 'Upload images',
        de_DE: 'Bilder hochladen'
    },
    'Shortcode selection': {
        en_US: 'Shortcode selection',
        de_DE: 'Shortcode Auswahl'
    },
    'Shortcode copied to clipboard': {
        en_US: 'Shortcode copied to clipboard',
        de_DE: 'Shortcode in Zwischenablage kopiert'
    },
    'Unable to copy shortcode to clipboard': {
        en_US: 'Unable to copy shortcode to clipboard. Please insert to folowing shortcode manually to your page:',
        de_DE: 'Shortcode kann nicht in die Zwischenablage kopiert werden. Bitte fügen Sie den folgenden Shortcode manuell auf Ihrer Seite ein:'
    },
    'No limitations': {
        en_US: 'No limitations',
        de_DE: 'Keine Einschränkungen'
    },
    'Limit to department': {
        en_US: 'Limit to department',
        de_DE: 'Auf Bereich beschränken'
    }
};