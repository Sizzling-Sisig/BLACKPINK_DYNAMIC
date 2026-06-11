<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BLACKPINK Admin — Gallery</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<?php
include("../includes/session.php");
include("../includes/conn.php");

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── UPLOAD HELPER ────────────────────────────────────────────
function handle_upload($field = 'image') {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== 0) return null;

    $allowed  = ['jpg','jpeg','png','webp','gif'];
    $ext      = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) return 'INVALID_TYPE';

    $upload_dir = '../assets/BLACKPINK_PHOTOS/gallery/';

    // Create folder if it doesn't exist
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $filename   = 'gallery_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
    $dest       = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) return 'MOVE_FAILED';

    return 'BLACKPINK_PHOTOS/gallery/' . $filename;
}

// ── DELETE ───────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    // Also delete the physical file
    $q   = mysqli_query($conn, "SELECT image_path FROM $tbl_gallery WHERE photo_id=$id");
    $row = mysqli_fetch_assoc($q);
    if ($row && file_exists('../assets/' . $row['image_path'])) {
        unlink('../assets/' . $row['image_path']);
    }
    mysqli_query($conn, "DELETE FROM $tbl_gallery WHERE photo_id=$id");
    $_SESSION['status'] = "Photo deleted.";
    $_SESSION['status_type'] = "error";
    header("Location: gallery.php"); exit();
}

// ── ADD ──────────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_path = handle_upload('image');

    if ($image_path === null) {
        $_SESSION['status']      = "Please select an image file.";
        $_SESSION['status_type'] = "error";
        header("Location: gallery.php?action=add"); exit();
    }
    if ($image_path === 'INVALID_TYPE') {
        $_SESSION['status']      = "Invalid file type. Use JPG, PNG, WEBP, or GIF.";
        $_SESSION['status_type'] = "error";
        header("Location: gallery.php?action=add"); exit();
    }
    if ($image_path === 'MOVE_FAILED') {
        $_SESSION['status']      = "Upload failed. Check folder permissions.";
        $_SESSION['status_type'] = "error";
        header("Location: gallery.php?action=add"); exit();
    }

    $cap = mysqli_real_escape_string($conn, trim($_POST['caption']));
    $so  = (int)$_POST['sort_order'];
    $ip  = mysqli_real_escape_string($conn, $image_path);

    mysqli_query($conn, "INSERT INTO $tbl_gallery (image_path,caption,sort_order) VALUES ('$ip','$cap','$so')");
    $_SESSION['status']      = "Photo added successfully.";
    $_SESSION['status_type'] = "success";
    header("Location: gallery.php"); exit();
}

// ── EDIT ─────────────────────────────────────────────────────
if ($action === 'edit' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cap = mysqli_real_escape_string($conn, trim($_POST['caption']));
    $so  = (int)$_POST['sort_order'];

    $new_path = handle_upload('image');

    if ($new_path === 'INVALID_TYPE') {
        $_SESSION['status']      = "Invalid file type. Use JPG, PNG, WEBP, or GIF.";
        $_SESSION['status_type'] = "error";
        header("Location: gallery.php?action=edit&id=$id"); exit();
    }
    if ($new_path === 'MOVE_FAILED') {
        $_SESSION['status']      = "Upload failed. Check folder permissions.";
        $_SESSION['status_type'] = "error";
        header("Location: gallery.php?action=edit&id=$id"); exit();
    }

    if ($new_path) {
        // Delete old file
        $q   = mysqli_query($conn, "SELECT image_path FROM $tbl_gallery WHERE photo_id=$id");
        $old = mysqli_fetch_assoc($q);
        if ($old && file_exists('../assets/' . $old['image_path'])) {
            unlink('../assets/' . $old['image_path']);
        }
        $ip = mysqli_real_escape_string($conn, $new_path);
        mysqli_query($conn, "UPDATE $tbl_gallery SET image_path='$ip', caption='$cap', sort_order='$so' WHERE photo_id=$id");
    } else {
        // No new file — keep existing image_path
        mysqli_query($conn, "UPDATE $tbl_gallery SET caption='$cap', sort_order='$so' WHERE photo_id=$id");
    }

    $_SESSION['status']      = "Photo updated successfully.";
    $_SESSION['status_type'] = "success";
    header("Location: gallery.php"); exit();
}

// ── FETCH ROWS ───────────────────────────────────────────────
$rows = mysqli_query($conn, "SELECT * FROM $tbl_gallery ORDER BY sort_order ASC");

$edit_row = null;
if ($action === 'edit' && $id) {
    $q        = mysqli_query($conn, "SELECT * FROM $tbl_gallery WHERE photo_id=$id");
    $edit_row = mysqli_fetch_assoc($q);
}
?>

