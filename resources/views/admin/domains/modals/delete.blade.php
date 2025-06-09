<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Deleting the domain will remove <b>ALL</b></p>
                <ul>
                    <li><b>Links</b></li>
                    <li><b>Links history</b></li>
                </ul>
                <p>Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary modal-domain-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteDomainForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger modal-domain-delete-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>