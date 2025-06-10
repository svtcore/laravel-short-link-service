<!-- Edit Domain Modal -->
<div class="modal fade" id="editDomainModal" tabindex="-1" aria-labelledby="editDomainModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDomainModalLabel">Edit Domain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDomainForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editDomainId" name="id">
                    <div class="mb-3">
                        <label for="editDomainName" class="form-label">Domain Name</label>
                        <input type="text" class="form-control" id="editDomainName" name="domainName" 
                            required
                            minlength="3"
                            maxlength="255"
                            pattern="^(?!:\/\/)(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$"
                            title="Please enter a valid domain name (e.g. example.com)">
                    </div>
                    <div class="mb-3">
                        <label for="editDomainStatus" class="form-label">Status</label>
                        <select class="form-select" id="editDomainStatus" name="domainStatus" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="modal-footer modal-custom-footer">
                        <button type="button" class="btn btn-outline-secondary modal-domain-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-domain-submit-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
