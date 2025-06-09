<!-- Add Link Modal -->
<div class="modal fade" id="linkModal" tabindex="-1" aria-labelledby="linkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="linkModalLabel">Add New Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLinkForm" action="{{ route('admin.links.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="custom_name" class="form-label">Custom name</label>
                        <input type="text" class="form-control" id="custom_name" name="custom_name" minlength="3" maxlength="50">
                        <div class="invalid-feedback">Custom name must be between 3 and 50 characters, using only letters, numbers, and dashes.</div>
                    </div>

                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="url" name="url" required minlength="5" maxlength="2048" required>
                        <div class="invalid-feedback">Please enter a valid URL (5 to 2048 characters).</div>
                    </div>

                    <div class="mb-3">
                        <label for="user_email" class="form-label">Assign to user</label>
                        <input type="email" class="form-control" id="user_email" name="user_email" placeholder="Enter email" maxlength="255">
                        <div class="form-text text-muted">Leave empty to assign to the current administrator.</div>
                        <div class="invalid-feedback">Please enter a valid email address (max 255 characters).</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary modal-link-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-link-submit-btn">Shorten Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>