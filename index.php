<?php

$destination_url = 'https://dailysource.online/';
$unavailable_page = 'maintenance.html';

$allowed_countries = [
    'PL', // Poland
    'DE', // Germany
    'DK', // Denmark
    'PT', // Portugal
    'HU', // Hungary
    'IT', // Italy
    'AU', // Australia
    'NO'  // Norway
];

$ipinfo_token = '7464e27274d405';


// -------------------------------------
// VISITOR IP
// -------------------------------------

$ip = $_SERVER['REMOTE_ADDR'] ?? '';

$country_code = 'XX';


// -------------------------------------
// BASIC RATE LIMIT
// Same rule applies to every visitor
// -------------------------------------

$rate_limit_dir = __DIR__ . '/rate-limit';

if (!is_dir($rate_limit_dir)) {
    @mkdir($rate_limit_dir, 0755, true);
}

$rate_limited = false;

if ($ip !== '') {

    $key = hash('sha256', $ip);

    $rate_file = $rate_limit_dir . '/' . $key . '.txt';

    $now = time();

    // 60-second window
    $window = 60;

    // Maximum requests in that window
    $max_requests = 20;

    $requests = [];

    if (file_exists($rate_file)) {

        $data = @file($rate_file, FILE_IGNORE_NEW_LINES);

        if ($data !== false) {

            foreach ($data as $timestamp) {

                $timestamp = (int) $timestamp;

                if ($timestamp > ($now - $window)) {
                    $requests[] = $timestamp;
                }
            }
        }
    }

    $requests[] = $now;

    if (count($requests) > $max_requests) {
        $rate_limited = true;
    }

    @file_put_contents(
        $rate_file,
        implode(PHP_EOL, $requests),
        LOCK_EX
    );
}


// -------------------------------------
// COUNTRY LOOKUP
// -------------------------------------

if (
    !$rate_limited &&
    $ip !== '' &&
    filter_var($ip, FILTER_VALIDATE_IP)
) {

    $url =
        'https://ipinfo.io/' .
        rawurlencode($ip) .
        '/country?token=' .
        rawurlencode($ipinfo_token);

    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents(
        $url,
        false,
        $context
    );

    if ($response !== false) {

        $result = strtoupper(trim($response));

        if (preg_match('/^[A-Z]{2}$/', $result)) {
            $country_code = $result;
        }
    }
}


// -------------------------------------
// LOG
// -------------------------------------

$line =
    date('Y-m-d H:i:s') .
    ' | IP: ' . $ip .
    ' | Country: ' . $country_code .
    ' | Rate Limited: ' .
    ($rate_limited ? 'Yes' : 'No') .
    PHP_EOL;

@file_put_contents(
    __DIR__ . '/access_log.txt',
    $line,
    FILE_APPEND | LOCK_EX
);


// -------------------------------------
// ROUTING
// -------------------------------------

// Excessive automated requests
if ($rate_limited) {

    header(
        'Location: ' . $unavailable_page,
        true,
        302
    );

    exit;
}


// Eligible countries
if (
    in_array(
        $country_code,
        $allowed_countries,
        true
    )
) {

    header(
        'Location: ' . $destination_url,
        true,
        302
    );

    exit;
}


// Other countries
header(
    'Location: ' . $unavailable_page,
    true,
    302
);

exit;
?>