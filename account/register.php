<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_SESSION['admin_username'])) {
    header("Location: ../admin/dashboard.php");
    exit();
}

require_once "../includes/conn.php";

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    $full_name   = trim($_POST['full_name']);
    $username    = trim($_POST['username']);
    $password    = trim($_POST['password']);
    $confirm     = trim($_POST['confirm_password']);
    $invite_code = strtoupper(trim($_POST['invite_code']));

    // ── Basic validation ──────────────────────────────────────
    if (empty($full_name) || empty($username) || empty($password) || empty($invite_code)) {
        $error = "All fields are required.";

    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $error = "Username must be between 3 and 30 characters.";

    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username may only contain letters, numbers, and underscores.";

    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";

    } else {
        // ── Check invite code ─────────────────────────────────
        $esc_code = mysqli_real_escape_string($conn, $invite_code);
        $code_res = mysqli_query($conn, "SELECT * FROM $tbl_invite_codes WHERE code='$esc_code' AND is_used=0");

        if (mysqli_num_rows($code_res) !== 1) {
            $error = "Invalid or already-used invite code.";

        } else {
            $code_row = mysqli_fetch_assoc($code_res);

            // ── Check username not taken ──────────────────────
            $esc_user = mysqli_real_escape_string($conn, $username);
            $user_res = mysqli_query($conn, "SELECT admin_id FROM $tbl_admins WHERE username='$esc_user'");

            if (mysqli_num_rows($user_res) > 0) {
                $error = "That username is already taken.";

            } else {
                // ── Create account ────────────────────────────
                $hashed   = password_hash($password, PASSWORD_DEFAULT);
                $esc_name = mysqli_real_escape_string($conn, $full_name);

                $insert = mysqli_query($conn,
                    "INSERT INTO $tbl_admins (full_name, username, password)
                     VALUES ('$esc_name', '$esc_user', '$hashed')"
                );

                if ($insert) {
                    // Mark invite code as used
                    $code_id = (int)$code_row['code_id'];
                    mysqli_query($conn,
                        "UPDATE $tbl_invite_codes SET is_used=1, used_by='$esc_user' WHERE code_id=$code_id"
                    );
                    $success = "Account created! You can now log in.";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BLACKPINK Admin — Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/admin.css">
</head>
<body class="login-page">

  <div class="login-bg"></div>

  <div class="login-box" style="max-width:420px;">

    <div class="login-logo">
      <div class="wordmark">BLACKPINK</div>
      <div class="tagline">Admin Portal</div>
    </div>

    <div class="login-divider"></div>
    <div class="login-title">Create Account</div>

    <?php if ($error): ?>
      <div class="alert-bp error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert-bp success">
        <i class="fa-solid fa-circle-check"></i>
        <?php echo htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" action="">

      <div style="margin-bottom:1rem;">
        <label class="form-label-bp">Full Name</label>
        <input
          type="text"
          class="form-control-bp"
          name="full_name"
          placeholder="e.g. Juan dela Cruz"
          value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
          required
        >
      </div>

      <div style="margin-bottom:1rem;">
        <label class="form-label-bp">Username</label>
        <input
          type="text"
          class="form-control-bp"
          name="username"
          placeholder="Letters, numbers, underscores only"
          value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
          required
        >
      </div>

      <div style="margin-bottom:1rem;">
        <label class="form-label-bp">Password</label>
        <input
          type="password"
          class="form-control-bp"
          name="password"
          placeholder="At least 6 characters"
          required
        >
      </div>

      <div style="margin-bottom:1rem;">
        <label class="form-label-bp">Confirm Password</label>
        <input
          type="password"
          class="form-control-bp"
          name="confirm_password"
          placeholder="Repeat your password"
          required
        >
      </div>

      <div style="margin-bottom:0.5rem;">
        <label class="form-label-bp">
          <i class="fa-solid fa-key" style="color:var(--hot-pink); margin-right:4px;"></i>
          Invite Code
        </label>
        <input
          type="text"
          class="form-control-bp"
          name="invite_code"
          placeholder="e.g. BLINK-2025-ADMIN"
          style="text-transform:uppercase; letter-spacing:0.1em;"
          value="<?php echo isset($_POST['invite_code']) ? htmlspecialchars(strtoupper($_POST['invite_code'])) : ''; ?>"
          required
        >
        <div style="font-size:0.72rem; color:var(--muted); margin-top:4px;">
          Invite codes are issued by an existing admin.
        </div>
      </div>

      <button type="submit" name="register" class="btn-login" style="margin-top:1.2rem;">
        <i class="fa-solid fa-user-plus"></i> CREATE ACCOUNT
      </button>

    </form>
    <?php endif; ?>

    <div style="text-align:center; margin-top:1.4rem;">
      <a href="login.php" style="font-size:0.8rem; color:var(--muted); text-decoration:none; letter-spacing:0.05em;">
        <i class="fa-solid fa-arrow-left"></i> Back to Login
      </a>
    </div>

  </div>

</body>
</html>