<?php
// Config.
$secret = 'MY_SECRET';
$domain = 'MY_DOMAIN.COM';
$serverAdmin = 'MY_EMAIL_ADDRESS';
$blacklist = [
    'BLACKLISTED_SUBDOMAIN',
    'BLACKLISTED_SUBDOMAIN',
];

// Variables.
$data = [
    'secret' => null,
    'subdomain' => null,
    'ip' => null
];

// Prepare variables.
$domainStart = $domain;
$domainEnd = '';
$domainExplode = explode('.', $domain);
if (count($domainExplode) > 1) {
    $domainEnd = end($domainExplode);
    unset($domainExplode[(count($domainExplode) - 1)]);
    $domainStart = implode('.', $domainExplode);
}

// Generate directories.
if (is_dir('update')) {
    mkdir('update');
}
if (is_dir('data')) {
    mkdir('data');
}

// Get GET parameters.
if (isset($_GET['secret'])) {
    $data['secret'] = $secret;
}
if (isset($_GET['subdomain'])) {
    $data['subdomain'] = $_GET['subdomain'];
}

// Get IP.
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $data['ip'] = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $data['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $data['ip'] = $_SERVER['REMOTE_ADDR'];
}

// Function redirect 404.
function redirect404() {
    http_response_code(404);
    die();
}

// Function set json file.
function setFile($setSubdomain, $setIp, $setDatetimeUpdate, $setDateCheck) {
    $new = [];
    $new['subdomain'] = $setSubdomain;
    $new['ip'] = $setIp;
    $new['datetimeUpdate'] = $setDatetimeUpdate;
    $new['datetimeCheck'] = $setDateCheck;
    $json = json_encode($new);
    file_put_contents('data/' . $setSubdomain . '.json', $json);
}

// Check secret.
if (!$domain) {
    echo 'Error :: Secret not set.';
    die();
}

// Check domain.
if (!$domain) {
    echo 'Error :: Domain not set.';
    die();
}

// Check server admin.
if (!$serverAdmin) {
    echo 'Error :: Server admin not set.';
    die();
}

// Check secret.
if ($secret !== $data['secret']) {
    redirect404();
}

// Check subdomain.
if (!$data['subdomain']) {
    redirect404();
}
if (in_array($data['subdomain'], $blacklist)) {
    redirect404();
}

// Check IP.
if (!$data['ip']) {
    redirect404();
}

// Check if exists.
if (file_exists('data/' . $data['subdomain'] . '.json')) {
    $json = file_get_contents('data/' . $data['subdomain'] . '.json');
    $file = json_decode($json, true);
    if ($file['ip'] === $data['ip']) {
        setFile($file['subdomain'], $file['ip'], $file['datetimeUpdate'], date('Y-m-d H:i:s'));
        echo 'Message :: Already up to date.';
        die();
    }
}

// Generate new apache config files.
$conf = file_get_contents('files/apache.conf');
$conf = str_replace('[SERVERADMIN]', $serverAdmin, $conf);
$conf = str_replace('[SUBDOMAIN]', $data['subdomain'], $conf);
$conf = str_replace('[DOMAIN]', $domain, $conf);
$leSslConf = file_get_contents('files/apache-le-ssl.conf');
$leSslConf = str_replace('[SERVERADMIN]', $serverAdmin, $leSslConf);
$leSslConf = str_replace('[SUBDOMAIN]', $data['subdomain'], $leSslConf);
$leSslConf = str_replace('[DOMAIN]', $domain, $leSslConf);
$leSslConf = str_replace('[IP]', $data['ip'], $leSslConf);
file_put_contents('update/www.' . $data['subdomain'] . '.' . $domainStart . '.conf', $conf);
file_put_contents('update/www.' . $data['subdomain'] . '.' . $domainStart . '-le-ssl.conf', $leSslConf);
setFile($data['subdomain'], $data['ip'], date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
echo 'Message :: Updated.';
die();
