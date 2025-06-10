<!-- Edit Link Modal -->
<div class="modal fade" id="editLinkModal" tabindex="-1" aria-labelledby="editLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLinkModalLabel">Edit Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLinkForm" action="" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editLinkId" name="editLinkId">

                    <div class="mb-3">
                        <label for="edit_custom_name" class="form-label">Custom name</label>
                        <input type="text" class="form-control" id="editCustomName" name="editCustomName" 
                            minlength="3"
                            maxlength="255"
                            pattern="^[a-zA-Z0-9_\- ]+$"
                            title="Only letters, numbers, spaces, underscores and dashes are allowed">
                        <div class="invalid-feedback">Custom name must be between 3 and 255 characters, using only letters, numbers, spaces, underscores and dashes.</div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="editURL" name="editURL" required minlength="5" maxlength="2048">
                        <div class="invalid-feedback">Please enter a valid URL (5 to 2048 characters).</div>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="editStatus">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary modal-link-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-link-submit-btn">Update Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
