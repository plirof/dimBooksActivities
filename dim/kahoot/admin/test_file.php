<?php
echo "PHP Test Page";
if (function_exists('password_hash')) {
    echo "✅ PHP functions loaded";
}
echo "<hr>";
echo "Current PHP Version: " . phpversion();
echo "<hr>";
echo "Loaded Extensions: " . implode(', ', get_loaded_extensions());
echo "<hr>";
echo "Config Path: " . getcwd();
echo "<hr>";
echo "Files in admin/:<br>";
foreach (glob(getcwd() . '/admin/*.php') as $file) {
    echo "- $file<br>";
}
echo "<hr>";
echo "<strong>Test Complete</strong>";
