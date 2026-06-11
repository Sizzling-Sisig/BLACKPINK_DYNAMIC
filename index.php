<?php
require 'includes/conn.php';

$timeline = mysqli_query($conn, "SELECT * FROM timeline ORDER BY sort_order ASC");
$awards   = mysqli_query($conn, "SELECT * FROM awards ORDER BY sort_order ASC");

if (!$timeline) die('Timeline query failed: ' . mysqli_error($conn));
if (!$awards)   die('Awards query failed: '   . mysqli_error($conn));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/style.css">
  <title>BLACKPINK | Main</title>
</head>
<body>

<!--Header-->
  <header class="header">
    <div class="container">
<div class="banner_head">
  <h1 class="blackpink-text">BLA<span>C</span>KPI<span>N</span>K</h1>
</div>      <div class="header_nav">
        <nav>
          <ul class="header_nav_content">
            <li><a href="index.php" class="active">MAIN</a></li>
            <li><a href="public/display_page/discography.php">DISCOGRAPHY</a></li>
            <li><a href="public/display_page/events_pub.php">EVENTS</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>

<!--Main Content-->
  <main class="main_content">
    <div class="container">

      <!--Members Section-->
      <section class="members_section">
        <h2 class="section_title">MEMBERS</h2>
        <div class="members_grid">
          <?php
          $members = mysqli_query($conn, "SELECT * FROM members ORDER BY member_id ASC");
          while ($m = mysqli_fetch_assoc($members)):
          ?>
          <div class="member_card"
               onclick="window.location='public/display_page/member_detail.php?slug=<?php echo $m['slug']; ?>'">
            <div class="member_image"
                 style="background-image: url('<?php echo htmlspecialchars($m['photo_card']); ?>');">
              <div class="member_overlay">
                <span class="member_name_overlay"><?php echo htmlspecialchars($m['stage_name']); ?></span>
              </div>
            </div>
            <div class="member_info">
              <h3><?php echo htmlspecialchars($m['stage_name']); ?></h3>
              <p class="member_real_name"><?php echo htmlspecialchars($m['real_name']); ?></p>
              <p class="member_role"><?php echo htmlspecialchars($m['role']); ?></p>
              <p class="member_detail"><?php echo date('F j, Y', strtotime($m['birthday'])); ?></p>
              <p class="member_detail"><?php echo htmlspecialchars($m['nationality']); ?></p>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
      </section>

      <!--The Group Section-->
      <section class="group_section">
        <h2 class="section_title">THE GROUP</h2>
        <div class="group_content">
          <p>BLACKPINK is a South Korean girl group formed by YG Entertainment, consisting of members Jisoo, Jennie, Rosé, and Lisa. The group debuted in August 2016 with their single album Square One, which featured "Whistle" and "Boombayah".</p>
          <p>Known for their powerful performances, catchy music, and global influence, BLACKPINK has become one of the most successful K-pop groups worldwide, breaking numerous records and achieving unprecedented success.</p>
        </div>
      </section>

      <!--History Section-->
    <section class="history_section">
      <h2 class="section_title">HISTORY</h2>
      <div class="history_timeline">
        <?php while ($row = mysqli_fetch_assoc($timeline)): ?>
        <div class="history_item">
          <h3 class="history_year"><?= htmlspecialchars($row['year_label']) ?></h3>
          <p><?= htmlspecialchars($row['description']) ?></p>
        </div>
        <?php endwhile; ?>
      </div>
    </section>

      <!--Gallery Section-->
      <section class="main_gallery_section">
        <h2 class="section_title">GALLERY</h2>
        <div class="gallery_slider">
          <button class="gallery_nav gallery_prev" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
          </button>
          <div class="gallery_container">
            <?php
            $photos = mysqli_query($conn, "SELECT * FROM gallery ORDER BY sort_order ASC");
            $first  = true;
            while ($p = mysqli_fetch_assoc($photos)):
            ?>
            <div class="gallery_slide <?php echo $first ? 'active' : ''; ?>">
              <img src="assets/<?php echo htmlspecialchars($p['image_path']); ?>"
                  alt="<?php echo htmlspecialchars($p['caption'] ?: 'BLACKPINK Gallery'); ?>">
            </div>
            <?php $first = false; endwhile; ?>
          </div>
          <button class="gallery_nav gallery_next" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
        <div class="gallery_indicators" id="galleryIndicators">
          <?php
          $total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM gallery"))[0];
          for ($i = 0; $i < $total; $i++):
          ?>
          <span class="indicator <?php echo $i === 0 ? 'active' : ''; ?>"
                onclick="currentSlide(<?php echo $i; ?>)"></span>
          <?php endfor; ?>
        </div>
      </section>

      <!--Awards Section-->
      <section class="awards_section">
      <h2 class="section_title">AWARDS & ACHIEVEMENTS</h2>
      <div class="awards_grid">
        <?php while ($row = mysqli_fetch_assoc($awards)): ?>
        <div class="award_item">
          <div class="award_icon"><i class="<?= htmlspecialchars($row['icon_class']) ?>"></i></div>
          <h4><?= htmlspecialchars($row['title']) ?></h4>
          <p><?= htmlspecialchars($row['description']) ?></p>
        </div>
        <?php endwhile; ?>
      </div>
    </section>

    </div>
  </main>

<!--Footer-->
  <footer class="footer">
    <div class="footer_container">
      <p class="footer_title">© 2025 | BLACKPINK Fan Archive</p>
      <p class="footer_disclaimer">Fan-made tribute • Not affiliated with YG Entertainment</p>
    </div>
  </footer>

  <script src="assets/script.js"></script>
</body>
</html>