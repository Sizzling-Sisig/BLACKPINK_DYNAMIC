<?php
require_once "../includes/auth_check.php";
require_once "../includes/conn.php";

$success = '';
$error   = '';

// ── Generate a new invite code ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $new_code = 'BLINK-' . strtoupper(bin2hex(random_bytes(4)));
    $esc      = mysqli_real_escape_string($conn, $new_code);
    $res      = mysqli_query($conn, "INSERT INTO $tbl_invite_codes (code) VALUES ('$esc')");
    $success  = $res ? "Code generated: <strong>$new_code</strong>" : "Failed to generate code.";
}

// ── Delete a code ─────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM $tbl_invite_codes WHERE code_id=$del_id AND is_used=0");
    header("Location: invite_codes.php?deleted=1");
    exit();
}

if (isset($_GET['deleted'])) $success = "Unused code deleted.";

// ── Fetch all codes ───────────────────────────────────────────
$codes = mysqli_query($conn, "SELECT * FROM $tbl_invite_codes ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invite Codes — BLACKPINK Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<div class="admin-wrapper">
  <?php include "../includes/sidebar.php"; ?>

  <main class="main-content">

    <div class="topbar">
      <div class="page-title">Invite <span>Codes</span></div>
      <div class="topbar-info">
        Logged in as <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="alert-bp success mb-3">
        <i class="fa-solid fa-circle-check"></i>
        <?php echo $success; ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert-bp error mb-3">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <!-- Generate form -->
    <div class="data-card mb-4">
      <div class="data-card-header">
        <div class="data-card-title"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate New Code</div>
      </div>
      <form method="POST" action="">
        <p style="font-size:0.83rem; color:var(--muted); margin-bottom:1rem;">
          Generate a single-use invite code to share with a new admin registrant.
        </p>
        <button type="submit" name="generate" class="btn-pink">
          <i class="fa-solid fa-plus"></i> Generate Code
        </button>
      </form>
    </div>

    <!-- Codes table -->
    <div class="data-card">
      <div class="data-card-header">
        <div class="data-card-title"><i class="fa-solid fa-key"></i> All Invite Codes</div>
        <span style="font-size:0.78rem; color:var(--muted);">
          <?php echo mysqli_num_rows($codes); ?> total
        </span>
      </div>

      <table class="table-bp">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Status</th>
            <th>Used By</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          mysqli_data_seek($codes, 0);
          while ($row = mysqli_fetch_assoc($codes)):
            $used = (int)$row['is_used'];
          ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td>
              <code style="
                font-family: monospace;
                font-size: 0.85rem;
                color: var(--hot-pink);
                background: var(--pink-dim);
                padding: 2px 8px;
                border-radius: 4px;
                letter-spacing: 0.08em;
              ">
                <?php echo htmlspecialchars($row['code']); ?>
              </code>
            </td>
            <td>
              <?php if ($used): ?>
                <span class="badge-gray">Used</span>
              <?php else: ?>
                <span class="badge-green">Available</span>
              <?php endif; ?>
            </td>
            <td style="color:var(--muted); font-size:0.82rem;">
              <?php echo $row['used_by'] ? htmlspecialchars($row['used_by']) : '—'; ?>
            </td>
            <td style="color:var(--muted); font-size:0.8rem;">
              <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
            </td>
            <td>
              <?php if (!$used): ?>
                <a href="invite_codes.php?delete=<?php echo $row['code_id']; ?>"
                   class="btn-icon danger"
                   title="Delete"
                   onclick="return confirm('Delete this unused code?')">
                  <i class="fa-solid fa-trash"></i>
                </a>
              <?php else: ?>
                <span style="color:var(--muted); font-size:0.75rem;">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if (mysqli_num_rows($codes) === 0): ?>
          <tr>
            <td colspan="6" style="text-align:center; color:var(--muted); padding:2rem;">
              No invite codes yet. Generate one above.
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>
</body>
</html>