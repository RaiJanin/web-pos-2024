<?php
    session_start();
    session_destroy();
    
    // Prevent back button access after logout
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Location: ../index.html");
    exit();
?>