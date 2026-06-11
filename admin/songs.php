<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BLACKPINK Admin — Songs</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<?php
include("../includes/session.php");
include("../includes/conn.php");

$action   = $_GET['action']   ?? 'list';
$id       = isset($_GET['id'])       ? (int)$_GET['id']       : 0;
$album_id = isset($_GET['album_id']) ? (int)$_GET['album_id'] : 0;

// ── DELETE ──────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT album_id FROM $tbl_songs WHERE song_id=$id"));
    $back_album = $row ? $row['album_id'] : 0;
    mysqli_query($conn, "DELETE FROM $tbl_songs WHERE song_id=$id");
    $_SESSION['status'] = "Song deleted.";
    header("Location: songs.php?album_id=$back_album"); exit();
}

// ── ADD ─────────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $aid = (int)$_POST['album_id'];
    $tn  = (int)$_POST['track_number'];
    $tt  = mysqli_real_escape_string($conn, trim($_POST['title']));
    $dur = mysqli_real_escape_string($conn, trim($_POST['duration']));
    mysqli_query($conn, "INSERT INTO $tbl_songs (album_id,track_number,title,duration)
                         VALUES ('$aid','$tn','$tt','$dur')");
    $_SESSION['status'] = "Song added.";
    header("Location: songs.php?album_id=$aid"); exit();
}

// ── EDIT ────────────────────────────────────────────────────
if ($action === 'edit' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $aid = (int)$_POST['album_id'];
    $tn  = (int)$_POST['track_number'];
    $tt  = mysqli_real_escape_string($conn, trim($_POST['title']));
    $dur = mysqli_real_escape_string($conn, trim($_POST['duration']));
    mysqli_query($conn, "UPDATE $tbl_songs SET album_id='$aid', track_number='$tn', title='$tt', duration='$dur'
                         WHERE song_id=$id");
    $_SESSION['status'] = "Song updated.";
    header("Location: songs.php?album_id=$aid"); exit();
}

// ── LIST ────────────────────────────────────────────────────
$search = '';
$where_parts = [];
if ($album_id) $where_parts[] = "s.album_id=$album_id";
if (!empty($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, trim($_GET['q']));
    $where_parts[] = "(s.title LIKE '%$search%' OR a.title LIKE '%$search%')";
}
$where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
$rows = mysqli_query($conn, "SELECT s.*, a.title AS album_title FROM $tbl_songs s
        JOIN $tbl_albums a ON s.album_id = a.album_id
        $where ORDER BY a.release_date DESC, s.track_number ASC");

$edit_row   = null;
$album_list = mysqli_query($conn, "SELECT album_id, title FROM $tbl_albums ORDER BY release_date DESC");
$albums_arr = [];
while ($al = mysqli_fetch_assoc($album_list)) $albums_arr[] = $al;

if ($action === 'edit' && $id) {
    $q        = mysqli_query($conn, "SELECT * FROM $tbl_songs WHERE song_id=$id");
    $edit_row = mysqli_fetch_assoc($q);
}

$filter_album = null;
if ($album_id) {
    $fa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT title FROM $tbl_albums WHERE album_id=$album_id"));
    $filter_album = $fa['title'] ?? null;
}
?>

<div class="admin-wrapper">
  <?php include("../includes/sidebar.php"); ?>
  <main class="main-content">

    <div class="topbar">
      <div class="page-title">SONG<span>S</span></div>
      <a href="?action=add<?php echo $album_id ? "&album_id=$album_id" : ''; ?>" class="btn-pink">
        <i class="fa-solid fa-plus"></i> Add Song
      </a>
    </div>

    <?php if (isset($_SESSION['status'])): ?>
      <div class="alert-bp success"><i class="fa-solid fa-circle-check"></i> <?php echo $_SESSION['status']; unset($_SESSION['status']); ?></div>
    <?php endif; ?>

    <?php if ($filter_album): ?>
      <div class="alert-bp info">
        <i class="fa-solid fa-filter"></i> Showing tracklist for: <strong><?php echo htmlspecialchars($filter_album); ?></strong>
        &nbsp;<a href="songs.php" style="color:var(--hot-pink);">Clear filter</a>
      </div>
    <?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <?php if ($action === 'add' || ($action === 'edit' && $edit_row)): ?>
    <div class="form-card mb-4">
      <div class="data-card-title mb-3"><?php echo $action === 'add' ? 'Add New Song' : 'Edit Song'; ?></div>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label-bp">Album *</label>
            <select class="form-control-bp" name="album_id" required>
              <?php foreach ($albums_arr as $al): ?>
                <option value="<?php echo $al['album_id']; ?>"
                  <?php echo (($edit_row && $edit_row['album_id'] == $al['album_id']) || ($action === 'add' && $album_id == $al['album_id'])) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($al['title']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label-bp">Track #</label>
            <input type="number" class="form-control-bp" name="track_number" min="1"
              value="<?php echo $edit_row ? $edit_row['track_number'] : '1'; ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label-bp">Song Title *</label>
            <input type="text" class="form-control-bp" name="title" required placeholder="e.g. Lovesick Girls"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['title']) : ''; ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label-bp">Duration</label>
            <input type="text" class="form-control-bp" name="duration" placeholder="3:36"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['duration']) : ''; ?>">
          </div>
        </div>
        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn-pink">
            <i class="fa-solid fa-floppy-disk"></i> <?php echo $action === 'add' ? 'Save Song' : 'Update Song'; ?>
          </button>
          <a href="songs.php<?php echo $album_id ? "?album_id=$album_id" : ''; ?>" class="btn-outline-pink">Cancel</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- LIST TABLE -->
    <div class="data-card">
      <div class="data-card-header">
        <div class="data-card-title">All Songs</div>
        <form method="GET" class="search-wrap">
          <?php if ($album_id): ?><input type="hidden" name="album_id" value="<?php echo $album_id; ?>"><?php endif; ?>
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" class="search-input" name="q" placeholder="Search songs..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
      </div>
      <table class="table-bp">
        <thead>
          <tr><th>#</th><th>Track</th><th>Title</th><th>Album</th><th>Duration</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php while ($r = mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><?php echo $r['song_id']; ?></td>
            <td><?php echo $r['track_number']; ?></td>
            <td style="color:var(--white);font-weight:600;"><?php echo htmlspecialchars($r['title']); ?></td>
            <td><span class="badge-pink"><?php echo htmlspecialchars($r['album_title']); ?></span></td>
            <td><?php echo htmlspecialchars($r['duration'] ?: '—'); ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="?action=edit&id=<?php echo $r['song_id']; ?>&album_id=<?php echo $r['album_id']; ?>" class="btn-icon" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <a href="?action=delete&id=<?php echo $r['song_id']; ?>" class="btn-icon danger" title="Delete"
                   onclick="return confirm('Delete &quot;<?php echo htmlspecialchars($r['title']); ?>&quot;?')">
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