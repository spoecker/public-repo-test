<?php
// Path to the unhealthy flag file
$unhealthyFile = '/tmp/unhealthy';

// Check if the file exists
if (file_exists($unhealthyFile)) {
    // File exists, return 500 Internal Server Error
    http_response_code(500);
    echo "Unhealthy";
} else {
    // File does not exist, return 200 OK
    http_response_code(200);
    echo "Healthy";
}