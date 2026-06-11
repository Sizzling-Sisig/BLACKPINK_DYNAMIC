<?php
$server   = "localhost";
$username = "root";
$password = "";
$dbname = "db_blackpink";

// Table name constants
$tbl_admins         = "admins";
$tbl_members        = "members";
$tbl_member_gallery = "member_gallery";
$tbl_member_videos  = "member_videos";
$tbl_albums         = "albums";
$tbl_songs          = "songs";
$tbl_events         = "events";
$tbl_gallery        = "gallery";
$tbl_invite_codes   = "invite_codes";  // ← add this one, it's missing

$conn = mysqli_connect($server, $username, $password, $dbname)
    or die("Cannot connect to database.");

mysqli_set_charset($conn, "utf8mb4");
?>