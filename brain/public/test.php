<?php
echo "PHP is working!\n";
echo "Laravel directory exists: " . (is_dir('/var/www/html') ? 'YES' : 'NO') . "\n";
echo "Index.php exists: " . (file_exists('/var/www/html/public/index.php') ? 'YES' : 'NO') . "\n";
echo "Vendor directory exists: " . (is_dir('/var/www/html/vendor') ? 'YES' : 'NO') . "\n";
echo "Bootstrap app.php exists: " . (file_exists('/var/www/html/bootstrap/app.php') ? 'YES' : 'NO') . "\n";
phpinfo();
?>