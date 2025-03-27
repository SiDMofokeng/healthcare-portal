<?php
$date = $_GET['date'] ?? '';
?>
<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">New Appointment</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <form id="appointment-form">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
        
        <div class="mb-3">
          <label class="form-label">Patient</label>
          <select name="patient_id" class="form-select" required>
            <?php
            $patients = $pdo->query("
              SELECT u.id, u.first_name, u.last_name 
              FROM users u
              JOIN patients p ON u.id = p.user_id
            ")->fetchAll();
            
            foreach ($patients as $patient) {
              echo "<option value='{$patient['id']}'>";
              echo htmlspecialchars("{$patient['first_name']} {$patient['last_name']}");
              echo "</option>";
            }
            ?>
          </select>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Time</label>
          <input type="time" name="time" class="form-control" required>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Reason</label>
          <textarea name="reason" class="form-control" rows="3"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary" id="save-appointment">Save</button>
    </div>
  </div>
</div>

<script>
document.getElementById('save-appointment').addEventListener('click', async () => {
  const formData = new FormData(document.getElementById('appointment-form'));
  
  const response = await fetch('/api/appointments.php', {
    method: 'POST',
    body: formData
  });
  
  const result = await response.json();
  if (result.success) {
    window.location.reload();
  }
});
</script>