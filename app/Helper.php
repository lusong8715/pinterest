<?php

function curlRequest($type, $url, $data=array()) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    if ($type == 'post') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    } else if ($type == 'delete') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}

function updateSitemap() {
    $filename = public_path('upload') . '/sitemap.xml';
    $fp = fopen($filename, 'w');
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $pins = \App\Models\Pins::all();
    foreach ($pins as $pin) {
        $xml .= sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>', $pin->url, date('Y-m-d') );
    }
    $xml .= '</urlset>';
    fwrite($fp, $xml);
    fclose($fp);
}

function getBoardNameForUrl($name) {
    $boardName = strtolower(preg_replace('/\s+/', '-', $name));
    $boardName = str_replace("'", '', $boardName);
    return $boardName;
}