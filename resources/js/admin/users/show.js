/**
 * Admin User Details Module
 * 
 * Handles user detail page functionality including:
 * - User links DataTable initialization
 * - User management actions (edit, delete, ban, freeze)
 * - Modal form handling for all actions
 * 
 * Features:
 * - Responsive DataTable for user links
 * - Unified modal handler system
 * - Dynamic form population
 * - Role and status management
 * - Confirmation dialogs for all actions
 * 
 * Dependencies:
 * - jQuery
 * - DataTables
 * - Bootstrap modals
 */
$(document).ready(function () {
    $("#userLinksTable").DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: true,
        searching: true,
        paging: true,
        ordering: true,
        info: false,
        order: [
            [2, "desc"]
        ]
    });
});


const MODAL_CONFIGS = {
    delete: {
        modalId: '#deleteUserConfirmModal',
        emailField: '#deleteUserEmail',
        formId: '#deleteUserForm'
    },
    ban: {
        modalId: '#banUserConfirmModal',
        emailField: '#banUserEmail',
        formId: '#banUserForm'
    },
    freeze: {
        modalId: '#freezeUserConfirmModal',
        emailField: '#freezeUserEmail',
        formId: '#freezeUserForm'
    }
};

const setupModalHandler = (actionType) => {
    $(document).on('click', `.${actionType}-user`, function (e) {
        e.preventDefault();

        const $button = $(this);
        const { modalId, emailField, formId } = MODAL_CONFIGS[actionType];

        $(emailField).text($button.data('user-email'));
        $(formId).attr('action', $button.data(`${actionType}-url`));

        $(modalId).modal('show');
    });
};

Object.keys(MODAL_CONFIGS).forEach(setupModalHandler);


$('#editUserModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var userId = button.data('user-id');
    var userName = button.data('user-name');
    var userEmail = button.data('user-email');
    var userRoles = button.data('user-roles') ? button.data('user-roles').split(',') : [];
    var userStatus = button.data('user-status');
    var userUrl = button.data('user-edit-url');

    $('#editUserId').val(userId);
    $('#editName').val(userName);
    $('#editEmail').val(userEmail);
    $('#editStatus').val(userStatus);

    $('#editRole').val(userRoles);
    var form = $('#editUserForm');
    form.attr('action', userUrl);
});
