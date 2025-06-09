/**
 * Admin Users Management Module
 * 
 * Handles user management functionality in admin panel including:
 * - DataTable initialization for users list
 * - User edit modal form handling
 * - User deletion confirmation flow
 * 
 * Features:
 * - Responsive DataTable with sorting and pagination
 * - Dynamic modal form population
 * - Role selection management
 * - Status updates
 * - Confirmation dialogs for destructive actions
 * 
 * Dependencies:
 * - jQuery
 * - DataTables
 * - Bootstrap modals
 */
$(document).ready(function () {
    $("#manageUsersTable").DataTable({
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

    $('#editUserModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var userId = button.data('user-id');
        var userName = button.data('user-name');
        var userEmail = button.data('user-email');
        var userRoles = button.data('user-roles') ? button.data('user-roles').split(',') : [];
        var userStatus = button.data('user-status');
        var userUrl = button.data('user-url');

        $('#editUserId').val(userId);
        $('#editName').val(userName);
        $('#editEmail').val(userEmail);
        $('#editStatus').val(userStatus);

        $('#editRole').val(userRoles);
        var form = $('#editUserForm');
        form.attr('action', userUrl);
    });

    $(document).on('click', '.delete-user', function (e) {
        e.preventDefault();
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        var userUrl = $(this).data('user-url');
        $('#deleteUserName').text(userName);
        var form = $('#deleteUserForm');
        form.attr('action', userUrl);
        $('#deleteUserConfirmModal').modal('show');
    });

});
