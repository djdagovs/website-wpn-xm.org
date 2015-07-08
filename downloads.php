<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2015 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */
/**
 * Downloads Listing Script for wpn-xm.org
 * ---------------------------------------
 * The script provides helper methods to generate a dynamic download list
 * based on files found in a specific downloads folder.
 */

/**
 * Formats filesize in human readable way.
 *
 * @param file $file
 *
 * @return string Formatted Filesize.
 */
function filesize_formatted($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes === 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

/**
 * Builds a md5 checksum for a file and writes it to a file for later reuse.
 *
 * @param string $filename
 *
 * @return string md5 file checksum
 */
function md5_checksum($filename)
{
    $md5 = '';

    $path = pathinfo($filename);
    $dir  = __DIR__ . '/' . $path['dirname'] . '/checksums/';
    if (is_dir($dir) === false) {
        mkdir($dir);
    }
    $md5ChecksumFile = $dir . $path['filename'] . '.md5';

    if (is_file($md5ChecksumFile) === true) {
        return file_get_contents($md5ChecksumFile);
    } else {
        $md5 = md5_file($filename);
        file_put_contents($md5ChecksumFile, $md5);
    }

    return $md5;
}

/**
 * Builds a sha1 checksum for a file and writes it to a file for later reuse.
 *
 * @param string $filename
 *
 * @return string sha1 file checksum
 */
function sha1_checksum($filename)
{
    $sha1 = '';

    $path = pathinfo($filename);
    $dir  = __DIR__ . '/' . $path['dirname'] . '/checksums/';
    if (is_dir($dir) === false) {
        mkdir($dir);
    }
    $sha1ChecksumFile = $dir . $path['filename'] . '.sha1';

    if (is_file($sha1ChecksumFile) === true) {
        $sha1 = file_get_contents($sha1ChecksumFile);
    } else {
        $sha1 = sha1_file($filename);
        file_put_contents($sha1ChecksumFile, $sha1);
    }

    return $sha1;
}

function get_github_releases()
{
    $cache_file = __DIR__ . '/downloads/github-releases-cache.json';

    if (file_exists($cache_file) && (filemtime($cache_file) > (time() - (7 * 24 * 60 * 60)))) {
        // Use cache file, when not older than 7 days.
        $data = file_get_contents($cache_file);
    } else {
        // The cache is out-of-date. Load the JSON data from Github.
        $data = curl_request();
        file_put_contents($cache_file, $data, LOCK_EX);
    }

    $array = json_decode($data, true);

    return $array;
}

function get_github_releases_tag($release_tag)
{
    $releases = get_github_releases();

    foreach ($releases as $release) {
        if ($release['tag_name'] === $release_tag) {
            return $release;
        }
    }
}

function get_total_downloads($release)
{
    $downloadsTotal = 0;

    foreach ($release['assets'] as $idx => $asset) {
        $downloadsTotal += $asset['download_count'];
    }

    return $downloadsTotal;
}

function render_github_releases()
{
    $releases = get_github_releases();

    $html = '';

    foreach ($releases as $release) {
        // skip our first release - only commits, no downloads
        if ($release['tag_name'] === '0.2.0') {
            continue;
        }

        unset($release['author']);

        if ($release['prerelease'] === false) {
            $html .= '<tr>'
                . '<td width="50%" style="vertical-align: middle;">'
                . '<h2 style="text-align: left;">' . $release['name'] . '&nbsp;'
                . '<small class="btn btn-sm" title="Release Date">Release Date<br><span class="bold">' . date('d M Y', strtotime($release['created_at'])) . '</span></small>'
                . '&nbsp;'
                . '<small class="btn btn-sm" title="Total Downloads">Downloads<br><span class="bold">' . get_total_downloads($release) . '</span></small>'
                . '</h2>'
                . '</td>';

            // release notes, e.g. https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.5.3
            $release_notes = '<a class="btn btn-large btn-info"'
                . 'href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-' . $release['tag_name'] . '">Release Notes</a>';

            // changelog, e.g. https://github.com/WPN-XM/WPN-XM/blob/0.5.2/changelog.txt
            $changelog = '<a class="btn btn-large btn-info"'
                . 'href="https://github.com/WPN-XM/WPN-XM/blob/' . $release['tag_name'] . '/changelog.txt">Changelog</a>';

            // component list with version numbers
            // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
            $github_tag = '<a class="btn btn-large btn-info"'
                . 'href="https://github.com/WPN-XM/WPN-XM/tree/' . $release['tag_name'] . '">Github Tag</a>';

            // print release notes, changelog, github tag once per version
            $html .= '<td style="vertical-align: middle;">' . $release_notes . '&nbsp;' . $changelog . '&nbsp;' . $github_tag . '</td>';
            $html .= '</tr>';

            foreach ($release['assets'] as $idx => $asset) {
                unset($asset['uploader'], $asset['url'], $asset['label'], $asset['content_type'], $asset['updated_at']);

                // download button for installer, filesize, downloadcounter
                $html .= '<tr><td colspan="2">';
                $html .= '<table border="0" width="100%">';
                $html .= '<th rowspan="2" width="66%">';
                $html .= '<a class="btn btn-sm btn-success" href="' . $asset['browser_download_url'] . '"><span class="glyphicon glyphicon-cloud-download"></span> ' . $asset['name'] . '</a></th>';
                $html .= '<tr><td>';
                $html .= '<div class="btn btn-small bold" title="Filesize">' . filesize_formatted($asset['size']) . '</div>&nbsp;';
                $html .= '<div class="btn btn-small bold" title="Downloads">' . $asset['download_count'] . '</div>';
                $html .= '</td></tr></table>';

                // component list for the installer
                $html .= render_component_list_for_installer($asset['name']);
            }

            $html .= '</td></tr>';
        }
    }
    return $html;
}

function curl_request()
{
    $headers[] = 'Accept: application/vnd.github.manifold-preview+json';

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => 'https://api.github.com/repos/wpn-xm/wpn-xm/releases',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_USERAGENT      => 'wpn-xm.org - downloads page',
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

// ----- Gather details for all available files

if (!is_dir(__DIR__ . '/downloads')) {
    echo 'The downloads directory is missing.';
    exit();
}

$downloads = [];
$details   = [];

# scan folder for files
foreach (glob('./downloads/*.exe') as $filename) {

    // file
    $file            = str_replace('./downloads/', '', $filename);
    $details['file'] = $file;

    // size
    $bytes           = filesize($filename);
    $details['size'] = filesize_formatted($bytes);

    $details = array_merge($details, get_installer_details($file));

    // md5 & sha1 hashes / checksums
    $details['md5']  = md5_checksum(substr($filename, 2));
    $details['sha1'] = sha1_checksum(substr($filename, 2));

    // download URL
    $details['download_url'] = 'http://wpn-xm.org/downloads/' . $file;

    // download link
    $details['link'] = '<a href="' . $details['download_url'] . '">' . $file . '</a>';

    // release notes, e.g. https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.5.3
    $details['release_notes'] = '<a class="btn btn-large btn-info"'
        . 'href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v' . $details['version'] . '">Release Notes</a>';

    // put "v" in front to get a properly versionized tag, starting from version "0.8.0"
    $version = (version_compare($details['version'], '0.8.0')) ? $details['version'] : 'v' . $details['version'];

    // changelog, e.g. https://github.com/WPN-XM/WPN-XM/blob/0.5.2/changelog.txt
    $details['changelog'] = '<a class="btn btn-large btn-info"'
        . 'href="https://github.com/WPN-XM/WPN-XM/blob/' . $version . '/changelog.txt">Changelog</a>';

    // component list with version numbers
    // link to github tag, e.g. https://github.com/WPN-XM/WPN-XM/tree/0.5.2
    $details['github_tag'] = '<a class="btn btn-large btn-info"'
        . 'href="https://github.com/WPN-XM/WPN-XM/tree/' . $version . '">Github Tag</a>';

    // date
    $details['date'] = date('d.m.Y', filectime($filename));

    // add download details to downloads array
    $downloads[] = $details;

    // reset array for next loop
    $details = [];
}

// ----- Gather some general data for the downloads list
// order downloads - latest version first
arsort($downloads);

// reindex
array_splice($downloads, 0, 0);

// add "versions", listing "all available version"
$versions = [];
foreach ($downloads as $download) {
    if (isset($download['version']) === true) {
        $versions[] = $download['version'];
    }
}
$downloads['versions'] = array_unique($versions);

// add "latest" as array key, referring to the latest version of WPN-XM
$downloads['latest_version']              = $downloads[0]['version'];
$downloads['latest_version_release_date'] = $downloads[0]['date'];

/*
  Example Downloads Array

  link, release_notes, changelog, github_tag are HTML anchor tags.

  array (
  39 =>
  array (
  'file' => 'WPNXM-0.8.0-Webinstaller-Setup-php56-w64.exe',
  'size' => '1.59 MB',
  'version' => '0.8.0',
  'installer' => 'Webinstaller',
  'phpversion' => 'php56',
  'platform' => 'w64',
  'md5' => '6ae27511a06bfbc98472283b30565913',
  'sha1' => '7258ed16afe86611572e1b5ea9f879b41adf4be1',
  'download_url' => 'http://wpn-xm.org/downloads/WPNXM-0.8.0-Webinstaller-Setup-php56-w64.exe',
  'link' => '<a href="http://wpn-xm.org/downloads/WPNXM-0.8.0-Webinstaller-Setup-php56-w64.exe">WPNXM-0.8.0-Webinstaller-Setup-php56-w64.exe</a>',
  'release_notes' => '<a class="btn btn-large btn-info"href="https://github.com/WPN-XM/WPN-XM/wiki/Release-Notes-v0.8.0">Release Notes</a>',
  'changelog' => '<a class="btn btn-large btn-info"href="https://github.com/WPN-XM/WPN-XM/blob/v0.8.0/changelog.txt">Changelog</a>',
  'github_tag' => '<a class="btn btn-large btn-info"href="https://github.com/WPN-XM/WPN-XM/tree/v0.8.0">Github Tag</a>',
  'date' => '20.09.2014',
  ),

 */

// ----- GET
// accept "type" as a get parameter, e.g. index.php?type=json
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

// send download list as json
if (!empty($type) && ($type === 'json')) {
    header('Content-Type: application/json');
    echo json_encode($downloads);
} else {
    // send html page
    // load software components registry
    $registry = include __DIR__ . '/registry/wpnxm-software-registry.php';

    // ensure registry array is available
    if (!is_array($registry)) {
        header('HTTP/1.0 404 Not Found');
    }

    // send with header (downloads.php) or without header (downloads.php?type=only-body)
    if (!empty($type) && ($type === 'only-body')) {
        $html = '';
    } else {
        $html = render_header();
    }

    unset($downloads['versions'], $downloads['latest_version'], $downloads['latest_version_release_date']);
    $version = '0.0.0';

    $html .= '<table style="width:auto; min-width:900px">';

    $html .= render_github_releases();

    foreach ($downloads as $download) {

        // print version only once for all files of that version
        if ($version !== $download['version']) {
            $version = $download['version'];

            $html .= '<tr>';
            $html .= '<td width="50%" style="vertical-align: middle;">';
            $html .= '<h2>WPИ-XM v' . $version . '&nbsp;<small>' . date('d M Y', strtotime($download['date'])) . '</small></h2>';
            $html .= '</td>';

            // print release notes, changelog, github tag once per version
            $html .= '<td style="vertical-align: middle;">';
            $html .= $download['release_notes'] . '&nbsp;';
            $html .= $download['changelog'] . '&nbsp;';
            $html .= $download['github_tag'];
            $html .= '</td>';
            $html .= '</tr>';
        }

        // download details
        $html .= '<td colspan="2">';
        $html .= '<table width="100%">';
        $html .= '<th rowspan="2" width="66%"><a class="btn btn-success btn-large" href="' . $download['download_url'] . '">' . $download['file'] . '</a></th>';
        $html .= '<tr><td><div class="btn btn-mini bold">' . $download['size'] . '</div></td><td>';
        $html .= '<button id="copy-to-clipboard" title="Copy hash to clipboard." class="btn btn-mini zclip" data-zclip-text="' . $download['md5'] . '">MD5</button>&nbsp;';
        $html .= '<button id="copy-to-clipboard" title="Copy hash to clipboard." class="btn btn-mini zclip" data-zclip-text="' . $download['sha1'] . '">SHA-1</button>';
        $html .= '</td></tr>';

        $html .= render_component_list_for_installer($download['file']);

        $html .= '</table>';
        $html .= '</td></tr>';
    }
    $html .= '</table><br/>';

    header('Content-Type: text/html; charset=utf-8');
    echo $html;
}

function get_installer_details($installer_filename)
{
    $details = [];

    // WPNXM-0.5.4-BigPack-Setup - without PHP version constraint
    if (substr_count($installer_filename, '-') === 3) {
        if (preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup.exe/', $installer_filename, $matches)) {
            $details['version']   = $matches['version'];
            $details['installer'] = $matches['installer'];
            $details['platform']  = 'w32';
        }
    }

    // WPNXM-0.5.4-BigPack-Setup-w32
    if (substr_count($installer_filename, '-') === 4) {
        if (preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup-(?<bitsize>.*).exe/', $installer_filename, $matches)) {
            $details['version']   = $matches['version'];
            $details['installer'] = $matches['installer'];
            $details['platform']  = $matches['bitsize']; //w32|w64
        }
    }

    // WPNXM-0.8.0-Full-Setup-php54-w32
    if (substr_count($installer_filename, '-') === 5) {
        if (preg_match('/WPNXM-(?<version>.*)-(?<installer>.*)-Setup-(?<phpversion>.*)-(?<bitsize>.*).exe/', $installer_filename, $matches)) {
            $details['version']    = $matches['version'];
            $details['installer']  = $matches['installer'];
            $details['phpversion'] = $matches['phpversion'];
            $details['platform']   = $matches['bitsize']; //w32|w64
        }
    }

    $details['name'] = $installer_filename;

    return $details;
}

function render_component_list_for_installer($installer_name)
{
    global $registry;

    $download = get_installer_details($installer_name);

    $html = '';

    // Components
    if ('webinstaller' === strtolower($download['installer'])) {
        $html .= '<tr><td colspan="3">Latest Components fetched from the Web</td></tr>';
    } else {
        $platform = isset($download['platform']) ? '-' . $download['platform'] : '';

        // set PHP version starting from 0.8.0 on
        $phpversion = isset($download['phpversion']) ? '-' . $download['phpversion'] : '';

        // PHP version dot fix
        $phpversion = str_replace(['php5', 'php7'], ['php5.', 'php7.'], $phpversion);

        $registry_file = __DIR__ . '/registry/' . strtolower($download['installer']) . '-' . $download['version'] . $phpversion . $platform . '.json';

        if (!is_file($registry_file)) {
            return '</p></td></tr>';
        }

        $installerRegistry = json_decode(file_get_contents($registry_file));

        $number_of_components = count($installerRegistry);

        $html .= '<tr><td colspan="3">Components (' . $number_of_components . ')<p>';

        //if($number_of_components >= 10) {
        $html .= render_component_list_multi_column($registry, $installerRegistry);
        //} else {
        //  $html .= render_component_list_comma_separated($registry, $installerRegistry, $number_of_components);
        //}

        $html .= '</p></td></tr>';
    }

    return $html;
}

function render_component_list_multi_column($registry, $installerRegistry)
{
    $html = '';
    $html .= '<ul id="multi-column-list">';

    $extensions_html = '<br>PHP Extension(s): ';

    foreach ($installerRegistry as $i => $component) {
        $shortName = $component[0];

        // skip - components removed from registry, still in 0.7.0 and breaking it
        if (in_array($shortName, ['phpext_xcache', 'junction'])) {
            continue;
        }

        $version = $component[3];

        // php extension - they are appended to the extension html fragment
        if (false !== strpos($shortName, 'phpext_')) {
            $name = str_replace('PHP Extension ', '', $registry[$shortName]['name']);
            $extensions_html .= render_component_li($name, $version);
            continue;
        }

        // normal component
        $name = $registry[$shortName]['name'];
        $html .= render_component_li($name, $version);
    }
    unset($installerRegistry);

    $html .= $extensions_html;
    $html .= '</ul>';

    return $html;
}

function render_component_list_comma_separated($registry, $installerRegistry, $number_of_components)
{
    $html            = '';
    $extensions_html = ', PHP Extension(s): ';

    foreach ($installerRegistry as $i => $component) {
        $shortName = $component[0];
        $version   = $component[3];

        // skip - removed from registry, still in 0.7.0 and breaking it
        if ($shortName === 'phpext_xcache') {
            continue;
        }

        if (false !== strpos($component[0], 'phpext_')) {
            $name = str_replace('PHP Extension ', '', $registry[$component[0]]['name']);
            $extensions_html .= '<span class="bold">' . $name . '</span> ' . $version;
            continue;
        }

        $name = $registry[$shortName]['name'];

        $html .= '<span style="font-weight:bold;">' . $name . '</span> ' . $version;
        $html .= ($i + 1 !== $number_of_components) ? ', ' : '';
    }
    unset($installerRegistry);

    $html .= $extensions_html;

    return $html;
}

function render_component_li($name, $version)
{
    return '<li><span class="bold">' . $name . '</span> ' . $version . '</li>';
}

function render_header()
{
    return <<<EOD
<!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head prefix="og: https://ogp.me/ns# fb: https://ogp.me/ns/fb# website: https://ogp.me/ns/website#">
  <meta charset="utf-8" />
  <title>WPN-XM - is a free and open-source web server solution stack for professional PHP development on the Windows&reg; platform.</title>
  <meta http-equiv="x-ua-compatible" content="IE=EmulateIE7" />
  <!-- Google Site Verification -->
  <meta name="google-site-verification" content="OxwcTMUNiYu78EIEA2kq-vg_CoTyhGL-YVKXieCObDw" />
  <meta name="Googlebot" content="index,follow">
  <meta name="Author" content="Jens-Andre Koch" />
  <meta name="Copyright" content="(c) 2011-onwards Jens-Andre Koch." />
  <meta name="Publisher" content="Koch Softwaresystemtechnik" />
  <meta name="Rating" content="general" />
  <meta name="page-type" content="Homepage, Website" />
  <meta name="robots" content="index, follow, all, noodp" />
  <meta name="Description" content="WPN-XM - is a free and open-source web server solution stack for professional PHP development on the Windows platform." />
  <meta name="keywords" content="WPN-XM, free, open-source, server, NGINX, PHP, Windows, MariaDb, MongoDb, Adminer, XDebug, WAMP, WIMP, WAMPP, LAMP" />
  <!-- avgthreatlabs.com Site Verification -->
  <meta name="avgthreatlabs-verification" content="247b6d3c405a91491b1fea8e89fb3b779f164a5f" />
  <!-- DC -->
  <meta name="DC.Title" content="WPN-XM" />
  <meta name="DC.Creator" content="Jens-Andre Koch" />
  <meta name="DC.Publisher" content="Koch Softwaresystemtechnik" />
  <meta name="DC.Type" content="Service" />
  <meta name="DC.Format" content="text/html" />
  <meta name="DC.Language" content="en" />
  <!-- Geo -->
  <meta name="geo.region" content="DE-MV" />
  <meta name="geo.placename" content="Neubrandenburg" />
  <meta name="geo.position" content="53.560348;13.249941" />
  <meta name="ICBM" content="53.560348, 13.249941" />
  <!-- Facebook OpenGraph -->
    <meta property="og:url" content="http://wpn-xm.org/" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="WPN-XM - is a free and open-source web server solution stack for professional PHP development on the Windows platform." />
    <meta property="og:description" content="WPN-XM - is a free and open-source web server solution stack for professional PHP development on the Windows platform." />
    <!-- Favicon & Touch-Icons -->
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="favicon.ico" />
    <link rel="apple-touch-icon" href="images/touch/apple-touch-icon.png" />
    <link rel="apple-touch-icon" sizes="57x57" href="images/touch/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="60x60" href="images/touch/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="images/touch/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="images/touch/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="images/touch/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="images/touch/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="images/touch/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="images/touch/apple-touch-icon-152x152.png" />
    <!-- Bootstrap CSS Framework -->
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    <!-- Javascripts -->
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/bootstrap.min.js"></script>    
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="/js/html5shiv.js"></script>
    <![endif]-->
    <!-- Google Analytics -->
    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-26811143-1']);
      _gaq.push(['_trackPageview']);

      (function () {
          var ga = document.createElement('script');
          ga.type = 'text/javascript';
          ga.async = true;
          ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
          var s = document.getElementsByTagName('script')[0];
          s.parentNode.insertBefore(ga, s);
      })();
    </script>
</head>
<body>
EOD;
}
