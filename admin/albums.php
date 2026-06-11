<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BLACKPINK Admin — Albums</title>
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

$types = ['Studio Album','Mini Album','Single Album','Debut Single','Digital Single','Japanese EP','Japanese Album','Live Album','Compilation'];

// ── DELETE ──────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    mysqli_query($conn, "DELETE FROM $tbl_albums WHERE album_id=$id");
    $_SESSION['status'] = "Album deleted.";
    header("Location: albums.php"); exit();
}

// ── ADD ─────────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sl = mysqli_real_escape_string($conn, trim($_POST['slug']));
    $t  = mysqli_real_escape_string($conn, trim($_POST['title']));
    $rd = mysqli_real_escape_string($conn, trim($_POST['release_date']));
    $at = mysqli_real_escape_string($conn, trim($_POST['album_type']));
    $lb = mysqli_real_escape_string($conn, trim($_POST['label']));
    $gn = mysqli_real_escape_string($conn, trim($_POST['genre']));
    $ln = mysqli_real_escape_string($conn, trim($_POST['language']));
    $ci = mysqli_real_escape_string($conn, trim($_POST['cover_image']));
    $d  = mysqli_real_escape_string($conn, trim($_POST['description']));
    $sp = mysqli_real_escape_string($conn, trim($_POST['spotify_url']));
    $yt = mysqli_real_escape_string($conn, trim($_POST['youtube_url']));

    mysqli_query($conn, "INSERT INTO $tbl_albums
        (slug,title,release_date,album_type,label,genre,language,cover_image,description,spotify_url,youtube_url)
        VALUES ('$sl','$t','$rd','$at','$lb','$gn','$ln','$ci','$d','$sp','$yt')");
    $_SESSION['status'] = "Album added.";
    header("Location: albums.php"); exit();
}

