<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Build the base query
$query = "SELECT a.*, 
          u.first_name, u.last_name, u.email,
          p.date_of_birth, p.gender, p.blood_type,
          d.specialization
          FROM appointments a
          JOIN users u ON a.patient_id = u.id
          LEFT JOIN patients p ON u.id = p.user_id
          LEFT JOIN doctors d ON a.doctor_id = d.user_id
          WHERE a.doctor_id = ?";

// Add conditions based on filters
$params = [$_SESSION['user_id']];
$conditions = [];

if ($status !== 'all') {
    $conditions[] = "a.status = ?";
    $params[] = $status;
}

if (!empty($date_from)) {
    $conditions[] = "a.appointment_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $conditions[] = "a.appointment_date <= ?";
    $params[] = $date_to;
}

if (!empty($search)) {
    $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR a.reason LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

// Add sorting
$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

// Pagination setup
$per_page = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination - use a separate query without LIMIT/OFFSET
$count_query = str_replace(
    "SELECT a.*", 
    "SELECT COUNT(*) as total", 
    explode("ORDER BY", $query)[0] // Remove ORDER BY for count query
);
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_appointments = $count_stmt->fetchColumn();
$total_pages = ceil($total_appointments / $per_page);

// Add pagination to main query - append after executing count query
$query_with_pagination = $query . " LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;

// Fetch appointments with pagination
$stmt = $pdo->prepare($query_with_pagination);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

$page_title = 'Appointments';
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header p-3" style="background: linear-gradient(195deg, #49a3f1 0%, #1A73E8 100%);">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <h5 class="mb-0 text-white font-weight-bold">
                                <i class="fas fa-calendar-check me-2 text-white"></i>
                                Appointments Management
                            </h5>
                            <p class="text-sm text-white opacity-8 mb-0">View and manage all appointments</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-white btn-sm" data-bs-toggle="modal" data-bs-target="#newAppointmentModal">
                                <i class="fas fa-plus me-1"></i> New Appointment
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body border-bottom">
                    <form method="get" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                                <option value="scheduled" <?= $status === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="no_show" <?= $status === 'no_show' ? 'selected' : '' ?>>No Show</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Patient or reason..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body px-0 pt-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Patient</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Details</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Appointment</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appt): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <img src="../assets/images/default-avatar.jpg" class="avatar avatar-sm me-3" alt="user">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm"><?= htmlspecialchars($appt['first_name'].' '.$appt['last_name']) ?></h6>
                                                    <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($appt['email']) ?></p>
                                                    <p class="text-xs text-secondary mb-0">
                                                        <?= !empty($appt['date_of_birth']) ? 'DOB: '.date('m/d/Y', strtotime($appt['date_of_birth'])) : '' ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                Gender: <?= !empty($appt['gender']) ? ucfirst($appt['gender']) : 'N/A' ?>
                                            </p>
                                            <p class="text-xs text-secondary mb-0">
                                                Blood Type: <?= !empty($appt['blood_type']) ? $appt['blood_type'] : 'N/A' ?>
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                <?= date('M j, Y', strtotime($appt['appointment_date'])) ?>
                                                at <?= date('g:i A', strtotime($appt['appointment_time'])) ?>
                                            </p>
                                            <p class="text-xs text-secondary mb-0">
                                                <?= htmlspecialchars($appt['reason']) ?>
                                            </p>
                                        </td>

                                        <!-- === STATUS COLUMN (REPLACE THIS TD) === -->
                                        <td> <!-- Temporary border for visibility -->
                                            <?php
                                            // Debug: Check what status value we're receiving
                                            // echo '<pre>'.print_r($appt, true).'</pre>'; // Uncomment to see all data
                                            
                                            $statusColors = [
                                                'scheduled' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                'no_show' => 'warning',
                                            ];
                                            
                                            $rawStatus = $appt['status'];
                                            $statusText = ucfirst(str_replace('_', ' ', $rawStatus));
                                            $badgeColor = $statusColors[$rawStatus] ?? 'secondary';
                                            
                                            // Debug output
                                            echo "<!-- Raw Status: $rawStatus -->";
                                            ?>
                                            
                                            <span class="badge bg-gradient-<?= $badgeColor ?>">
                                                <?= $statusText ?>
                                                <!-- Debug: <?= $rawStatus ?> -->
                                            </span>
                                        </td>
                                        <!-- === END STATUS COLUMN === -->

                                        <td class="align-middle text-end">
                                          <div class="d-flex justify-content-end gap-1">
                                              <!-- View Button -->
                                              <button class="btn btn-sm btn-outline-primary view-btn" 
                                                      data-id="<?= $appt['id'] ?>"
                                                      title="View Details">
                                                  <i class="fas fa-eye"></i>
                                              </button>
                                              
                                              <?php if ($appt['status'] === 'scheduled') : ?>
                                                  <!-- Complete Button -->
                                                  <button class="btn btn-sm btn-success complete-btn" 
                                                          data-id="<?= $appt['id'] ?>"
                                                          title="Mark as Completed">
                                                      <i class="fas fa-check"></i>
                                                  </button>
                                                  
                                                  <!-- Cancel Button -->
                                                  <button class="btn btn-sm btn-danger cancel-btn" 
                                                          data-id="<?= $appt['id'] ?>"
                                                          title="Cancel Appointment">
                                                      <i class="fas fa-times"></i>
                                                  </button>
                                              <?php endif; ?>
                                          </div>
                                      </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($appointments)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-2x text-gray-300 mb-3"></i>
                                            <p class="text-muted">No appointments found</p>
                                            <a href="?status=all" class="btn btn-sm btn-primary">Reset Filters</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <nav class="px-4 pt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {

  // Initialize form with default times
  const now = new Date();
  const defaultTime = `${now.getHours().toString().padStart(2, '0')}:${Math.floor(now.getMinutes()/15)*15}`;
  $('input[name="appointment_time"]').val(`${defaultTime}:00`.substring(0,5));

  // Form submission handler
  $('#appointmentForm').submit(function(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    // Show loading state
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Scheduling...');
    
    // Collect form data
    const formData = new FormData(form[0]);
    
    // Add CSRF token if you're using one
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    
    $.ajax({
      url: '../api/create_appointment.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          Swal.fire({
            title: 'Success!',
            text: response.message || 'Appointment scheduled successfully',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            $('#newAppointmentModal').modal('hide');
            form.trigger('reset');
            // Optional: Refresh the appointments table
            // location.reload();
          });
        } else {
          Swal.fire('Error', response.message || 'Failed to schedule appointment', 'error');
        }
      },
      error: function(xhr) {
        let errorMessage = 'Network error - please try again';
        try {
          const response = JSON.parse(xhr.responseText);
          errorMessage = response.message || errorMessage;
        } catch (e) {
          console.error('Error parsing response:', e);
        }
        Swal.fire('Error', errorMessage, 'error');
      },
      complete: function() {
        submitBtn.prop('disabled', false).html(originalText);
      }
    });
  });

    // Function to handle API calls
    function updateAppointmentStatus(appointmentId, status, reason = '') {
        return $.ajax({
            url: '../api/update_appointment_status.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                id: appointmentId,
                status: status,
                reason: reason
            }),
            dataType: 'json'
        });
    }

    // Complete Appointment
    $('.complete-btn').click(function() {
        const appointmentId = $(this).data('id');
        const $btn = $(this);
        
        Swal.fire({
            title: 'Complete Appointment',
            text: 'Mark this appointment as completed?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, complete it',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return updateAppointmentStatus(appointmentId, 'completed')
                    .then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Failed to complete appointment');
                        }
                        return response;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.statusText || error.message}`
                        );
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Completed!',
                    text: 'Appointment marked as completed.',
                    icon: 'success'
                }).then(() => {
                    location.reload(); // Refresh to show updated status
                });
            }
        });
    });

    // Cancel Appointment
    $('.cancel-btn').click(function() {
        const appointmentId = $(this).data('id');
        
        Swal.fire({
            title: 'Cancel Appointment',
            html: `
                <p>Please provide a reason for cancellation:</p>
                <textarea id="cancel-reason" class="swal2-textarea" required></textarea>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm Cancellation',
            focusConfirm: false,
            preConfirm: () => {
                const reason = $('#cancel-reason').val();
                if (!reason) {
                    Swal.showValidationMessage('Reason is required');
                    return false;
                }
                return updateAppointmentStatus(appointmentId, 'cancelled', reason)
                    .then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Failed to cancel appointment');
                        }
                        return response;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.statusText || error.message}`
                        );
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cancelled!',
                    text: 'Appointment has been cancelled.',
                    icon: 'success'
                }).then(() => {
                    location.reload(); // Refresh to show updated status
                });
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>