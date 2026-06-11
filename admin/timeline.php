<?php
require '../includes/auth_check.php';
require '../includes/conn.php';

$msg = '';

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM timeline WHERE id = $id");
    header('Location: timeline.php?deleted=1'); exit;
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $year_label  = mysqli_real_escape_string($conn, trim($_POST['year_label']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $sort_order  = (int)$_POST['sort_order'];

    if ($id > 0) {
        mysqli_query($conn, "UPDATE timeline SET year_label='$year_label', description='$description', sort_order=$sort_order WHERE id=$id");
        $msg = 'updated';
    } else {
        mysqli_query($conn, "INSERT INTO timeline (year_label, description, sort_order) VALUES ('$year_label','$description',$sort_order)");
        $msg = 'added';
    }
    header('Location: timeline.php?msg=' . $msg); exit;
}

// EDIT PRE-FILL
$edit = null;
if (isset($_GET['edit'])) {
    $id   = (int)$_GET['edit'];
    $res  = mysqli_query($conn, "SELECT * FROM timeline WHERE id=$id");
    $edit = mysqli_fetch_assoc($res);
}

$rows = mysqli_query($conn, "SELECT * FROM timeline ORDER BY sort_order ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Timeline — BLACKPINK Admin</title>
  <link rel="stylesheet" href="../assets/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
  <?php include '../includes/sidebar.php'; ?>
  <main class="main-content">

    <div class="topbar">
      <h1 class="page-title">TIME<span>LINE</span></h1>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert-bp <?= $_GET['msg'] === 'deleted' ? 'error' : 'success' ?>">
      <i class="fas fa-check-circle"></i>
      <?= $_GET['msg'] === 'added' ? 'Entry added.' : ($_GET['msg'] === 'updated' ? 'Entry updated.' : 'Entry deleted.') ?>
    </div>
    <?php endif; ?>

    <!-- FORM -->
    <div class="form-card mb-4">
      <div class="data-card-header">
        <span class="data-card-title"><i class="fas fa-clock"></i> <?= $edit ? 'Edit Entry' : 'Add Entry' ?></span>
      </div>
      <form method="POST">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label-bp">Year / Label</label>
            <input type="text" name="year_label" class="form-control-bp"
              placeholder="e.g. 2016 - DEBUT"
              value="<?= $edit ? htmlspecialchars($edit['year_label']) : '' ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label-bp">Sort Order</label>
            <input type="number" name="sort_order" class="form-control-bp"
              value="<?= $edit ? (int)$edit['sort_order'] : 0 ?>">
          </div>
          <div class="col-12">
            <label class="form-label-bp">Description</label>
            <textarea name="description" class="form-control-bp" rows="3" required><?= $edit ? htmlspecialchars($edit['description']) : '' ?></textarea>
          </div>
          <div class="col-12">
            <button type="submit" class="btn-pink">
              <i class="fas fa-save"></i> <?= $edit ? 'Update Entry' : 'Add Entry' ?>
            </button>
            <?php if ($edit): ?>
            <a href="timeline.php" class="btn-outline-pink" style="margin-left:8px">Cancel</a>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>

    <!-- TABLE -->
    <div class="data-card">
      <div class="data-card-header">
        <span class="data-card-title"><i class="fas fa-list"></i> All Timeline Entries</span>
      </div>
      <table class="table-bp">
        <thead>
          <tr>
            <th>#</th>
            <th>Year / Label</th>
            <th>Description</th>
            <th>Order</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td style="color:var(--pink);font-weight:600"><?= htmlspecialchars($row['year_label']) ?></td>
            <td style="color:#aaa;max-width:420px"><?= htmlspecialchars(substr($row['description'], 0, 100)) ?>...</td>
            <td><?= $row['sort_order'] ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="timeline.php?edit=<?= $row['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                <a href="timeline.php?delete=<?= $row['id'] ?>" class="btn-icon danger"
                   onclick="return confirm('Delete this entry?')" title="Delete"><i class="fas fa-trash"></i></a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>
</body>
</html>