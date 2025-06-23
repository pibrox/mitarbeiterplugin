/**
 * Frontend
 *
 * This file contains the jQuery-code for the employee-list-frontend
 *
 * @since 2.0.0
 */

jQuery(function($) {
    /**
     * Represents the DOM element for the search input field in the front-end interface.
     * This variable holds a jQuery object corresponding to the search field with the ID 'btzc-el-frontend-search-field'.
     * It allows interaction with the search input for functionalities such as reading user input, binding events, or manipulating its properties.
     *
     * @since 2.0.0
     */
    const searchField = $('#btzc-el-frontend-search-field');

    /**
     * Represents a jQuery object that targets the department selection element in the frontend.
     * This variable is used to manipulate or retrieve information related to the department dropdown/select functionality.
     *
     * @since 2.0.0
     */
    const departmentSelect = $('#btzc-el-frontend-department-select');

    /**
     * Represents a jQuery object that targets the occupation selection element in the frontend.
     * This variable is used to manipulate or retrieve information related to the occupation dropdown/select functionality.
     *
     * @since 2.0.0
     */
    const occupationSelect = $('#btzc-el-frontend-occupation-select');

    /**
     * Represents the jQuery object for the employee list in the front-end.
     *
     * The variable `employeeList` refers to the DOM element with the identifier
     * `#btzc-el-frontend-employee-list-body`. It is utilized to manage or manipulate
     * the employee list's UI or contents programmatically within the application.
     *
     * @since 2.0.0
     */
    const employeeList = $('#btzc-el-frontend-employee-list-body');




    /**
     * Resets the department- and occupation-selection when the search-field receives focus.
     *
     * @return void
     *
     * @since 2.0.0
     */
    searchField.on('focus', function () {
        departmentSelect.val('0');
        occupationSelect.val('0');
    })


    /**
     * Searches for the user-input in employee first- and lastname.
     * Matching results are shown, non-matching are hidden.
     *
     * @return void
     *
     * @since 2.0.0
     */
    searchField.on('input', function (e) {
        const searchString = e.target.value.toLowerCase();
        for (const tile of employeeList.children()) {
            const dataset = tile.dataset;
            if (dataset.firstname.toLowerCase().includes(searchString)  ||  dataset.lastname.toLowerCase().includes(searchString)) {
                $(tile).slideDown(300);
            } else {
                $(tile).slideUp(300);
            }
        }
    })


    /**
     * Retrieves the selected department- and occupation-ids when the department-selection has changed.
     * Calls the filter-function to filter the list with the new selection(s).
     *
     * @return void
     *
     * @since 2.0.0
     */
    departmentSelect.on('change', function () {
        const departmentID = Number.parseInt($(this).val());
        const occupationID = Number.parseInt(occupationSelect.val());
        searchField.val('');
        filterEmployeeList(departmentID, occupationID);
    })


    /**
     * Retrieves the selected department- and occupation-ids when the occupation-selection has changed.
     * Calls the filter-function to filter the list with the new selection(s).
     *
     * @return void
     *
     * @since 2.0.0
     */
    occupationSelect.on('change', function () {
        const departmentID = Number.parseInt(departmentSelect.val());
        const occupationID = Number.parseInt($(this).val());
        searchField.val('');
        filterEmployeeList(departmentID, occupationID);
    })


    /**
     * Filters a list of employee elements based on the specified department ID and occupation ID.
     * If both parameters are 0, all employees are shown. If only one parameter is specified,
     * the filter will apply based on that parameter alone.
     *
     * @param {number} departmentID - The ID of the department to filter by. Use 0 to disable department filtering.
     * @param {number} occupationID - The ID of the occupation to filter by. Use 0 to disable occupation filtering.
     *
     * @return {void} This function does not return any value. It manipulates the DOM to show or hide employee elements.
     *
     * @since 2.0.0
     */
    function filterEmployeeList(departmentID, occupationID) {
        for (const tile of employeeList.children()) {
            if (departmentID === 0  &&  occupationID === 0) {
                resetFilter(tile)
            } else if (departmentID !==   0  &&  occupationID ===   0) {
                filterByDepartment(tile, departmentID)
            } else if (departmentID ===   0  &&  occupationID !==   0) {
                filterByOccupation(tile, occupationID)
            } else {
                filterByDepartmentAndOccupation(tile, departmentID, occupationID);
            }
        }

        /**
         * Resets the filter applied to the provided tile element by sliding it down.
         *
         * @param {HTMLElement} tile - The tile element to reset the filter on.
         *
         * @return {void} No return value.
         *
         * @since 2.0.0
         */
        function resetFilter(tile) {
            $(tile).slideDown(300);
        }

        /**
         * Filters the tile elements based on the provided department ID.
         *
         * @param {HTMLElement} tile The HTML element representing the tile to filter.
         * @param {number} departmentID The numeric ID of the department to filter by.
         *
         * @return {void}
         *
         * @since 2.0.0
         */
        function filterByDepartment(tile, departmentID) {
            const departments = $(tile).data('departments').trim().split(' ').map(Number);
            if (departments.includes(departmentID)) {
                $(tile).slideDown(300);
            } else {
                $(tile).slideUp(300);
            }
        }

        /**
         * Filters the tile elements based on the provided occupation ID.
         *
         * @param {HTMLElement} tile The HTML element representing the tile to filter.
         * @param {number} occupationID The numeric ID of the occupation to filter by.
         *
         * @return {void}
         *
         * @since 2.0.0
         */
        function filterByOccupation(tile, occupationID) {
            const occupations = $(tile).data('occupations').trim().split(' ').map(Number);
            if (occupations.includes(occupationID)) {
                $(tile).slideDown(300);
            } else {
                $(tile).slideUp(300);
            }
        }

        /**
         * Filters the tile elements based on the provided department- and occupation ID.
         *
         * @param {HTMLElement} tile The HTML element representing the tile to filter.
         * @param {number} departmentID The numeric ID of the department to filter by.
         * @param {number} occupationID The numeric ID of the occupation to filter by.
         *
         * @return {void}
         *
         * @since 2.0.0
         */
        function filterByDepartmentAndOccupation(tile, departmentID, occupationID) {
            const departments = $(tile).data('departments').trim().split(' ').map(Number);
            const occupations = $(tile).data('occupations').trim().split(' ').map(Number);
            if (departments.includes(departmentID)   &&   occupations.includes(occupationID)) {
                $(tile).slideDown(300);
            } else {
                $(tile).slideUp(300);
            }
        }
    }

})