<div class="modal fade" id="shareModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Document</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="share_document_id">
                <div class="mb-3">
                    <label>Select Users</label>
                    <select id="share_users" class="form-select select2" multiple>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Permission</label>
                    <select id="permission" class="form-select">
                        <option value="view">View</option>
                        <option value="download">Download</option>
                        <option value="edit">Edit</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="shareBtn">Share</button>
            </div>
        </div>
    </div>
</div>
