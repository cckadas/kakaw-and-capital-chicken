<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Handle delete
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $conn->prepare('DELETE FROM sanitation_checklist WHERE id = ?');
  $stmt->execute([$id]);
  header('Location: sanitation.php');
  exit;
}

// Handle toggle completion
if (isset($_POST['toggle_task'])) {
  $id = intval($_POST['task_id']);
  $stmt = $conn->prepare('UPDATE sanitation_checklist SET is_completed = NOT is_completed WHERE id = ?');
  $stmt->execute([$id]);
  echo json_encode(['success' => true]);
  exit;
}

// Handle add/edit
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['toggle_task'])) {
  $area = $_POST['area'];
  $task_name = $_POST['task_name'];
  $day_of_week = $_POST['day_of_week'];
  
  if (isset($_POST['edit_id']) && $_POST['edit_id']) {
    // Edit
    $id = intval($_POST['edit_id']);
    $stmt = $conn->prepare('UPDATE sanitation_checklist SET area=?, task_name=?, day_of_week=? WHERE id=?');
    $stmt->execute([$area, $task_name, $day_of_week, $id]);
    $message = 'Task updated!';
  } else {
    // Add
    $stmt = $conn->prepare('INSERT INTO sanitation_checklist (area, task_name, day_of_week) VALUES (?, ?, ?)');
    $stmt->execute([$area, $task_name, $day_of_week]);
    $message = 'Task added!';
  }
}

// Fetch tasks
$tasks = $conn->query('SELECT * FROM sanitation_checklist ORDER BY area, task_name, day_of_week')->fetchAll(PDO::FETCH_ASSOC);

