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

        $('#editDomainId').val(domainId);
        $('#editDomainName').val(domainName);
        $('#editDomainStatus').val(domainStatus);

        var form = $('#editDomainForm');
        form.attr('action', '/admin/domains/' + domainId);
    });
    $(document).on('click', '.delete-button', function(e) {
        e.preventDefault();
        var domainId = $(this).data('domain-id');
        var form = $('#deleteDomainForm');
        form.attr('action', '/admin/domains/' + domainId);
        $('#deleteConfirmModal').modal('show');
    });
});