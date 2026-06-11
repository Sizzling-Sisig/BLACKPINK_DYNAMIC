<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BLACKPINK Admin — Events</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<?php
include("../includes/auth_check.php");
include("../includes/conn.php");

$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$event_types = ['Concert','Festival','Fan Meeting','Award Show','Tour'];
$statuses    = ['upcoming','recent','past'];

// ── DELETE ──────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    mysqli_query($conn, "DELETE FROM $tbl_events WHERE event_id=$id");
    $_SESSION['status'] = "Event deleted.";
    header("Location: events.php"); exit();
}

// ── ADD ─────────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $en  = mysqli_real_escape_string($conn, trim($_POST['title']));
    $ed  = mysqli_real_escape_string($conn, trim($_POST['event_date']));
    $ede = mysqli_real_escape_string($conn, trim($_POST['event_date_end']));
    $v   = mysqli_real_escape_string($conn, trim($_POST['venue']));
    $lo  = mysqli_real_escape_string($conn, trim($_POST['location']));
    $tu  = mysqli_real_escape_string($conn, trim($_POST['ticket_url']));
    $th  = mysqli_real_escape_string($conn, trim($_POST['thumbnail']));
    $et  = mysqli_real_escape_string($conn, trim($_POST['event_type']));
    $st  = mysqli_real_escape_string($conn, trim($_POST['status']));
    $de  = mysqli_real_escape_string($conn, trim($_POST['description']));
    $ede_val = $ede ? "'$ede'" : 'NULL';
    $tu_val  = $tu  ? "'$tu'"  : 'NULL';
    mysqli_query($conn, "INSERT INTO $tbl_events
        (title,event_date,event_date_end,venue,location,ticket_url,thumbnail,event_type,status,description)
        VALUES ('$en','$ed',$ede_val,'$v','$lo',$tu_val,'$th','$et','$st','$de')");
    $_SESSION['status'] = "Event added.";
    header("Location: events.php"); exit();
}