// Fetch for edit
$edit = null;
if (isset($_GET['edit'])) {
  $id = intval($_GET['edit']);
  $stmt = $conn->prepare('SELECT * FROM sanitation_checklist WHERE id = ?');
  $stmt->execute([$id]);
  $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sanitation Checklist</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(120deg, #fffbe7 0%, #f2f2f2 100%); margin: 0; }
    .main-flex-wrap {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: flex-start;
      gap: 40px;
      max-width: 1200px;
      margin: 60px auto 60px auto;
      padding: 0 24px;
    }
    .centered-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(247,184,1,0.13);
      padding: 40px 36px 32px 36px;
      min-width: 340px;
      flex: 1 1 520px;
      margin-bottom: 0;
      max-width: 540px;
      align-self: flex-start;
      box-sizing: border-box;
    }
    .task-list-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 4px 16px rgba(247,184,1,0.10);
      padding: 40px 36px 32px 36px;
      min-width: 600px;
      flex: 2 1 900px;
      max-width: 900px;
      align-self: flex-start;
      box-sizing: border-box;
    }
    h2 { margin-bottom: 18px; color: #F7B801; font-size: 1.5rem; font-weight: 700; }
    form { margin-bottom: 0; }
    input, textarea, select { width: 95%; padding: 14px; margin-bottom: 16px; border: 2px solid #ffe7a0; border-radius: 8px; font-size: 1rem; background: #fff; transition: border 0.2s; }
    input:focus, textarea:focus, select:focus { border: 2px solid #F7B801; outline: none; }
    .form-btn-row { display: flex; gap: 16px; align-items: center; }
    button { background: linear-gradient(90deg, #F7B801 80%, #ffe7a0 100%); color: #fff; border: none; border-radius: 22px; padding: 14px 38px; font-size: 1.15rem; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px #ffe7a0; transition: background 0.2s, color 0.2s; letter-spacing: 0.5px; margin-bottom: 0; }
    button:hover { background: #F7B801; color: #fffbe7; }
    .msg { color: #0a0; margin-bottom: 12px; font-weight: 600; background: #eaffea; border-radius: 8px; padding: 8px 16px; display: inline-block; }
    .error { color: #c00; margin-bottom: 12px; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 24px; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px #ffe7a0; }
    th, td { border: 1px solid #e0e0e0; padding: 12px 10px; text-align: left; font-size: 1rem; }
    th { background: #fffbe7; color: #F7B801; font-weight: 700; position: sticky; top: 0; z-index: 1; }
    tr:hover td { background: #fffbe7; transition: background 0.2s; }
    .actions { display: flex; gap: 8px; }
    .actions a { color: #F7B801; text-decoration: none; font-weight: 600; border-radius: 6px; padding: 8px 18px; background: #fffbe7; border: 2px solid #ffe7a0; transition: background 0.2s, color 0.2s; font-size: 1rem; }
    .actions a.delete { color: #fff; background: #c00; border: 2px solid #c00; }
    .actions a:hover { background: #F7B801; color: #fff; }
    .actions a.delete:hover { background: #a00; color: #fff; }
    .back-link { display: inline-block; margin-top: 18px; color: #F7B801; text-decoration: underline; font-weight: 600; }
    .back-link:hover { color: #c00; }
    .completion-checkbox { width: 20px; height: 20px; cursor: pointer; }
    .completed-row { background-color: #e8f5e8 !important; }
    .day-badge { 
      display: inline-block; 
      padding: 4px 8px; 
      border-radius: 12px; 
      font-size: 0.8rem; 
      font-weight: 600; 
      color: #fff;
      text-align: center;
      min-width: 35px;
    }
    .day-MON { background: #FF6B6B; }
    .day-TUE { background: #4ECDC4; }
    .day-WED { background: #45B7D1; }
    .day-THU { background: #96CEB4; }
    .day-FRI { background: #FECA57; }
    .day-SAT { background: #FF9FF3; }
    .day-SUN { background: #54A0FF; }
    /* Popup Modal */
    .modal-success-bg { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.18); align-items: center; justify-content: center; }
    .modal-success { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(247,184,1,0.13); padding: 36px 32px 28px 32px; min-width: 320px; max-width: 90vw; text-align: center; position: relative; }
    .modal-success h3 { color: #F7B801; font-size: 1.3rem; margin-bottom: 10px; }
    .modal-success p { color: #333; font-size: 1.08rem; margin-bottom: 18px; }
    .modal-success button { background: #F7B801; color: #fff; border: none; border-radius: 22px; font-size: 1.1rem; font-weight: 600; padding: 10px 32px; cursor: pointer; transition: background 0.2s; }
    .modal-success button:hover { background: #e6a800; }
    @media (max-width: 1100px) {
      .main-flex-wrap { flex-direction: column; align-items: center; gap: 32px; }
      .centered-card, .task-list-card { max-width: 98vw; min-width: 280px; margin-bottom: 32px; }
    }
    @media (max-width: 700px) {
      .main-flex-wrap { flex-direction: column; align-items: center; gap: 18px; }
      .centered-card, .task-list-card { padding: 14px 2vw; margin-bottom: 18px; }
      th, td { font-size: 0.95rem; }
    }
  </style>
</head>
<body>
  <div class="main-flex-wrap">
    <div class="centered-card">
      <a href="dashboard.php" class="back-link">Back to Dashboard</a>
      <h2 style="text-align:center; margin-bottom: 24px;"><?php echo $edit ? 'Edit Task' : 'Add Task'; ?></h2>
      <form method="POST" id="taskForm">
        <input type="hidden" name="edit_id" value="<?php echo $edit['id'] ?? ''; ?>">
        <select name="area" required>
          <option value="">Select Area</option>
          <?php $areas = ['Kitchen','Dining Area','Restroom','Storage'];
          foreach ($areas as $area): ?>
          <option value="<?php echo $area; ?>" <?php if (($edit['area'] ?? '') === $area) echo 'selected'; ?>><?php echo $area; ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="task_name" placeholder="Task Name" required value="<?php echo htmlspecialchars($edit['task_name'] ?? ''); ?>" />
        <select name="day_of_week" required>
          <option value="">Select Day</option>
          <?php $days = ['MON' => 'Monday', 'TUE' => 'Tuesday', 'WED' => 'Wednesday', 'THU' => 'Thursday', 'FRI' => 'Friday', 'SAT' => 'Saturday', 'SUN' => 'Sunday'];
          foreach ($days as $code => $name): ?>
          <option value="<?php echo $code; ?>" <?php if (($edit['day_of_week'] ?? '') === $code) echo 'selected'; ?>><?php echo $name; ?></option>
          <?php endforeach; ?>
        </select>
        <div class="form-btn-row">
          <button type="submit"><?php echo $edit ? 'Update Task' : 'Add Task'; ?></button>
          <?php if ($edit): ?>
            <a href="sanitation.php" class="back-link">Cancel Edit</a>
          <?php endif; ?>
        </div>
      </form>
      <?php if ($message) echo '<div class="msg">' . htmlspecialchars($message) . '</div>'; ?>
    </div>
    
    <div class="task-list-card">
      <h2 style="margin-top:0; text-align:center;">Sanitation Tasks</h2>
      <div class="table-wrap">
        <table>
          <tr>
            <th>Completed</th>
            <th>Area</th>
            <th>Task</th>
            <th>Day</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
          <?php foreach ($tasks as $task): ?>
          <tr class="<?php echo $task['is_completed'] ? 'completed-row' : ''; ?>">
            <td>
              <input type="checkbox" class="completion-checkbox" 
                     data-task-id="<?php echo $task['id']; ?>" 
                     <?php echo $task['is_completed'] ? 'checked' : ''; ?> />
            </td>
            <td><?php echo htmlspecialchars($task['area']); ?></td>
            <td><?php echo htmlspecialchars($task['task_name']); ?></td>
            <td><span class="day-badge day-<?php echo $task['day_of_week']; ?>"><?php echo $task['day_of_week']; ?></span></td>
            <td><?php echo date('M j, Y', strtotime($task['created_at'])); ?></td>
            <td class="actions">
              <a href="sanitation.php?edit=<?php echo $task['id']; ?>">Edit</a>
              <a href="sanitation.php?delete=<?php echo $task['id']; ?>" class="delete" onclick="return confirm('Delete this task?');">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
  </div>
  
  <!-- Success Modal Popup -->
  <div class="modal-success-bg" id="successModalBg">
    <div class="modal-success">
      <h3 id="successModalTitle">Success!</h3>
      <p id="successModalMsg">Action completed successfully.</p>
      <button onclick="closeSuccessModal()">OK</button>
    </div>
  </div>
  
  <script>
    // Show the success modal with a custom message
    function showSuccessModal(msg, title = 'Success!') {
      document.getElementById('successModalTitle').textContent = title;
      document.getElementById('successModalMsg').textContent = msg;
      document.getElementById('successModalBg').style.display = 'flex';
    }
    
    function closeSuccessModal() {
      document.getElementById('successModalBg').style.display = 'none';
      if (window._modalShouldReload) location.href = 'sanitation.php';
    }
    
    // Show popup if redirected after add/edit/delete
    <?php if ($message): ?>
      showSuccessModal('<?php echo addslashes($message); ?>');
      window._modalShouldReload = true;
    <?php endif; ?>
    
    // Handle completion checkbox toggle
    document.querySelectorAll('.completion-checkbox').forEach(function(checkbox) {
      checkbox.addEventListener('change', function() {
        var taskId = this.getAttribute('data-task-id');
        var row = this.closest('tr');
        
        fetch('sanitation.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'toggle_task=1&task_id=' + taskId
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            if (this.checked) {
              row.classList.add('completed-row');
              showSuccessModal('Task marked as completed!');
            } else {
              row.classList.remove('completed-row');
              showSuccessModal('Task marked as incomplete!');
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          this.checked = !this.checked; // Revert checkbox state
        });
      });
    });
    
    // Intercept form submit for AJAX add/edit
    document.getElementById('taskForm').onsubmit = function(e) {
      e.preventDefault();
      var form = this;
      var formData = new FormData(form);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'sanitation.php');
      xhr.onload = function() {
        if (xhr.status === 200) {
          try {
            var parser = new DOMParser();
            var doc = parser.parseFromString(xhr.responseText, 'text/html');
            var msg = doc.querySelector('.msg');
            if (msg) {
              showSuccessModal(msg.textContent);
              window._modalShouldReload = true;
            } else {
              showSuccessModal('Task saved!');
              window._modalShouldReload = true;
            }
          } catch(e) { showSuccessModal('Task saved!'); window._modalShouldReload = true; }
        }
      };
      xhr.send(formData);
    };
    
    // Intercept delete links for popup
    document.querySelectorAll('.actions a.delete').forEach(function(link) {
      link.addEventListener('click', function(e) {
        if (!confirm('Delete this task?')) return;
        e.preventDefault();
        fetch(this.href)
          .then(function() {
            showSuccessModal('Task deleted!');
            window._modalShouldReload = true;
          });
      });
    });
  </script>
</body>
</html>