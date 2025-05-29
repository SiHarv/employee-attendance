<div class="modal fade" id="editAfternoonAttendanceModal" tabindex="-1" aria-labelledby="editAfternoonAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editAfternoonAttendanceForm" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editAfternoonAttendanceModalLabel">Edit Afternoon Attendance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_afternoon_attendance_id" name="attendance_id">
          <div class="mb-3">
            <label for="edit_afternoon_employee_name" class="form-label">Employee</label>
            <input type="text" class="form-control" id="edit_afternoon_employee_name" name="employee_name" readonly>
          </div>
          <div class="mb-3">
            <label for="edit_afternoon_time_in" class="form-label">Time In</label>
            <input type="datetime-local" class="form-control" id="edit_afternoon_time_in" name="time_in" required>
          </div>
          <div class="mb-3">
            <label for="edit_afternoon_time_out" class="form-label">Time Out</label>
            <input type="datetime-local" class="form-control" id="edit_afternoon_time_out" name="time_out">
          </div>
          <div class="mb-3">
            <label for="edit_afternoon_status" class="form-label">Status</label>
            <select class="form-select" id="edit_afternoon_status" name="status" required>
              <option value="present">Present</option>
              <option value="late">Late</option>
              <option value="absent">Absent</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveAfternoonAttendanceChanges">Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>
