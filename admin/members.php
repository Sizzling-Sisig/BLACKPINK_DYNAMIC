<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BLACKPINK Admin — Members</title>
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

// ── UPLOAD HELPER ─────────────────────────────────────────────
function upload_member_image($field, $subfolder) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== 0) return null;
    $allowed = ['jpg','jpeg','png','webp','gif'];
    $ext     = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return 'INVALID';
    $dir = "../assets/BLACKPINK_PHOTOS/members/$subfolder/";
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = $field . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dir . $name)) return 'FAILED';
    return "assets/BLACKPINK_PHOTOS/members/$subfolder/" . $name;
}

// ── DELETE ────────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    mysqli_query($conn, "DELETE FROM $tbl_members WHERE member_id=$id");
    $_SESSION['status']      = "Member deleted.";
    $_SESSION['status_type'] = "error";
    header("Location: members.php"); exit();
}

// ── ADD ───────────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug']);

    $pc = upload_member_image('photo_card', $slug);
    $mi = upload_member_image('main_image', $slug);

    if ($pc === 'INVALID' || $mi === 'INVALID') {
        $_SESSION['status']      = "Invalid file type. Use JPG, PNG, WEBP, or GIF.";
        $_SESSION['status_type'] = "error";
        header("Location: members.php?action=add"); exit();
    }
    if ($pc === 'FAILED' || $mi === 'FAILED') {
        $_SESSION['status']      = "Upload failed. Check folder permissions.";
        $_SESSION['status_type'] = "error";
        header("Location: members.php?action=add"); exit();
    }
    if (!$pc || !$mi) {
        $_SESSION['status']      = "Both Photo Card and Main Image are required.";
        $_SESSION['status_type'] = "error";
        header("Location: members.php?action=add"); exit();
    }

    $slug  = mysqli_real_escape_string($conn, $slug);
    $sn    = mysqli_real_escape_string($conn, trim($_POST['stage_name']));
    $rn    = mysqli_real_escape_string($conn, trim($_POST['real_name']));
    $role  = mysqli_real_escape_string($conn, trim($_POST['role']));
    $bd    = mysqli_real_escape_string($conn, trim($_POST['birthday']));
    $nat   = mysqli_real_escape_string($conn, trim($_POST['nationality']));
    $ht    = mysqli_real_escape_string($conn, trim($_POST['height']));
    $st    = mysqli_real_escape_string($conn, trim($_POST['status']));
    $about = mysqli_real_escape_string($conn, trim($_POST['about']));
    $pc    = mysqli_real_escape_string($conn, $pc);
    $mi    = mysqli_real_escape_string($conn, $mi);

    mysqli_query($conn, "INSERT INTO $tbl_members
        (slug,stage_name,real_name,role,birthday,nationality,height,status,about,main_image,photo_card)
        VALUES ('$slug','$sn','$rn','$role','$bd','$nat','$ht','$st','$about','$mi','$pc')");
    $_SESSION['status']      = "Member added successfully.";
    $_SESSION['status_type'] = "success";
    header("Location: members.php"); exit();
}