<div class="admin-wrapper">
  <?php include("../includes/sidebar.php"); ?>
  <main class="main-content">

    <div class="topbar">
      <div class="page-title">GAL<span>LERY</span></div>
      <a href="?action=add" class="btn-pink"><i class="fa-solid fa-plus"></i> Add Photo</a>
    </div>

    <?php if (isset($_SESSION['status'])): ?>
      <div class="alert-bp <?php echo $_SESSION['status_type'] ?? 'success'; ?>">
        <i class="fa-solid <?php echo ($_SESSION['status_type'] ?? 'success') === 'error' ? 'fa-circle-xmark' : 'fa-circle-check'; ?>"></i>
        <?php echo $_SESSION['status']; unset($_SESSION['status'], $_SESSION['status_type']); ?>
      </div>
    <?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <?php if ($action === 'add' || ($action === 'edit' && $edit_row)): ?>
    <div class="form-card mb-4">
      <div class="data-card-title mb-3">
        <i class="fa-solid fa-image" style="color:var(--pink)"></i>
        <?php echo $action === 'add' ? 'Add New Photo' : 'Edit Photo'; ?>
      </div>

      <form method="POST" action="" enctype="multipart/form-data">
        <div class="row g-3">

          <!-- FILE UPLOAD -->
          <div class="col-md-6">
            <label class="form-label-bp">
              Image File <?php echo $action === 'add' ? '*' : '(leave empty to keep current)'; ?>
            </label>
            <input type="file" class="form-control-bp" name="image"
                   accept="image/jpeg,image/png,image/webp,image/gif"
                   <?php echo $action === 'add' ? 'required' : ''; ?>
                   style="padding:0.45rem 0.9rem; cursor:pointer;">
          </div>

          <!-- CURRENT IMAGE PREVIEW (edit only) -->
          <?php if ($action === 'edit' && $edit_row && $edit_row['image_path']): ?>
          <div class="col-md-2" style="display:flex;align-items:flex-end">
            <div>
              <label class="form-label-bp">Current Image</label>
              <img src="../assets/<?php echo htmlspecialchars($edit_row['image_path']); ?>"
                   style="height:64px;width:64px;object-fit:cover;border-radius:6px;border:1px solid var(--pink-border);display:block;">
            </div>
          </div>
          <?php endif; ?>

          <!-- CAPTION -->
          <div class="col-md-<?php echo ($action === 'edit' && $edit_row) ? '2' : '4'; ?>">
            <label class="form-label-bp">Caption</label>
            <input type="text" class="form-control-bp" name="caption"
                   placeholder="Optional caption"
                   value="<?php echo $edit_row ? htmlspecialchars($edit_row['caption']) : ''; ?>">
          </div>

          <!-- SORT ORDER -->
          <div class="col-md-2">
            <label class="form-label-bp">Sort Order</label>
            <input type="number" class="form-control-bp" name="sort_order" min="0"
                   value="<?php echo $edit_row ? (int)$edit_row['sort_order'] : 0; ?>">
          </div>

        </div>

        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn-pink">
            <i class="fa-solid fa-floppy-disk"></i>
            <?php echo $action === 'add' ? 'Save Photo' : 'Update Photo'; ?>
          </button>
          <a href="gallery.php" class="btn-outline-pink">Cancel</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- GALLERY GRID -->
    <div class="data-card">
      <div class="data-card-header">
        <span class="data-card-title"><i class="fa-solid fa-images" style="color:var(--pink)"></i> All Gallery Photos</span>
        <span style="font-size:0.75rem;color:var(--muted)">
          <?php
            $count = mysqli_num_rows($rows);
            echo $count . ' photo' . ($count !== 1 ? 's' : '');
          ?>
        </span>
      </div>

      <?php if (mysqli_num_rows($rows) === 0): ?>
        <p style="color:var(--muted);text-align:center;padding:3rem 0;font-size:0.85rem">
          No photos yet. <a href="?action=add" style="color:var(--pink)">Add one →</a>
        </p>
      <?php else: ?>
      <div class="gallery-admin-grid">
        <?php
        // Reset pointer since num_rows consumed it — re-query
        $rows = mysqli_query($conn, "SELECT * FROM $tbl_gallery ORDER BY sort_order ASC");
        while ($r = mysqli_fetch_assoc($rows)):
        ?>
        <div class="gallery-admin-item">
            <img src="../assets/<?php echo htmlspecialchars($r['image_path']); ?>" alt="">          <div class="gallery-admin-overlay">
            <div class="gallery-caption"><?php echo htmlspecialchars($r['caption'] ?: 'No caption'); ?></div>
            <div class="gallery-actions">
              <a href="?action=edit&id=<?php echo $r['photo_id']; ?>" class="btn-icon" title="Edit">
                <i class="fa-solid fa-pen"></i>
              </a>
              <a href="?action=delete&id=<?php echo $r['photo_id']; ?>" class="btn-icon danger" title="Delete"
                 onclick="return confirm('Delete this photo? This cannot be undone.')">
                <i class="fa-solid fa-trash"></i>
              </a>
            </div>
          </div>
          <div class="gallery-order-badge">#<?php echo $r['sort_order']; ?></div>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>
    </div>

  </main>
</div>
</body>
</html>