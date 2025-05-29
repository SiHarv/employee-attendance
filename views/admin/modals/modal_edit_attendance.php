<div class="modal fade" id="editAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAttendanceForm">
                    <input type="hidden" id="edit_attendance_id" name="attendance_id">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" id="edit_employee_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Time In</label>
                        <input type="datetime-local" class="form-control" id="edit_time_in" name="time_in" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Time Out</label>
                        <input type="datetime-local" class="form-control" id="edit_time_out" name="time_out">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveAttendanceChanges">Save changes</button>
            </div>
        </div>
    </div>
</div>
