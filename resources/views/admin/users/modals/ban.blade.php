<!-- Ban User Confirmation Modal -->
<div class="modal fade" id="banUserConfirmModal" tabindex="-1" aria-labelledby="banUserConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="banUserConfirmModalLabel">Confirm User Ban</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to ban this user?</p>
                <p><strong>User:</strong> <span id="banUserEmail"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="banUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-dark">Ban User</button>
                </form>
            </div>
        </div>
    </div>
</div>