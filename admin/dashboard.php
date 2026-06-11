<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BLACKPINK Admin — Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<?php
include("../includes/session.php");
include("../includes/conn.php");

$count_members = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM $tbl_members"))[0];
$count_albums  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM $tbl_albums"))[0];
$count_songs   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM $tbl_songs"))[0];
$count_events  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM $tbl_events"))[0];
$count_gallery = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM $tbl_gallery"))[0];
$upcoming      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM $tbl_events WHERE status='upcoming'"))[0];
?>

<div class="admin-wrapper">
  <?php include("../includes/sidebar.php"); ?>

  <main class="main-content">
    <div class="topbar">
      <div class="page-title">DASH<span>BOARD</span></div>
      <div class="topbar-info">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></div>
    </div>

    <?php if (isset($_SESSION['status'])): ?>
      <div class="alert-bp success"><i class="fa-solid fa-circle-check"></i> <?php echo $_SESSION['status']; unset($_SESSION['status']); ?></div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info">
          <div class="stat-number"><?php echo $count_members; ?></div>
          <div class="stat-label">Members</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-compact-disc"></i></div>
        <div class="stat-info">
          <div class="stat-number"><?php echo $count_albums; ?></div>
          <div class="stat-label">Albums</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-music"></i></div>
        <div class="stat-info">
          <div class="stat-number"><?php echo $count_songs; ?></div>
          <div class="stat-label">Songs</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
        <div class="stat-info">
          <div class="stat-number"><?php echo $count_events; ?></div>
          <div class="stat-label">Events</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-images"></i></div>
        <div class="stat-info">
          <div class="stat-number"><?php echo $count_gallery; ?></div>
          <div class="stat-label">Gallery Photos</div>
        </div>
      </div>
      <div class="stat-card accent">
        <div class="stat-icon"><i class="fa-solid fa-ticket"></i></div>
        <div class="stat-info">
          <div class="stat-number"><?php echo $upcoming; ?></div>
          <div class="stat-label">Upcoming Events</div>
        </div>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="data-card mt-4">
      <div class="data-card-title mb-3">Quick Actions</div>
      <div class="quick-links">
        <a href="members.php?action=add" class="btn-pink"><i class="fa-solid fa-plus"></i> Add Member</a>
        <a href="albums.php?action=add"  class="btn-pink"><i class="fa-solid fa-plus"></i> Add Album</a>
        <a href="songs.php?action=add"   class="btn-pink"><i class="fa-solid fa-plus"></i> Add Song</a>
        <a href="events.php?action=add"  class="btn-pink"><i class="fa-solid fa-plus"></i> Add Event</a>
        <a href="gallery.php?action=add" class="btn-pink"><i class="fa-solid fa-plus"></i> Add Photo</a>
      </div>
    </div>

    <!-- Recent Events -->
    <div class="data-card mt-4">
      <div class="data-card-header">
        <div class="data-card-title">Upcoming Events</div>
        <a href="events.php" class="btn-outline-pink">View All</a>
      </div>
      <table class="table-bp">
        <thead>
          <tr><th>Event</th><th>Date</th><th>Venue</th><th>Location</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php
        $ev = mysqli_query($conn, "SELECT * FROM $tbl_events WHERE status='upcoming' ORDER BY event_date ASC LIMIT 5");
        while ($r = mysqli_fetch_assoc($ev)): ?>
          <tr>
            <td style="color:var(--white);font-weight:600;"><?php echo htmlspecialchars($r['title']); ?></td>            <td><?php echo htmlspecialchars($r['venue']); ?></td>
            <td><?php echo htmlspecialchars($r['location']); ?></td>
            <td><span class="badge-upcoming">Upcoming</span></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>
</body>
</html>