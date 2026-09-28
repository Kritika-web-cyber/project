<?php

header('Content-Type: text/plain; charset=UTF-8');

echo "REMOTE_ADDR:\n";
echo $_SERVER['REMOTE_ADDR'] ?? 'NOT SET';

echo "\n\nX_FORWARDED_FOR:\n";
echo $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'NOT SET';

echo "\n\nX_REAL_IP:\n";
echo $_SERVER['HTTP_X_REAL_IP'] ?? 'NOT SET';

echo "\n\nCF_CONNECTING_IP:\n";
echo $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'NOT SET';

echo "\n\nUSER_AGENT:\n";
echo $_SERVER['HTTP_USER_AGENT'] ?? 'NOT SET';