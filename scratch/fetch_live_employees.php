<?php
// Start session and login
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/webapp/public/auth/processLogin');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);

// Now request /payroll/employees
curl_setopt($ch, CURLOPT_URL, 'http://localhost/webapp/public/payroll/employees');
curl_setopt($ch, CURLOPT_POST, false);
$employeesHtml = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "HTML Length: " . strlen($employeesHtml) . "\n";
file_put_contents(__DIR__ . '/rendered_employees.html', $employeesHtml);
echo "Saved rendered HTML to scratch/rendered_employees.html\n";
