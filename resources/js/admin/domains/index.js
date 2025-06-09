/**
 * Admin Domains Management Module
 * 
 * Handles domain management functionality in admin panel including:
 * - Domains DataTable initialization
 * - Domain editing and deletion
 * - Modal form handling
 * 
 * Features:
 * - Responsive DataTable for domains
 * - Edit modal with prefilled data
 * - Delete confirmation flow
 * 
 * Dependencies:
 * - jQuery
 * - DataTables
 * - Bootstrap modals
 */
$(document).ready(function() {
    $("#manageDomainsTable").DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: true,
        searching: true,
        paging: true,
        ordering: true,
        info: false,
        order: [
            [1, "desc"]
        ]
    });

    $('#editDomainModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var domainId = button.data('domain-id');
        var domainName = button.data('domain-name');
        var domainStatus = button.data('domain-status');
        var domainUrl = button.data('domain-url');

        $('#editDomainId').val(domainId);
        $('#editDomainName').val(domainName);
        $('#editDomainStatus').val(domainStatus);

        var form = $('#editDomainForm');
        form.attr('action', domainUrl);
    });
    $(document).on('click', '.delete-button', function(e) {
        e.preventDefault();
        var domainId = $(this).data('domain-id');
        var form = $('#deleteDomainForm');
        form.attr('action', '/admin/domains/' + domainId);
        $('#deleteConfirmModal').modal('show');
    });
});
