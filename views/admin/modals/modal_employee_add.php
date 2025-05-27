<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addEmployeeModalLabel">Add Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" action="../../controller/admin/employee_add.php" method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="add_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="add_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="add_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="add_password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_code" class="form-label">QR Code</label>
                        <input type="text" class="form-control" id="add_code" name="code">
                        <div class="form-text">Leave blank to auto-generate.</div>
                    </div>
                    <div id="addEmployeeResult"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addEmployeeForm" class="btn btn-primary">Add Employee</button>
            </div>
        </div>
    </div>
</div>
