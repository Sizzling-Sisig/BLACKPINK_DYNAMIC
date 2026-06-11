<?php
require '../includes/auth_check.php';
require '../includes/conn.php';

$msg = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM awards WHERE id = $id");
    header('Location: awards.php?msg=deleted'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $icon_class  = mysqli_real_escape_string($conn, trim($_POST['icon_class']));
    $title       = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $sort_order  = (int)$_POST['sort_order'];

    if ($id > 0) {
        mysqli_query($conn, "UPDATE awards SET icon_class='$icon_class', title='$title', description='$description', sort_order=$sort_order WHERE id=$id");
        $msg = 'updated';
    } else {
        mysqli_query($conn, "INSERT INTO awards (icon_class, title, description, sort_order) VALUES ('$icon_class','$title','$description',$sort_order)");
        $msg = 'added';
    }
    header('Location: awards.php?msg=' . $msg); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $id   = (int)$_GET['edit'];
    $res  = mysqli_query($conn, "SELECT * FROM awards WHERE id=$id");
    $edit = mysqli_fetch_assoc($res);
}

$rows = mysqli_query($conn, "SELECT * FROM awards ORDER BY sort_order ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Awards — BLACKPINK Admin</title>
  <link rel="stylesheet" href="../assets/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
  <?php include '../includes/sidebar.php'; ?>
  <main class="main-content">

    <div class="topbar">
      <h1 class="page-title">A<span>WARDS</span></h1>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert-bp <?= $_GET['msg'] === 'deleted' ? 'error' : 'success' ?>">
      <i class="fas fa-check-circle"></i>
      <?= $_GET['msg'] === 'added' ? 'Award added.' : ($_GET['msg'] === 'updated' ? 'Award updated.' : 'Award deleted.') ?>
    </div>
    <?php endif; ?>

    <!-- FORM -->
    <div class="form-card mb-4">
      <div class="data-card-header">
        <span class="data-card-title"><i class="fas fa-trophy"></i> <?= $edit ? 'Edit Award' : 'Add Award' ?></span>
      </div>
      <form method="POST">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label-bp">Title</label>
            <input type="text" name="title" class="form-control-bp"
              placeholder="e.g. MTV VMAs"
              value="<?= $edit ? htmlspecialchars($edit['title']) : '' ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Icon Class <span style="color:var(--muted);font-size:0.65rem">(FontAwesome)</span></label>
            <input type="text" name="icon_class" class="form-control-bp"
              placeholder="e.g. fas fa-trophy"
              value="<?= $edit ? htmlspecialchars($edit['icon_class']) : '' ?>" required>
          </div>
          <div class="col-md-2">
            <label class="form-label-bp">Sort Order</label>
            <input type="number" name="sort_order" class="form-control-bp"
              value="<?= $edit ? (int)$edit['sort_order'] : 0 ?>">
          </div>
          <div class="col-12">
            <label class="form-label-bp">Description</label>
            <input type="text" name="description" class="form-control-bp"
              placeholder="e.g. 2022 - Best Metaverse Performance"
              value="<?= $edit ? htmlspecialchars($edit['description']) : '' ?>" required>
          </div>
          <div class="col-12">
            <button type="submit" class="btn-pink">
              <i class="fas fa-save"></i> <?= $edit ? 'Update Award' : 'Add Award' ?>
            </button>
            <?php if ($edit): ?>
            <a href="awards.php" class="btn-outline-pink" style="margin-left:8px">Cancel</a>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>

    <!-- TABLE -->
    <div class="data-card">
      <div class="data-card-header">
        <span class="data-card-title"><i class="fas fa-list"></i> All Awards</span>
      </div>
      <table class="table-bp">
        <thead>
          <tr>
            <th>#</th>
            <th>Icon</th>
            <th>Title</th>
            <th>Description</th>
            <th>Order</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><i class="<?= htmlspecialchars($row['icon_class']) ?>" style="color:var(--pink);font-size:1.2rem"></i></td>
            <td style="color:var(--pink);font-weight:600"><?= htmlspecialchars($row['title']) ?></td>
            <td style="color:#aaa"><?= htmlspecialchars($row['description']) ?></td>
            <td><?= $row['sort_order'] ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="awards.php?edit=<?= $row['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                <a href="awards.php?delete=<?= $row['id'] ?>" class="btn-icon danger"
                   onclick="return confirm('Delete this award?')" title="Delete"><i class="fas fa-trash"></i></a>
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