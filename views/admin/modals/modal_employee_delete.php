<div class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteEmployeeModalLabel">Delete Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteEmployeeForm" action="../../controller/admin/employee_delete.php" method="post">
                <div class="modal-body">
                    <p>Are you sure you want to delete this employee? <strong id="delete_employee_name"></strong></p>
                    <p class="text-danger">This action cannot be undone. All attendance records for this employee will also be deleted.</p>
                    <input type="hidden" name="employee_id" id="delete_employee_id">
                    <div id="deleteEmployeeResult"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
