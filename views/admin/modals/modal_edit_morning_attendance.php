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

<script>
    $(document).ready(function() {
        // Search functionality with jQuery
        $("#searchReport").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#reportTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
            paginateReportTable(6);
        });

        // Print functionality
        $("#printBtn").click(function() {
            window.print();
        });

        // Pagination for Attendance Records Table
        function paginateReportTable(rowsPerPage) {
            var $table = $("#reportTable");
            var $rows = $table.find("tbody tr:visible");
            var totalRows = $rows.length;
            var totalPages = Math.ceil(totalRows / rowsPerPage);

            // Remove old pagination
            $("#attendanceTablePagination").remove();

            if (totalPages <= 1) return;

            // Add pagination controls after the table
            var $pagination = $('<ul class="pagination justify-content-center mt-3" id="attendanceTablePagination"></ul>');
            for (var i = 1; i <= totalPages; i++) {
                $pagination.append('<li class="page-item"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>');
            }
            $table.closest('.card').append($pagination);

            function showPage(page) {
                $rows.hide();
                $rows.slice((page - 1) * rowsPerPage, page * rowsPerPage).show();
                $pagination.find('li').removeClass('active');
                $pagination.find('li').eq(page - 1).addClass('active');
            }

            // Initial page
            showPage(1);

            // Pagination click
            $pagination.on('click', 'a.page-link', function(e) {
                e.preventDefault();
                var page = parseInt($(this).data('page'));
                if (!isNaN(page)) {
                    showPage(page);
                }
            });
        }

        // Call pagination for Attendance Records Table, 6 rows per page
        paginateReportTable(6);

        // Button group toggle for Morning/Afternoon
        $(".btn-group button").on("click", function() {
            var btnText = $(this).text().trim();
            if (btnText === "Morning") {
                $("#morningAttendanceContainer").show();
                $("#afternoonAttendanceContainer").hide();
                window.currentAttendanceType = 'morning';
            } else if (btnText === "Afternoon") {
                $("#morningAttendanceContainer").hide();
                $("#afternoonAttendanceContainer").show();
                window.currentAttendanceType = 'afternoon';
            }
            // Set active state
            $(".btn-group button").removeClass("active");
            $(this).addClass("active");
        });

        // Set Morning as active by default
        $(".btn-group button:contains('Morning')").addClass("active");
        $("#morningAttendanceContainer").show();
        $("#afternoonAttendanceContainer").hide();
    });
</script>