// ── EDIT ──────────────────────────────────────────────────────
if ($action === 'edit' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug']);

    // Fetch current image paths as fallback
    $cur = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT photo_card, main_image FROM $tbl_members WHERE member_id=$id"));

    $new_pc = upload_member_image('photo_card', $slug);
    $new_mi = upload_member_image('main_image', $slug);

    $pc = ($new_pc && $new_pc !== 'INVALID' && $new_pc !== 'FAILED')
          ? $new_pc : $cur['photo_card'];
    $mi = ($new_mi && $new_mi !== 'INVALID' && $new_mi !== 'FAILED')
          ? $new_mi : $cur['main_image'];

    $slug  = mysqli_real_escape_string($conn, $slug);
    $sn    = mysqli_real_escape_string($conn, trim($_POST['stage_name']));
    $rn    = mysqli_real_escape_string($conn, trim($_POST['real_name']));
    $role  = mysqli_real_escape_string($conn, trim($_POST['role']));
    $bd    = mysqli_real_escape_string($conn, trim($_POST['birthday']));
    $nat   = mysqli_real_escape_string($conn, trim($_POST['nationality']));
    $ht    = mysqli_real_escape_string($conn, trim($_POST['height']));
    $st    = mysqli_real_escape_string($conn, trim($_POST['status']));
    $about = mysqli_real_escape_string($conn, trim($_POST['about']));
    $pc    = mysqli_real_escape_string($conn, $pc);
    $mi    = mysqli_real_escape_string($conn, $mi);

    mysqli_query($conn, "UPDATE $tbl_members SET
        slug='$slug', stage_name='$sn', real_name='$rn', role='$role',
        birthday='$bd', nationality='$nat', height='$ht', status='$st',
        about='$about', main_image='$mi', photo_card='$pc'
        WHERE member_id=$id");
    $_SESSION['status']      = "Member updated successfully.";
    $_SESSION['status_type'] = "success";
    header("Location: members.php"); exit();
}

// ── SEARCH / LIST ─────────────────────────────────────────────
$search = '';
$where  = '';
if (!empty($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, trim($_GET['q']));
    $where  = "WHERE stage_name LIKE '%$search%' OR real_name LIKE '%$search%'
               OR role LIKE '%$search%' OR status LIKE '%$search%'";
}
$rows = mysqli_query($conn, "SELECT * FROM $tbl_members $where ORDER BY member_id");

$edit_row = null;
if ($action === 'edit' && $id) {
    $q        = mysqli_query($conn, "SELECT * FROM $tbl_members WHERE member_id=$id");
    $edit_row = mysqli_fetch_assoc($q);
}

$statuses = ['Active Group', 'Active Solo', 'Hiatus', 'On Leave', 'Former Member'];
?>

<div class="admin-wrapper">
  <?php include("../includes/sidebar.php"); ?>
  <main class="main-content">

    <div class="topbar">
      <div class="page-title">MEM<span>BERS</span></div>
      <a href="?action=add" class="btn-pink"><i class="fa-solid fa-plus"></i> Add Member</a>
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
        <i class="fa-solid fa-user" style="color:var(--pink)"></i>
        <?php echo $action === 'add' ? 'Add New Member' : 'Edit: ' . htmlspecialchars($edit_row['stage_name']); ?>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="row g-3">

          <!-- SLUG -->
          <div class="col-md-4">
            <label class="form-label-bp">Slug (URL key) *</label>
            <input type="text" class="form-control-bp" name="slug" required
                   placeholder="e.g. jisoo"
                   value="<?php echo $edit_row ? htmlspecialchars($edit_row['slug']) : ''; ?>">
          </div>

          <!-- STAGE NAME -->
          <div class="col-md-4">
            <label class="form-label-bp">Stage Name *</label>
            <input type="text" class="form-control-bp" name="stage_name" required
                   placeholder="e.g. JISOO"
                   value="<?php echo $edit_row ? htmlspecialchars($edit_row['stage_name']) : ''; ?>">
          </div>

          <!-- REAL NAME -->
          <div class="col-md-4">
            <label class="form-label-bp">Real Name *</label>
            <input type="text" class="form-control-bp" name="real_name" required
                   placeholder="e.g. Kim Ji-soo"
                   value="<?php echo $edit_row ? htmlspecialchars($edit_row['real_name']) : ''; ?>">
          </div>

          <!-- ROLE -->
          <div class="col-md-4">
            <label class="form-label-bp">Role / Position *</label>
            <input type="text" class="form-control-bp" name="role" required
                   placeholder="e.g. Lead Vocalist, Visual"
                   value="<?php echo $edit_row ? htmlspecialchars($edit_row['role']) : ''; ?>">
          </div>

          <!-- BIRTHDAY -->
          <div class="col-md-4">
            <label class="form-label-bp">Birthday</label>
            <input type="date" class="form-control-bp" name="birthday"
                   value="<?php echo $edit_row ? $edit_row['birthday'] : ''; ?>">
          </div>

          <!-- NATIONALITY -->
          <div class="col-md-4">
            <label class="form-label-bp">Nationality</label>
            <input type="text" class="form-control-bp" name="nationality"
                   placeholder="e.g. South Korean"
                   value="<?php echo $edit_row ? htmlspecialchars($edit_row['nationality']) : ''; ?>">
          </div>

          <!-- HEIGHT -->
          <div class="col-md-4">
            <label class="form-label-bp">Height</label>
            <input type="text" class="form-control-bp" name="height"
                   placeholder='e.g. 162 cm'
                   value="<?php echo $edit_row ? htmlspecialchars($edit_row['height']) : ''; ?>">
          </div>

          <!-- STATUS -->
          <div class="col-md-4">
            <label class="form-label-bp">Status</label>
            <select class="form-control-bp" name="status">
              <?php foreach ($statuses as $s): ?>
                <option value="<?php echo $s; ?>"
                  <?php echo ($edit_row && $edit_row['status'] === $s) ? 'selected' : ''; ?>>
                  <?php echo $s; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- PHOTO CARD UPLOAD -->
          <div class="col-md-4">
            <label class="form-label-bp">
              Photo Card Image <?php echo $action === 'add' ? '*' : '(leave empty to keep current)'; ?>
            </label>
            <input type="file" class="form-control-bp" name="photo_card"
                   accept="image/*"
                   style="padding:0.45rem 0.9rem;cursor:pointer;"
                   <?php echo $action === 'add' ? 'required' : ''; ?>>
            <?php if ($action === 'edit' && !empty($edit_row['photo_card'])): ?>
              <div style="margin-top:8px;display:flex;align-items:center;gap:8px">
                <img src="../<?php echo htmlspecialchars($edit_row['photo_card']); ?>"
                     style="height:56px;width:56px;object-fit:cover;border-radius:6px;border:1px solid var(--pink-border)">
                <span style="font-size:0.7rem;color:var(--muted)">Current photo card</span>
              </div>
            <?php endif; ?>
          </div>

          <!-- MAIN IMAGE UPLOAD -->
          <div class="col-md-4">
            <label class="form-label-bp">
              Main Image (Detail Page) <?php echo $action === 'add' ? '*' : '(leave empty to keep current)'; ?>
            </label>
            <input type="file" class="form-control-bp" name="main_image"
                   accept="image/*"
                   style="padding:0.45rem 0.9rem;cursor:pointer;"
                   <?php echo $action === 'add' ? 'required' : ''; ?>>
            <?php if ($action === 'edit' && !empty($edit_row['main_image'])): ?>
              <div style="margin-top:8px;display:flex;align-items:center;gap:8px">
                <img src="../<?php echo htmlspecialchars($edit_row['main_image']); ?>"
                     style="height:56px;width:80px;object-fit:cover;border-radius:6px;border:1px solid var(--pink-border)">
                <span style="font-size:0.7rem;color:var(--muted)">Current main image</span>
              </div>
            <?php endif; ?>
          </div>

          <!-- ABOUT -->
          <div class="col-12">
            <label class="form-label-bp">About / Bio</label>
            <textarea class="form-control-bp" name="about" rows="5"
              ><?php echo $edit_row ? htmlspecialchars($edit_row['about']) : ''; ?></textarea>
          </div>

        </div>

        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn-pink">
            <i class="fa-solid fa-floppy-disk"></i>
            <?php echo $action === 'add' ? 'Save Member' : 'Update Member'; ?>
          </button>
          <a href="members.php" class="btn-outline-pink">Cancel</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- LIST TABLE -->
    <div class="data-card">
      <div class="data-card-header">
        <div class="data-card-title">
          <i class="fa-solid fa-users" style="color:var(--pink)"></i> All Members
        </div>
        <form method="GET" class="search-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" class="search-input" name="q"
                 placeholder="Search members..."
                 value="<?php echo htmlspecialchars($search); ?>">
        </form>
      </div>
      <table class="table-bp">
        <thead>
          <tr>
            <th>#</th>
            <th>Photo</th>
            <th>Stage Name</th>
            <th>Real Name</th>
            <th>Role</th>
            <th>Status</th>
            <th>Birthday</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($r = mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><?php echo $r['member_id']; ?></td>
            <td>
              <?php if (!empty($r['photo_card'])): ?>
                <img src="../<?php echo htmlspecialchars($r['photo_card']); ?>"
                     class="thumb-img" alt="">
              <?php else: ?>
                <div class="thumb-placeholder"><i class="fa-solid fa-user"></i></div>
              <?php endif; ?>
            </td>
            <td style="color:var(--white);font-weight:600">
              <?php echo htmlspecialchars($r['stage_name']); ?>
            </td>
            <td><?php echo htmlspecialchars($r['real_name']); ?></td>
            <td><span class="badge-pink"><?php echo htmlspecialchars($r['role']); ?></span></td>
            <td>
              <?php
              $sc  = ['Active Group'=>'badge-green','Active Solo'=>'badge-blue',
                      'Hiatus'=>'badge-gray','Military Service'=>'badge-yellow'];
              $cls = $sc[$r['status']] ?? 'badge-gray';
              ?>
              <span class="<?php echo $cls; ?>"><?php echo htmlspecialchars($r['status']); ?></span>
            </td>
            <td><?php echo $r['birthday'] ? date('M d, Y', strtotime($r['birthday'])) : '—'; ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="?action=edit&id=<?php echo $r['member_id']; ?>"
                   class="btn-icon" title="Edit">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <a href="?action=delete&id=<?php echo $r['member_id']; ?>"
                   class="btn-icon danger" title="Delete"
                   onclick="return confirm('Delete <?php echo htmlspecialchars($r['stage_name']); ?>?')">
                  <i class="fa-solid fa-trash"></i>
                </a>
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