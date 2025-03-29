<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
$page_title = 'Patients';
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card shadow-lg">
        <div class="card-header p-3" style="background: linear-gradient(195deg, #f8f9fa 0%, #e9ecef 100%);">
          <h5 class="mb-0 text-dark font-weight-bold">
            <i class="fas fa-procedures me-2 text-primary"></i>
            Patient Management
          </h5>
        </div>
        <div class="card-body">
          <!-- Patient list will go here -->
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>