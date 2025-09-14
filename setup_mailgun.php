<?php
/**
 * Mailgun Setup Script
 * 
 * This script helps you configure your Mailgun API key
 * Run this once to set up your Mailgun configuration
 */

// Check if config file exists
$config_file = __DIR__ . '/config/mailgun_config.php';

if (!file_exists($config_file)) {
    die("Error: Mailgun config file not found. Please make sure config/mailgun_config.php exists.");
}

// Read current config
$config_content = file_get_contents($config_file);

// Check if API key is already set
if (strpos($config_content, 'your-mailgun-api-key-here') !== false) {
    echo "<h2>🔧 Mailgun Setup Required</h2>";
    echo "<p>You need to configure your Mailgun API key before the forgot password system will work.</p>";
    echo "<hr>";
    echo "<h3>📋 Steps to Complete Setup:</h3>";
    echo "<ol>";
    echo "<li><strong>Get your Mailgun API Key:</strong>";
    echo "<ul>";
    echo "<li>Log into your <a href='https://app.mailgun.com/' target='_blank'>Mailgun Dashboard</a></li>";
    echo "<li>Go to <strong>Settings</strong> → <strong>API Keys</strong></li>";
    echo "<li>Copy your <strong>Private API Key</strong></li>";
    echo "</ul></li>";
    echo "<li><strong>Update the configuration file:</strong>";
    echo "<ul>";
    echo "<li>Open <code>config/mailgun_config.php</code></li>";
    echo "<li>Replace <code>your-mailgun-api-key-here</code> with your actual API key</li>";
    echo "<li>Save the file</li>";
    echo "</ul></li>";
    echo "<li><strong>Create database tables:</strong>";
    echo "<ul>";
    echo "<li>Run the SQL in <code>database/password_reset_table.sql</code></li>";
    echo "<li>Or execute the SQL commands in your database</li>";
    echo "</ul></li>";
    echo "<li><strong>Test the system:</strong>";
    echo "<ul>";
    echo "<li>Go to <a href='forgot_pass.php'>forgot_pass.php</a></li>";
    echo "<li>Try requesting a password reset</li>";
    echo "</ul></li>";
    echo "</ol>";
    
    echo "<hr>";
    echo "<h3>🔑 Your Mailgun API Key Location:</h3>";
    echo "<p>In <code>config/mailgun_config.php</code>, line 10:</p>";
    echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
    echo "define('MAILGUN_API_KEY', 'your-mailgun-api-key-here'); // Replace with your actual API key";
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>📊 Database Tables Needed:</h3>";
    echo "<p>Make sure you have these tables in your database:</p>";
    echo "<ul>";
    echo "<li><code>password_reset_tokens</code> - Stores reset tokens</li>";
    echo "<li><code>password_reset_logs</code> - Logs reset attempts</li>";
    echo "</ul>";
    echo "<p>SQL file: <code>database/password_reset_table.sql</code></p>";
    
} else {
    echo "<h2>✅ Mailgun Configuration Complete!</h2>";
    echo "<p>Your Mailgun API key is configured. The forgot password system should be working.</p>";
    echo "<hr>";
    echo "<h3>🧪 Test the System:</h3>";
    echo "<ul>";
    echo "<li><a href='forgot_pass.php' target='_blank'>Test Forgot Password</a></li>";
    echo "<li><a href='login.php' target='_blank'>Go to Login Page</a></li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>📁 Files Created:</h3>";
    echo "<ul>";
    echo "<li>✅ <code>config/mailgun_config.php</code> - Mailgun configuration</li>";
    echo "<li>✅ <code>config/email_templates.php</code> - Email templates</li>";
    echo "<li>✅ <code>database/password_reset_table.sql</code> - Database tables</li>";
    echo "<li>✅ <code>forgot_pass.php</code> - Updated with Mailgun</li>";
    echo "<li>✅ <code>change_pass.php</code> - Updated with secure tokens</li>";
    echo "<li>✅ <code>login.php</code> - Updated with success message</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>🔒 Security Features Added:</h3>";
    echo "<ul>";
    echo "<li>✅ Secure token generation (64-character random tokens)</li>";
    echo "<li>✅ Token expiration (1 hour)</li>";
    echo "<li>✅ Rate limiting (3 attempts per hour)</li>";
    echo "<li>✅ Password hashing with password_hash()</li>";
    echo "<li>✅ Email validation</li>";
    echo "<li>✅ Activity logging</li>";
    echo "<li>✅ Professional email templates</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h3>📞 Need Help?</h3>";
echo "<p>If you encounter any issues:</p>";
echo "<ul>";
echo "<li>Check your Mailgun dashboard for delivery logs</li>";
echo "<li>Verify your DNS records are properly configured</li>";
echo "<li>Check the email logs in <code>logs/email_logs.txt</code></li>";
echo "<li>Make sure your database tables are created</li>";
echo "</ul>";

echo "<hr>";
echo "<p><small>Setup completed on: " . date('Y-m-d H:i:s') . "</small></p>";
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8f9fa;
}
h2, h3 {
    color: #c41e67;
}
code {
    background-color: #e9ecef;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}
pre {
    font-family: 'Courier New', monospace;
    font-size: 14px;
}
a {
    color: #c41e67;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
ul, ol {
    line-height: 1.6;
}
</style>
