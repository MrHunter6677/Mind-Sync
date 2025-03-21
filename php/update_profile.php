<?php
require_once 'config.php';
require_once 'auth_check.php';

// Ensure user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to update your profile'
    ]);
    exit;
}

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    $userId = $_SESSION['user_id'];
    $email = $_SESSION['user_email']; // Always use the current email from session
    $removePicture = isset($_POST['remove_picture']) && $_POST['remove_picture'] == '1';
    
    // Validate inputs
    if (empty($firstName) || empty($lastName)) {
        $response['message'] = 'First name and last name are required';
    } else {
        try {
            $pdo = getDbConnection();
            
            // Check if email exists and belongs to another user
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $userId]);
            
            if ($stmt->rowCount() > 0) {
                $response['message'] = 'Email address is already in use by another account';
            } else {
                // Get current user data
                $stmt = $pdo->prepare("SELECT profile_pic, profile_pic_type, user_name FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                $profilePicUpdated = false;
                $profilePic = null;
                $profilePicType = null;
                
                // Handle profile picture upload
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                    $file = $_FILES['profile_picture'];
                    
                    // Validate file type
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $file_type = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    
                    if (!in_array($file_type, $allowed_types)) {
                        $response['message'] = 'Invalid file type. Only JPG, PNG, and GIF files are allowed.';
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit;
                    }
                    
                    // Validate file size (2MB max)
                    if ($file['size'] > 2 * 1024 * 1024) {
                        $response['message'] = 'File size exceeds the 2MB limit.';
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit;
                    }
                    
                    // Read file content
                    $profilePic = file_get_contents($file['tmp_name']);
                    $profilePicType = $file_type;
                    $profilePicUpdated = true;
                    
                    // Generate a timestamp parameter to force refresh of the image
                    $timestamp = time();
                    $response['timestamp'] = $timestamp;
                } else if ($removePicture) {
                    // Remove profile picture if requested
                    $profilePic = null;
                    $profilePicType = null;
                    $profilePicUpdated = true;
                    $response['picture_removed'] = true;
                }
                
                // Begin transaction
                $pdo->beginTransaction();
                
                try {
                    // Update user profile
                    if ($profilePicUpdated) {
                        $sql = "UPDATE users SET first_name = ?, last_name = ?, bio = ?, profile_pic = ?, profile_pic_type = ? WHERE user_id = ?";
                        $stmt = $pdo->prepare($sql);
                        
                        // Bind parameters
                        $stmt->bindParam(1, $firstName, PDO::PARAM_STR);
                        $stmt->bindParam(2, $lastName, PDO::PARAM_STR);
                        $stmt->bindParam(3, $bio, PDO::PARAM_STR);
                        
                        if ($profilePic === null) {
                            $stmt->bindParam(4, $profilePic, PDO::PARAM_NULL);
                            $stmt->bindParam(5, $profilePicType, PDO::PARAM_NULL);
                        } else {
                            $stmt->bindParam(4, $profilePic, PDO::PARAM_LOB);
                            $stmt->bindParam(5, $profilePicType, PDO::PARAM_STR);
                        }
                        
                        $stmt->bindParam(6, $userId, PDO::PARAM_INT);
                    } else {
                        $sql = "UPDATE users SET first_name = ?, last_name = ?, bio = ? WHERE user_id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(1, $firstName, PDO::PARAM_STR);
                        $stmt->bindParam(2, $lastName, PDO::PARAM_STR);
                        $stmt->bindParam(3, $bio, PDO::PARAM_STR);
                        $stmt->bindParam(4, $userId, PDO::PARAM_INT);
                    }
                    
                    $stmt->execute();
                    
                    // Commit transaction
                    $pdo->commit();
                    
                    if ($stmt->rowCount() > 0 || $profilePicUpdated) {
                        // Update session data
                        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                        $_SESSION['first_name'] = $firstName;
                        $_SESSION['last_name'] = $lastName;
                        
                        // Return success
                        $response['success'] = true;
                        $response['message'] = 'Profile updated successfully';
                        $response['first_name'] = $firstName;
                        $response['last_name'] = $lastName;
                        $response['full_name'] = $firstName . ' ' . $lastName;
                    } else {
                        // No changes were made
                        $response['success'] = true;
                        $response['message'] = 'No changes were made to your profile';
                    }
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $pdo->rollBack();
                    throw $e;
                }
            }
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $response['message'] = 'An error occurred while updating your profile. Please try again.';
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 