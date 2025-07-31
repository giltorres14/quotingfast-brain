<?php
echo "<h1>QuotingFast Laravel Brain - Coming Soon!</h1>";
echo "<p>Status: Service is running</p>";
echo "<p>Database: " . (getenv("DATABASE_URL") ? "Connected" : "Not configured") . "</p>";
echo "<p>Environment: " . (getenv("APP_ENV") ?: "Not set") . "</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
?>