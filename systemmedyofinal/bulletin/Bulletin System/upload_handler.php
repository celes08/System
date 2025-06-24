<?php
include("user_session.php");
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // File properties
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    // Get file extension
    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));


    // Allowed extensions
    $allowed = ['jpg', 'jpeg', 'png', 'gif','jfif','webp','heic','heif'];

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            // Max file size: 5MB
            if ($fileSize < 5000000) {
                // Create a unique file name
                $fileNameNew = "profile_" . $currentUser['user_id'] . "." . $fileActualExt;
                
                // Set file destination
                $fileDestination = 'uploads/' . $fileNameNew;

                // Create uploads directory if it doesn't exist
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                // Move the file
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Update database
                    $stmt = $con->prepare("UPDATE signuptbl SET profile_picture = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $fileDestination, $currentUser['user_id']);
                    $stmt->execute();
                    $stmt->close();

                    // Update session variable
                    $_SESSION['currentUser']['profile_picture'] = $fileDestination;

                    // Redirect back to profile page
                    header("Location: profile.php?uploadsuccess");
                    exit();
                } else {
                    header("Location: profile.php?error=uploadfailed");
                    exit();
                }
            } else {
                header("Location: profile.php?error=filetoobig");
                exit();
            }
        } else {
            header("Location: profile.php?error=uploaderror");
            exit();
        }
    } else {
        header("Location: profile.php?error=invalidfiletype");
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}
?> 