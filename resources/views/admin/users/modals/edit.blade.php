<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editUserId" name="id">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editName" name="editName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="editEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="editStatus">
                            <option value="active">Active</option>
                            <option value="banned">Banned</option>
                            <option value="freezed">Freezed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Roles</label>
                        <select class="form-select" id="editRole" name="editRoles[]" multiple size="2">
                            <option value="user" {{ $user->roles->contains('name', 'user') ? 'selected' : '' }}
                                class="py-2 px-3">
                                User
                            </option>
                            <option value="admin" {{ $user->roles->contains('name', 'admin') ? 'selected' : '' }}
                                class="py-2 px-3">
                                Admin
                            </option>
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple options</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary modal-user-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-user-submit-btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>