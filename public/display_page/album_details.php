<?php
include("../../includes/conn.php");

$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, trim($_GET['slug'])) : '';

if (empty($slug)) {
    header("Location: discography.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM albums WHERE slug='$slug'");
if (mysqli_num_rows($result) === 0) {
    header("Location: discography.php");
    exit();
}
$a = mysqli_fetch_assoc($result);

$tracks       = mysqli_query($conn, "SELECT * FROM songs WHERE album_id={$a['album_id']} ORDER BY track_number ASC");
$total_tracks = mysqli_num_rows($tracks);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../../assets/style.css">
  <title>BLACKPINK | <?php echo htmlspecialchars($a['title']); ?></title>
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
            <li><a href="discography.php" class="active">DISCOGRAPHY</a></li>
            <li><a href="events_pub.php">EVENTS</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <main class="main_content">
    <div class="container">

      <section class="album_detail_page">
        <div class="album_detail_container">

          <div class="album_detail_left">
            <div class="album_detail_cover"
                 style="background-image: url('../../<?php echo htmlspecialchars($a['cover_image']); ?>');">
            </div>
          </div>

          <div class="album_detail_right">
            <h2 class="album_detail_title"><?php echo htmlspecialchars($a['title']); ?></h2>

            <div class="album_info_section">
              <h4 class="info_subtitle">INFO</h4>
              <p><strong>Release Date:</strong> <?php echo date('F j, Y', strtotime($a['release_date'])); ?></p>
              <p><strong>Type:</strong> <?php echo htmlspecialchars($a['album_type']); ?></p>
              <p><strong>Label:</strong> <?php echo htmlspecialchars($a['label']); ?></p>
              <p><strong>Genre:</strong> <?php echo htmlspecialchars($a['genre']); ?></p>
              <p><strong>Language:</strong> <?php echo htmlspecialchars($a['language']); ?></p>
              <p><strong>Total Tracks:</strong> <?php echo $total_tracks; ?></p>
              <?php if (!empty($a['description'])): ?>
              <p class="album_description"><?php echo htmlspecialchars($a['description']); ?></p>
              <?php endif; ?>
            </div>

            <!--Tracklist-->
            <?php if ($total_tracks > 0): ?>
            <div class="tracklist_section">
              <h4 class="info_subtitle">TRACKLIST</h4>
              <ol class="tracklist">
                <?php
                mysqli_data_seek($tracks, 0);
                while ($t = mysqli_fetch_assoc($tracks)):
                ?>
                <li>
                  <span class="track_title"><?php echo htmlspecialchars($t['title']); ?></span>
                  <?php if (!empty($t['duration'])): ?>
                  <span class="track_duration"><?php echo htmlspecialchars($t['duration']); ?></span>
                  <?php endif; ?>
                </li>
                <?php endwhile; ?>
              </ol>
            </div>
            <?php endif; ?>

            <!--Listen Now-->
            <div class="listening_platforms">
              <h4 class="info_subtitle">LISTEN NOW</h4>
              <div class="platform_buttons">
                <?php if (!empty($a['spotify_url'])): ?>
                <a href="<?php echo htmlspecialchars($a['spotify_url']); ?>"
                   target="_blank" class="platform_btn">
                  <i class="fab fa-spotify"></i> Spotify
                </a>
                <?php endif; ?>
                <?php if (!empty($a['youtube_url'])): ?>
                <a href="<?php echo htmlspecialchars($a['youtube_url']); ?>"
                   target="_blank" class="platform_btn">
                  <i class="fab fa-youtube"></i> YouTube Music
                </a>
                <?php endif; ?>
              </div>
            </div>

          </div>
        </div>

        <!--Back Button-->
        <div class="back_button_container">
          <a href="discography.php" class="back_button">
            <i class="fas fa-arrow-left"></i> Back to Albums
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