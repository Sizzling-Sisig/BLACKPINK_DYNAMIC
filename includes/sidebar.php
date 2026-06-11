<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <span class="wordmark">BLACKPINK</span>
    <span class="sub">Admin Panel</span>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Main</div>
    <a href="dashboard.php" class="<?php echo $current === 'dashboard.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-gauge-high"></i> Dashboard
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section-label">Manage</div>

    <a href="members.php" class="<?php echo $current === 'members.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-users"></i> Members
    </a>
    <a href="albums.php" class="<?php echo $current === 'albums.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-compact-disc"></i> Albums
    </a>
    <a href="songs.php" class="<?php echo $current === 'songs.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-music"></i> Songs
    </a>
    <a href="events.php" class="<?php echo $current === 'events.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-calendar-days"></i> Events
    </a>
    <a href="gallery.php" class="<?php echo $current === 'gallery.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-images"></i> Gallery
    </a>
    <a href="timeline.php" <?= basename($_SERVER['PHP_SELF']) === 'timeline.php' ? 'class="active"' : '' ?>>
      <i class="fas fa-clock"></i> Timeline
    </a>
    <a href="awards.php" <?= basename($_SERVER['PHP_SELF']) === 'awards.php' ? 'class="active"' : '' ?>>
      <i class="fas fa-trophy"></i> Awards
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section-label">Access</div>
    <a href="invite_codes.php" class="<?php echo $current === 'invite_codes.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-key"></i> Invite Codes
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section-label">Site</div>
    <a href="../index.php" target="_blank">
      <i class="fa-solid fa-arrow-up-right-from-square"></i> View Site
    </a>
  </nav>

  <div class="sidebar-logout">
    <a href="../account/logout.php" class="btn-logout">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
    <div class="sidebar-user">
      <i class="fa-solid fa-user-shield"></i>
      <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
    </div>
  </div>
</aside>