<?php echo "\n[ERROR] " . get_class($exception) . "\n";
echo $message . "\n";
echo "File: " . $exception->getFile() . "\n";
echo "Line: " . $exception->getLine() . "\n";
