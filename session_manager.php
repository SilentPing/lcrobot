<?php
/**
 * Session Manager for Civil Registry System
 * 
 * This file provides centralized session management functions
 * to prevent caching issues and ensure proper authentication.
 */

/**
 * Start session with security settings
 */
function startSecureSession() {
    // Configure session security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Set cache control headers to prevent caching
 */
function setNoCacheHeaders() {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['name']) && !empty($_SESSION['name']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'admin';
}

/**
 * Redirect to appropriate dashboard based on user type
 */
function redirectToDashboard() {
    if (isAdmin()) {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit;
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Require admin access - redirect to user dashboard if not admin
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header("Location: user_dashboard.php");
        exit;
    }
}

/**
 * Clear session and redirect to login
 */
function logout() {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Clear cache and redirect
    setNoCacheHeaders();
    header("Location: login.php");
    exit;
}

/**
 * Prevent back button issues with JavaScript
 */
function getBackButtonPreventionJS() {
    return "
    <script>
        // Prevent back button issues and clear cache
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        // Clear form data when page loads
        window.addEventListener('load', function() {
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });

        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>";
}
?>
