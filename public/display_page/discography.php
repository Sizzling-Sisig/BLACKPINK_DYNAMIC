<?php include("../../includes/conn.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../../assets/style.css">
  <title>BLACKPINK | Discography</title>
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

      <!--Search Bar-->
      <div class="search_bar_wrap">
        <form method="GET" action="">
          <input type="text" name="q" class="search_input_pub"
                 placeholder="Search albums or songs..."
                 value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
          <button type="submit" class="search_btn_pub">
            <i class="fas fa-search"></i> SEARCH
          </button>
        </form>
      </div>

      <?php
      // ── SEARCH MODE ───────────────────────────────────────
      if (!empty($_GET['q'])) :
        $q = mysqli_real_escape_string($conn, trim($_GET['q']));
      ?>

      <section class="albums_section">
        <h2 class="section_title">SEARCH RESULTS FOR "<?php echo htmlspecialchars(strtoupper($q)); ?>"</h2>
        <div class="albums_grid">
          <?php
          $res = mysqli_query($conn, "SELECT * FROM albums
                WHERE title LIKE '%$q%' OR album_type LIKE '%$q%' OR genre LIKE '%$q%'
                ORDER BY release_date DESC");
          if (mysqli_num_rows($res) === 0): ?>
            <p style="color:#888;">No albums found.</p>
          <?php else: while ($a = mysqli_fetch_assoc($res)): ?>
          <div class="album_card"
               onclick="window.location='album_details.php?slug=<?php echo $a['slug']; ?>'">
            <div class="album_cover"
                 style="background-image: url('../../<?php echo htmlspecialchars($a['cover_image']); ?>');">
            </div>
            <div class="album_info">
              <h3><?php echo htmlspecialchars($a['title']); ?></h3>
              <p class="album_date"><?php echo date('F j, Y', strtotime($a['release_date'])); ?></p>
              <p class="album_type"><?php echo htmlspecialchars($a['album_type']); ?></p>
            </div>
          </div>
          <?php endwhile; endif; ?>
        </div>
      </section>

      <?php
      // Also search songs
      $songs_res = mysqli_query($conn, "SELECT s.*, a.title AS album_title, a.slug AS album_slug
            FROM songs s JOIN albums a ON s.album_id = a.album_id
            WHERE s.title LIKE '%$q%'
            ORDER BY a.release_date DESC, s.track_number ASC");
      if (mysqli_num_rows($songs_res) > 0): ?>
      <section class="albums_section">
        <h2 class="section_title">MATCHING SONGS</h2>
        <table class="search_songs_table">
          <thead>
            <tr>
              <th>#</th>
              <th>Song Title</th>
              <th>Album</th>
              <th>Duration</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($s = mysqli_fetch_assoc($songs_res)): ?>
            <tr onclick="window.location='album_details.php?slug=<?php echo $s['album_slug']; ?>'"
                style="cursor:pointer;">
              <td><?php echo $s['track_number']; ?></td>
              <td><?php echo htmlspecialchars($s['title']); ?></td>
              <td><?php echo htmlspecialchars($s['album_title']); ?></td>
              <td><?php echo htmlspecialchars($s['duration'] ?: '—'); ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </section>
      <?php endif; ?>

      <?php
      // ── BROWSE MODE ───────────────────────────────────────
      else:
        $types = [
          'Studio Albums'     => 'Studio Album',
          'Extended Plays'    => 'Mini Album',
          'Single Albums'     => ['Single Album', 'Debut Single', 'Digital Single'],
          'Japanese Albums'   => 'Japanese Album',
          'Live Albums'       => 'Live Album',
        ];

        foreach ($types as $label => $type_val):
          if (is_array($type_val)) {
            $in_list = "'" . implode("','", $type_val) . "'";
            $albums  = mysqli_query($conn, "SELECT * FROM albums WHERE album_type IN ($in_list) ORDER BY release_date ASC");
          } else {
            $t      = mysqli_real_escape_string($conn, $type_val);
            $albums = mysqli_query($conn, "SELECT * FROM albums WHERE album_type='$t' ORDER BY release_date ASC");
          }
          if (mysqli_num_rows($albums) === 0) continue;
      ?>
      <section class="albums_section">
        <h2 class="section_title"><?php echo strtoupper($label); ?></h2>
        <div class="albums_grid">
          <?php while ($a = mysqli_fetch_assoc($albums)): ?>
          <div class="album_card"
               onclick="window.location='album_details.php?slug=<?php echo $a['slug']; ?>'">
            <div class="album_cover"
                 style="background-image: url('../../<?php echo htmlspecialchars($a['cover_image']); ?>');">
            </div>
            <div class="album_info">
              <h3><?php echo htmlspecialchars($a['title']); ?></h3>
              <p class="album_date"><?php echo date('F j, Y', strtotime($a['release_date'])); ?></p>
              <p class="album_type"><?php echo htmlspecialchars($a['album_type']); ?></p>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
      </section>
      <?php endforeach; endif; ?>

    </div>
  </main>

<!--Footer-->
  <footer class="footer">
    <div class="footer_container">
      <p class="footer_title">© 2025 | BLACKPINK Fan Archive</p>
      <p class="footer_disclaimer">Fan-made tribute • Not affiliated with YG Entertainment</p>
    </div>
  </footer>

  <script src="../../assets/script.js"></script>
</body>
</html>