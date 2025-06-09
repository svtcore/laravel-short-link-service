<!-- Add Domain Modal -->
<div class="modal fade" id="domainModal" tabindex="-1" aria-labelledby="domainModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="domainModalLabel">Add New Domain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addDomainForm" action="{{ route('admin.domains.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="domainName" class="form-label">Domain Name</label>
                        <input type="text" class="form-control" id="domainName" name="domainName" required>
                    </div>
                    <div class="mb-3">
                        <label for="domainStatus" class="form-label">Status</label>
                        <select class="form-select" id="domainStatus" name="domainStatus">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary modal-domain-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-domain-submit-btn">Add Domain</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>