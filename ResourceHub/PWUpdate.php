<?php
session_start();
require 'db.php'; // Database connection

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate password
    if (strlen($new_password) < 10 || !preg_match("/[A-Z]/", $new_password) || !preg_match("/[a-z]/", $new_password) || !preg_match("/[0-9]/", $new_password)) {
        $message = "Password must be at least 10 characters long and include uppercase, lowercase letters, and a number.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Check if the code exists in the reset_code table
        $stmt = $conn->prepare("SELECT user_id FROM reset_code WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $resetEntry = $stmt->fetch();

        if ($resetEntry) {
            $userId = $resetEntry['user_id'];

            // Hash the new password
            $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the user's password
            $updateStmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
            $updateStmt->execute(['password' => $hashedPassword, 'id' => $userId]);

            // Delete the reset code
            $deleteStmt = $conn->prepare("DELETE FROM reset_code WHERE user_id = :user_id");
            $deleteStmt->execute(['user_id' => $userId]);

            // Redirect to SignIn.php
            header("Location: SignOut.php");
            exit;
        } else {
            $message = "Invalid reset code.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <style>
        /* body */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #3D648A;
        }

        /* Container for form */
        .login-container {
            background-color: #00274C;
            border-radius: 10px;
            padding: 40px;
            width: 400px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
            text-align: center;
            color: white;
        }

        .login-container h1 {
            font-size: 24px;
            margin-bottom: 30px;
        }

        .login-container label {
            display: block;
            font-size: 16px;
            margin-bottom: 8px;
            text-align: left;
        }

        .login-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        /* Button styling */
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #FFCB05;
            border: none;
            color: #00274C;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .login-container button:hover {
            background-color: #FFB600;
        }

        /* Error message */
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        /* Footer link */
        .footer a {
            color: #FFCB05;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Update Password</h1>
        <?php if (!empty($message)): ?>
            <p class="error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="code">Code:</label>
            <input type="number" name="code" id="code" required>

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <button type="submit">Update</button>
        </form>
        <div class="footer">
            <a href="SignIn.php">Back to Sign In</a>
        </div>
    </div>
</body>
</html>
