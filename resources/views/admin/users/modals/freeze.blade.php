<!-- Freeze User Confirmation Modal -->
<div class="modal fade" id="freezeUserConfirmModal" tabindex="-1" aria-labelledby="freezeUserConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="freezeUserConfirmModalLabel">Confirm User Freeze</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to freeze this user's account?</p>
                <p><strong>User:</strong> <span id="freezeUserEmail">{{ $user->email }}</span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="freezeUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-warning">Freeze Account</button>
                </form>
            </div>
        </div>
    </div>
</div>