<?php include("../../includes/conn.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../../assets/style.css">
  <title>BLACKPINK | Events</title>
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
            <li><a href="events_pub.php" class="active">EVENTS</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <main class="main_content">
    <div class="container">

      <!--Search-->
      <div class="search_bar_wrap">
        <form method="GET" action="">
          <input type="text" name="q" class="search_input_pub"
                 placeholder="Search events, venues, locations..."
                 value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
          <button type="submit" class="search_btn_pub">
            <i class="fas fa-search"></i> SEARCH
          </button>
        </form>
      </div>

      <?php
      $search = '';
      $where  = '';
      if (!empty($_GET['q'])) {
          $search = mysqli_real_escape_string($conn, trim($_GET['q']));
          $where  = "AND (title LIKE '%$search%' OR venue LIKE '%$search%' OR location LIKE '%$search%')";
      }
      ?>

      <!--Upcoming / Schedule-->
      <section class="schedule_section">
        <h2 class="section_title">SCHEDULE</h2>
        <div class="schedule_container">
          <?php
          $upcoming = mysqli_query($conn, "SELECT * FROM events WHERE status='upcoming' $where ORDER BY event_date ASC");
          if (mysqli_num_rows($upcoming) === 0): ?>
            <p style="color:#888;">No upcoming events found.</p>
          <?php else: while ($e = mysqli_fetch_assoc($upcoming)):
            $day_start  = date('D', strtotime($e['event_date']));
            $day_end    = $e['event_date_end'] ? date('D', strtotime($e['event_date_end'])) : '';
            $day_label  = strtoupper($day_end ? $day_start . '-' . $day_end : $day_start);
            $date_fmt     = strtoupper(date('M.d', strtotime($e['event_date'])));
            $date_end_fmt = $e['event_date_end']
              ? '-' . date('d', strtotime($e['event_date_end'])) . '.' . date('y', strtotime($e['event_date_end']))
              : '.' . date('y', strtotime($e['event_date']));
          ?>
          <div class="schedule_item">
            <div class="schedule_day">
              <p class="day_name"><?php echo $day_label; ?></p>
              <p class="schedule_date"><?php echo $date_fmt . $date_end_fmt; ?></p>
            </div>
            <div class="schedule_thumbnail">
              <?php if (!empty($e['thumbnail'])): ?>
              <img src="../../<?php echo htmlspecialchars($e['thumbnail']); ?>"
                   alt="<?php echo htmlspecialchars($e['venue']); ?>">
              <?php endif; ?>
            </div>
            <div class="schedule_info">
              <p class="schedule_location">
                <?php echo htmlspecialchars($e['venue'] . ', ' . $e['location']); ?>
              </p>
              <?php if (!empty($e['description'])): ?>
              <p style="font-size:1.2rem;color:#888;margin-top:4px;">
                <?php echo htmlspecialchars($e['description']); ?>
              </p>
              <?php endif; ?>
            </div>
            <div class="schedule_status">
              <?php if (!empty($e['ticket_url'])): ?>
              <a href="<?php echo htmlspecialchars($e['ticket_url']); ?>"
                 target="_blank" class="status_badge status_available">GET TICKETS</a>
              <?php else: ?>
              <span class="status_badge status_unavailable">COMING SOON</span>
              <?php endif; ?>
            </div>
          </div>
          <?php endwhile; endif; ?>
        </div>
      </section>

      <!--Recents-->
      <section class="recents_section">
        <h2 class="section_title">RECENTS</h2>
        <div class="concerts_timeline">
          <?php
          $recents = mysqli_query($conn, "SELECT * FROM events WHERE status='recent' $where ORDER BY event_date DESC");
          if (mysqli_num_rows($recents) === 0): ?>
            <p style="color:#888;">No recent events found.</p>
          <?php else: while ($e = mysqli_fetch_assoc($recents)):
            $date_str = date('F j, Y', strtotime($e['event_date']));
            if ($e['event_date_end'])
              $date_str .= ' — ' . date('F j, Y', strtotime($e['event_date_end']));
          ?>
          <div class="concert_item">
            <?php if (!empty($e['thumbnail'])): ?>
            <div class="concert_image">
              <img src="../../<?php echo htmlspecialchars($e['thumbnail']); ?>"
                   alt="<?php echo htmlspecialchars($e['title']); ?>">
            </div>
            <?php endif; ?>
            <div class="concert_info">
              <h3><?php echo htmlspecialchars(strtoupper($e['title'])); ?></h3>
              <p class="concert_date"><?php echo $date_str; ?></p>
              <p class="concert_location">
                <?php echo htmlspecialchars($e['venue'] . ', ' . $e['location']); ?>
              </p>
              <?php if (!empty($e['description'])): ?>
              <p class="concert_description">
                <?php echo htmlspecialchars($e['description']); ?>
              </p>
              <?php endif; ?>
            </div>
          </div>
          <?php endwhile; endif; ?>
        </div>
      </section>

      <!--Notice-->
      <section class="notice_section">
        <div class="notice_box">
          <h2>NOTICE</h2>
          <p>Stay updated with BLACKPINK's latest activities and announcements! Follow their official social media channels for real-time updates on upcoming events, concerts, and releases.</p>
          <p>Official announcements regarding tour dates, album releases, and special events will be posted on YG Entertainment's website and BLACKPINK's official accounts.</p>
          <p>For ticketing information and tour schedules, please visit the official BLACKPINK website or authorized ticket vendors in your region.</p>
          <a href="https://www.ygfamily.com/en/artists/blackpink/concert"
             target="_blank" class="learn_more_button">LEARN MORE</a>
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