<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_SESSION['admin_username'])) {
    header("Location: ../admin/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    require_once "../includes/conn.php";

    $username       = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password_input = trim($_POST['password']);

    $result = mysqli_query($conn, "SELECT * FROM $tbl_admins WHERE username='$username'");

    if (mysqli_num_rows($result) === 1) {
        $admin         = mysqli_fetch_assoc($result);
        $authenticated = false;

        if (password_verify($password_input, $admin['password'])) {
            $authenticated = true;
        }

        if ($authenticated) {
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_id']       = $admin['admin_id'];
            $_SESSION['admin_fullname'] = $admin['full_name'];
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BLACKPINK Admin — Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/admin.css">
</head>
<body class="login-page">

  <div class="login-bg"></div>

  <div class="login-box">

    <div class="login-logo">
      <div class="wordmark">BLACKPINK</div>
      <div class="tagline">Admin Portal</div>
    </div>

    <div class="login-divider"></div>
    <div class="login-title">Sign In</div>

    <?php if ($error): ?>
      <div class="alert-bp error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div style="margin-bottom:1rem;">
        <label class="form-label-bp">Username</label>
        <input type="text" class="form-control-bp" name="username" required autocomplete="username">
      </div>
      <div style="margin-bottom:0.5rem;">
        <label class="form-label-bp">Password</label>
        <input type="password" class="form-control-bp" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" name="login" class="btn-login">
        <i class="fa-solid fa-right-to-bracket"></i> LOGIN
      </button>
    </form>

    <div class="login-footer" style="text-align:center; margin-top:1.4rem;">
      <a href="register.php" style="font-size:0.8rem; color:var(--muted); text-decoration:none; letter-spacing:0.05em;">
        Register with Invite Code
      </a>
      <span style="color:var(--muted); margin:0 0.6rem;">|</span>
      <a href="../index.php" style="font-size:0.8rem; color:var(--muted); text-decoration:none; letter-spacing:0.05em;">
        <i class="fa-solid fa-arrow-left"></i> Back to Site
      </a>
    </div>

  </div>

</body>
</html>