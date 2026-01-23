<?php
// Test PDO MySQL driver availability
echo "<h2>PHP PDO MySQL Driver Test</h2>";

echo "<h3>Loaded Extensions:</h3>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";

echo "<h3>PDO Available Drivers:</h3>";
echo "<pre>";
if (extension_loaded('pdo')) {
    echo "PDO extension: LOADED\n";
    print_r(PDO::getAvailableDrivers());
} else {
    echo "PDO extension: NOT LOADED\n";
}
echo "</pre>";

echo "<h3>PDO MySQL Extension:</h3>";
echo "<pre>";
if (extension_loaded('pdo_mysql')) {
    echo "pdo_mysql extension: LOADED\n";
} else {
    echo "pdo_mysql extension: NOT LOADED\n";
}
echo "</pre>";

echo "<h3>PHP Configuration:</h3>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP INI File: " . php_ini_loaded_file() . "\n";
echo "Extension Dir: " . ini_get('extension_dir') . "\n";
echo "</pre>";




