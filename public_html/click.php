<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | click.php
// |                                                                           |
// | Geeklog hello plugin file                                                 |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2016-2026 by the following authors:                         |
// |                                                                           |
// | Authors: ::Ben - ben AT geeklog DOT fr                                    |
// +---------------------------------------------------------------------------+
// | Created with the Geeklog Plugin Toolkit.                                  |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

/**
* @package hello
*/
require_once '../lib-common.php';

if (!in_array('hello', $_PLUGINS)) {
    exit;
}

require_once $_CONF['path'] . 'plugins/hello/functions.inc';

$token = isset($_GET['t']) ? strtolower(trim($_GET['t'])) : '';
$url = '';
$uid = 0;
$hello_id = 0;
$test_mode = isset($_GET['test']) && (int) $_GET['test'] === 1;
$test_context = isset($_GET['k']) && $_GET['k'] === 'digest' ? 'digest' : 'campaign';

if (preg_match('/^[a-f0-9]{32}$/', $token)) {
    $safe_token = DB_escapeString($token);
    $result = DB_query("SELECT hello_id, uid, url FROM {$_TABLES['hello_links']} "
        . "WHERE token = '$safe_token' LIMIT 1");

    if ($result && DB_numRows($result) === 1) {
        $link = DB_fetchArray($result);
        $hello_id = (int) $link['hello_id'];
        $uid = (int) $link['uid'];
        $url = $link['url'];
    }
}

// Transitional support for links already sent by Hello 2.2.0.
// For security, legacy arbitrary redirects are accepted only to the site's
// own host. New messages never expose an encoded destination.
if ($url === '' && isset($_GET['url'], $_GET['u'], $_GET['h'])) {
    $legacy = base64_decode((string) $_GET['url'], true);
    $legacy_uid = (int) $_GET['u'];
    $legacy_hello_id = (int) $_GET['h'];

    if ($legacy !== false && filter_var($legacy, FILTER_VALIDATE_URL)
        && $legacy_uid > 1 && $legacy_hello_id > 0) {
        $legacy_scheme = strtolower((string) parse_url($legacy, PHP_URL_SCHEME));

        if ($legacy_scheme === 'http' || $legacy_scheme === 'https') {
            $legacy_allowed = false;

            // Preserve historical external links only when the destination can
            // be verified against the stored campaign content.
            $campaign_content = DB_getItem(
                $_TABLES['hello'],
                'content',
                'hello_id = ' . $legacy_hello_id
            );

            if (!empty($campaign_content)) {
                $decoded_legacy = html_entity_decode($legacy, ENT_QUOTES, 'UTF-8');
                $decoded_content = html_entity_decode(
                    stripslashes((string) $campaign_content),
                    ENT_QUOTES,
                    'UTF-8'
                );

                if (strpos($decoded_content, $decoded_legacy) !== false) {
                    $legacy_allowed = true;
                }
            }

            // Same-site links remain accepted as a conservative fallback for
            // old campaigns whose stored content is unavailable/incomplete.
            if (!$legacy_allowed) {
                $legacy_host = strtolower((string) parse_url($legacy, PHP_URL_HOST));
                $site_host = strtolower((string) parse_url($_CONF['site_url'], PHP_URL_HOST));

                if ($legacy_host !== '' && $legacy_host === $site_host) {
                    $legacy_allowed = true;
                }
            }

            if ($legacy_allowed) {
                $url = $legacy;
                $uid = $legacy_uid;
                $hello_id = $legacy_hello_id;
            }
        }
    }
}

if ($url !== '' && $uid > 1) {
    if ($test_mode && $hello_id === 0) {
        HELLO_recordTestTracking($uid, $test_context, 'click');
    } else if (!$test_mode && $hello_id > 0) {
        $safe_url = DB_escapeString($url);
        $click_time = date('Y-m-d H:i:s');

        $check_click = DB_query("SELECT click_id FROM {$_TABLES['hello_urls_clicked']} "
            . "WHERE hello_id = $hello_id AND uid = $uid AND url = '$safe_url'");
        if (DB_numRows($check_click) == 0) {
            DB_query("INSERT INTO {$_TABLES['hello_urls_clicked']} "
                . "(hello_id, uid, url, click_time) VALUES "
                . "($hello_id, $uid, '$safe_url', '$click_time')");
        }

        if (!isset($_HE_CONF['track_opens']) || (bool) $_HE_CONF['track_opens']) {
            DB_query("INSERT INTO {$_TABLES['hello_stats']} (hello_id, uid, opened) "
                . "VALUES ($hello_id, $uid, 1) ON DUPLICATE KEY UPDATE opened = 1");
        }
    }
}

if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if ($scheme === 'http' || $scheme === 'https') {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Referrer-Policy: no-referrer');
        header('Location: ' . $url, true, 302);
        exit;
    }
}

header('Location: ' . $_CONF['site_url'], true, 302);
exit;
?>