// ── EDIT ────────────────────────────────────────────────────
if ($action === 'edit' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $en  = mysqli_real_escape_string($conn, trim($_POST['title']));
    $ed  = mysqli_real_escape_string($conn, trim($_POST['event_date']));
    $ede = mysqli_real_escape_string($conn, trim($_POST['event_date_end']));
    $v   = mysqli_real_escape_string($conn, trim($_POST['venue']));
    $lo  = mysqli_real_escape_string($conn, trim($_POST['location']));
    $tu  = mysqli_real_escape_string($conn, trim($_POST['ticket_url']));
    $th  = mysqli_real_escape_string($conn, trim($_POST['thumbnail']));
    $et  = mysqli_real_escape_string($conn, trim($_POST['event_type']));
    $st  = mysqli_real_escape_string($conn, trim($_POST['status']));
    $de  = mysqli_real_escape_string($conn, trim($_POST['description']));
    $ede_val = $ede ? "'$ede'" : 'NULL';
    $tu_val  = $tu  ? "'$tu'"  : 'NULL';
    mysqli_query($conn, "UPDATE $tbl_events SET
        title='$en', event_date='$ed', event_date_end=$ede_val,
        venue='$v', location='$lo', ticket_url=$tu_val, thumbnail='$th',
        event_type='$et', status='$st', description='$de'
        WHERE event_id=$id");
    $_SESSION['status'] = "Event updated.";
    header("Location: events.php"); exit();
}

// ── SEARCH / LIST ───────────────────────────────────────────
$search = '';
$where  = '';
if (!empty($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, trim($_GET['q']));
    $where  = "WHERE title LIKE '%$search%' OR venue LIKE '%$search%'
               OR location LIKE '%$search%' OR status LIKE '%$search%'";
}
$rows = mysqli_query($conn, "SELECT * FROM $tbl_events $where ORDER BY event_date ASC");

$edit_row = null;
if ($action === 'edit' && $id) {
    $q        = mysqli_query($conn, "SELECT * FROM $tbl_events WHERE event_id=$id");
    $edit_row = mysqli_fetch_assoc($q);
}
?>

<div class="admin-wrapper">
  <?php include("../includes/sidebar.php"); ?>
  <main class="main-content">

    <div class="topbar">
      <div class="page-title">E<span>VENTS</span></div>
      <a href="?action=add" class="btn-pink"><i class="fa-solid fa-plus"></i> Add Event</a>
    </div>

    <?php if (isset($_SESSION['status'])): ?>
      <div class="alert-bp success"><i class="fa-solid fa-circle-check"></i> <?php echo $_SESSION['status']; unset($_SESSION['status']); ?></div>
    <?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <?php if ($action === 'add' || ($action === 'edit' && $edit_row)): ?>
    <div class="form-card mb-4">
      <div class="data-card-title mb-3"><?php echo $action === 'add' ? 'Add New Event' : 'Edit Event'; ?></div>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label-bp">Event Title *</label>
            <input type="text" class="form-control-bp" name="title" required
              placeholder="e.g. BLACKPINK World Tour — Deadline"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['title']) : ''; ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label-bp">Start Date *</label>
            <input type="date" class="form-control-bp" name="event_date" required
              value="<?php echo $edit_row ? $edit_row['event_date'] : ''; ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label-bp">End Date <small>(optional)</small></label>
            <input type="date" class="form-control-bp" name="event_date_end"
              value="<?php echo $edit_row ? $edit_row['event_date_end'] : ''; ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Venue</label>
            <input type="text" class="form-control-bp" name="venue"
              placeholder="e.g. Kai Tak Stadium"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['venue']) : ''; ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Location</label>
            <input type="text" class="form-control-bp" name="location"
              placeholder="e.g. Hong Kong"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['location']) : ''; ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Event Type</label>
            <select class="form-control-bp" name="event_type">
              <?php foreach ($event_types as $et): ?>
                <option value="<?php echo $et; ?>"
                  <?php echo ($edit_row && $edit_row['event_type'] === $et) ? 'selected' : ''; ?>>
                  <?php echo $et; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label-bp">Status</label>
            <select class="form-control-bp" name="status">
              <?php foreach ($statuses as $st): ?>
                <option value="<?php echo $st; ?>"
                  <?php echo ($edit_row && $edit_row['status'] === $st) ? 'selected' : ''; ?>>
                  <?php echo ucfirst($st); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label-bp">Ticket URL <small>(optional)</small></label>
            <input type="text" class="form-control-bp" name="ticket_url" placeholder="https://..."
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['ticket_url']) : ''; ?>">
          </div>
          <div class="col-12">
            <label class="form-label-bp">Thumbnail Image Path</label>
            <input type="text" class="form-control-bp" name="thumbnail"
              placeholder="assets/BLACKPINK_PHOTOS/events/Upcoming_Concert1.png"
              value="<?php echo $edit_row ? htmlspecialchars($edit_row['thumbnail']) : ''; ?>">
          </div>
          <div class="col-12">
            <label class="form-label-bp">Description</label>
            <textarea class="form-control-bp" name="description" rows="3"
              ><?php echo $edit_row ? htmlspecialchars($edit_row['description']) : ''; ?></textarea>
          </div>
        </div>
        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn-pink">
            <i class="fa-solid fa-floppy-disk"></i>
            <?php echo $action === 'add' ? 'Save Event' : 'Update Event'; ?>
          </button>
          <a href="events.php" class="btn-outline-pink">Cancel</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- LIST TABLE -->
    <div class="data-card">
      <div class="data-card-header">
        <div class="data-card-title"><i class="fa-solid fa-calendar-days"></i> All Events</div>
        <form method="GET" class="search-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" class="search-input" name="q"
            placeholder="Search events..."
            value="<?php echo htmlspecialchars($search); ?>">
        </form>
      </div>
      <table class="table-bp">
        <thead>
          <tr>
            <th>#</th><th>Thumb</th><th>Title</th><th>Date</th>
            <th>Venue</th><th>Type</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($r = mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><?php echo $r['event_id']; ?></td>
            <td>
              <?php if (!empty($r['thumbnail'])): ?>
                <img src="../<?php echo htmlspecialchars($r['thumbnail']); ?>" class="thumb-img" alt="">
              <?php else: ?>
                <div class="thumb-placeholder"><i class="fa-solid fa-calendar"></i></div>
              <?php endif; ?>
            </td>
            <td style="color:var(--white);font-weight:600;">
              <?php echo htmlspecialchars($r['title']); ?>
            </td>
            <td>
              <?php
                echo date('M d, Y', strtotime($r['event_date']));
                if (!empty($r['event_date_end']))
                  echo '<br><small style="color:var(--muted);">to ' . date('M d, Y', strtotime($r['event_date_end'])) . '</small>';
              ?>
            </td>
            <td>
              <?php echo htmlspecialchars($r['venue']); ?>
              <br><small style="color:var(--muted);"><?php echo htmlspecialchars($r['location']); ?></small>
            </td>
            <td><span class="badge-pink"><?php echo htmlspecialchars($r['event_type']); ?></span></td>
            <td>
              <?php
                $sc = ['upcoming' => 'badge-upcoming', 'recent' => 'badge-green', 'past' => 'badge-gray'];
                echo '<span class="' . ($sc[$r['status']] ?? 'badge-gray') . '">' . ucfirst($r['status']) . '</span>';
              ?>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="?action=edit&id=<?php echo $r['event_id']; ?>" class="btn-icon" title="Edit">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <a href="?action=delete&id=<?php echo $r['event_id']; ?>" class="btn-icon danger" title="Delete"
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