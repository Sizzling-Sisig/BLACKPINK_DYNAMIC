<?php
include("../../includes/conn.php");

$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, trim($_GET['slug'])) : '';

if (empty($slug)) {
    header("Location: ../../index.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM members WHERE slug='$slug'");
if (mysqli_num_rows($result) === 0) {
    header("Location: ../../index.php");
    exit();
}
$m = mysqli_fetch_assoc($result);

$gallery = mysqli_query($conn, "SELECT * FROM member_gallery WHERE member_id={$m['member_id']} ORDER BY sort_order ASC");
$videos  = mysqli_query($conn, "SELECT * FROM member_videos WHERE member_id={$m['member_id']} ORDER BY sort_order ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../../assets/style.css">
  <title>BLACKPINK | <?php echo htmlspecialchars($m['stage_name']); ?></title>
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
            <li><a href="../../index.php">MAIN</a></li>
            <li><a href="discography.php">DISCOGRAPHY</a></li>
            <li><a href="events_pub.php">EVENTS</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <main class="main_content">
    <div class="container">

      <section class="member_detail_page">
        <div class="member_detail_header">
          <div class="member_detail_image_large"
               style="background-image: url('../../<?php echo htmlspecialchars($m['main_image']); ?>');">
          </div>
          <div class="member_basic_info">
            <h2 class="member_name_title"><?php echo htmlspecialchars($m['stage_name']); ?></h2>
            <?php
            $sc = [
              'Active Group'     => '#00c864',
              'Active Solo'      => '#4da6ff',
              'Hiatus'           => '#888888',
              'Military Service' => '#f5c842',
            ];
            $color = $sc[$m['status']] ?? '#888';
            ?>
            <p>
              <span style="display:inline-block;padding:3px 10px;border-radius:3px;
                background:<?php echo $color; ?>22;color:<?php echo $color; ?>;
                border:1px solid <?php echo $color; ?>55;font-size:0.8rem;font-weight:700;">
                <?php echo htmlspecialchars($m['status']); ?>
              </span>
            </p>
            <p><strong>Real Name:</strong> <?php echo htmlspecialchars($m['real_name']); ?></p>
            <p><strong>Position:</strong> <?php echo htmlspecialchars($m['role']); ?></p>
            <p><strong>Birthday:</strong> <?php echo date('F j, Y', strtotime($m['birthday'])); ?></p>
            <p><strong>Nationality:</strong> <?php echo htmlspecialchars($m['nationality']); ?></p>
            <p><strong>Height:</strong> <?php echo htmlspecialchars($m['height']); ?></p>
          </div>
        </div>

        <!--About-->
        <div class="member_about_section">
          <h3 class="subsection_title">ABOUT</h3>
          <p class="about_text"><?php echo nl2br(htmlspecialchars($m['about'])); ?></p>
        </div>

        <!--Gallery-->
        <?php if (mysqli_num_rows($gallery) > 0): ?>
        <div class="member_gallery_section">
          <h3 class="subsection_title">GALLERY</h3>
          <div class="gallery_grid">
            <?php while ($g = mysqli_fetch_assoc($gallery)): ?>
            <div class="gallery_item"
                 style="background-image: url('../../<?php echo htmlspecialchars($g['image_path']); ?>');">
            </div>
            <?php endwhile; ?>
          </div>
        </div>
        <?php endif; ?>

        <!--Music Videos-->
        <?php if (mysqli_num_rows($videos) > 0): ?>
        <div class="member_clips_section">
          <h3 class="subsection_title">MUSIC VIDEOS</h3>
          <div class="clips_grid_large">
            <?php while ($v = mysqli_fetch_assoc($videos)): ?>
            <a href="<?php echo htmlspecialchars($v['youtube_url']); ?>"
               class="clip_item_link" target="_blank">
              <div class="clip_item">
                <div class="video_thumbnail"
                     style="background-image: url('../../<?php echo htmlspecialchars($v['thumbnail']); ?>');">
                  <div class="play_button_overlay">
                    <i class="fab fa-youtube"></i>
                  </div>
                </div>
                <p class="clip_title"><?php echo htmlspecialchars($v['title']); ?></p>
              </div>
            </a>
            <?php endwhile; ?>
          </div>
        </div>
        <?php endif; ?>

        <!--Back Button-->
        <div class="back_button_container">
          <a href="../../index.php" class="back_button">
            <i class="fas fa-arrow-left"></i> Back to Members
          </a>
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

</body>
</html>