// ── EDIT ────────────────────────────────────────────────────
if ($action === 'edit' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sl = mysqli_real_escape_string($conn, trim($_POST['slug']));
    $t  = mysqli_real_escape_string($conn, trim($_POST['title']));
    $rd = mysqli_real_escape_string($conn, trim($_POST['release_date']));
    $at = mysqli_real_escape_string($conn, trim($_POST['album_type']));
    $lb = mysqli_real_escape_string($conn, trim($_POST['label']));
    $gn = mysqli_real_escape_string($conn, trim($_POST['genre']));
    $ln = mysqli_real_escape_string($conn, trim($_POST['language']));
    $ci = mysqli_real_escape_string($conn, trim($_POST['cover_image']));
    $d  = mysqli_real_escape_string($conn, trim($_POST['description']));
    $sp = mysqli_real_escape_string($conn, trim($_POST['spotify_url']));
    $yt = mysqli_real_escape_string($conn, trim($_POST['youtube_url']));

    mysqli_query($conn, "UPDATE $tbl_albums SET
        slug='$sl', title='$t', release_date='$rd', album_type='$at', label='$lb',
        genre='$gn', language='$ln', cover_image='$ci', description='$d',
        spotify_url='$sp', youtube_url='$yt'
        WHERE album_id=$id");
    $_SESSION['status'] = "Album updated.";
    header("Location: albums.php"); exit();
}

// ── SEARCH / LIST ───────────────────────────────────────────
$search = '';
$where  = '';
if (!empty($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, trim($_GET['q']));
    $where  = "WHERE title LIKE '%$search%' OR album_type LIKE '%$search%' OR genre LIKE '%$search%'";
}
$rows = mysqli_query($conn, "SELECT * FROM $tbl_albums $where ORDER BY release_date DESC");

$edit_row = null;
if ($action === 'edit' && $id) {
    $q        = mysqli_query($conn, "SELECT * FROM $tbl_albums WHERE album_id=$id");
    $edit_row = mysqli_fetch_assoc($q);
}
?>

<div class="admin-wrapper">
  <?php include("../includes/sidebar.php"); ?>
  <main class="main-content">

    <div class="topbar">
      <div class="page-title">AL<span>BUMS</span></div>
      <a href="?action=add" class="btn-pink"><i class="fa-solid fa-plus"></i> Add Album</a>
    </div>

    <?php if (isset($_SESSION['status'])): ?>
      <div class="alert-bp success"><i class="fa-solid fa-circle-check"></i> <?php echo $_SESSION['status']; unset($_SESSION['status']); ?></div>
    <?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <?php if ($action === 'add' || ($action === 'edit' && $edit_row)): ?>
    <div class="form-card mb-4">
      <div class="data-card-title mb-3"><?php echo $action === 'add' ? 'Add New Album' : 'Edit Album'; ?></div>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label-bp">Slug (URL key) *</label>
            <input type="text" class="form-control-bp" name="slug" required placeholder="e.g. born-pink"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['slug']) : ''; ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Title *</label>
            <input type="text" class="form-control-bp" name="title" required placeholder="e.g. BORN PINK"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['title']) : ''; ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Release Date</label>
            <input type="date" class="form-control-bp" name="release_date"
              value="<?php echo $edit_row ? $edit_row['release_date'] : ''; ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Album Type</label>
            <select class="form-control-bp" name="album_type">
              <?php foreach ($types as $tp): ?>
                <option value="<?php echo $tp; ?>" <?php echo ($edit_row && $edit_row['album_type'] === $tp) ? 'selected' : ''; ?>>
                  <?php echo $tp; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Label</label>
            <input type="text" class="form-control-bp" name="label" placeholder="YG Entertainment"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['label']) : ''; ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Genre</label>
            <input type="text" class="form-control-bp" name="genre" placeholder="K-pop, Pop, Hip-Hop"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['genre']) : ''; ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Language</label>
            <input type="text" class="form-control-bp" name="language" placeholder="Korean, English"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['language']) : ''; ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label-bp">Cover Image Path</label>
            <input type="text" class="form-control-bp" name="cover_image" placeholder="BLACKPINK_PHOTOS/Album_BornPink.jpg"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['cover_image']) : ''; ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label-bp">Spotify URL</label>
            <input type="text" class="form-control-bp" name="spotify_url" placeholder="https://open.spotify.com/album/..."
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['spotify_url']) : ''; ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label-bp">YouTube URL</label>
            <input type="text" class="form-control-bp" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..."
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['youtube_url']) : ''; ?>">
          </div>
          <div class="col-12">
            <label class="form-label-bp">Description</label>
            <textarea class="form-control-bp" name="description" rows="4"><?php echo $edit_row ? htmlspecialchars($edit_row['description']) : ''; ?></textarea>
          </div>
        </div>
        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn-pink">
            <i class="fa-solid fa-floppy-disk"></i> <?php echo $action === 'add' ? 'Save Album' : 'Update Album'; ?>
          </button>
          <a href="albums.php" class="btn-outline-pink">Cancel</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- LIST TABLE -->
    <div class="data-card">
      <div class="data-card-header">
        <div class="data-card-title">All Albums</div>
        <form method="GET" class="search-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" class="search-input" name="q" placeholder="Search albums..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
      </div>
      <table class="table-bp">
        <thead>
          <tr><th>#</th><th>Cover</th><th>Title</th><th>Type</th><th>Release Date</th><th>Genre</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php while ($r = mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><?php echo $r['album_id']; ?></td>
            <td>
              <?php if (!empty($r['cover_image'])): ?>
                <img src="../<?php echo htmlspecialchars($r['cover_image']); ?>" class="thumb-img" alt="">
              <?php else: ?>
                <div class="thumb-placeholder"><i class="fa-solid fa-compact-disc"></i></div>
              <?php endif; ?>
            </td>
            <td style="color:var(--white);font-weight:600;"><?php echo htmlspecialchars($r['title']); ?></td>
            <td><span class="badge-pink"><?php echo htmlspecialchars($r['album_type']); ?></span></td>
            <td><?php echo date('M d, Y', strtotime($r['release_date'])); ?></td>
            <td><?php echo htmlspecialchars($r['genre']); ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="songs.php?album_id=<?php echo $r['album_id']; ?>" class="btn-icon" title="Tracklist"><i class="fa-solid fa-music"></i></a>
                <a href="?action=edit&id=<?php echo $r['album_id']; ?>" class="btn-icon" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <a href="?action=delete&id=<?php echo $r['album_id']; ?>" class="btn-icon danger" title="Delete"
                   onclick="return confirm('Delete <?php echo htmlspecialchars($r['title']); ?>?')">
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