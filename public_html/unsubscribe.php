<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | unsubscribe.php
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
    COM_displayMessageAndAbort($LANG_HELLO01['plugin_disabled'], 'hello', 403, 'Forbidden');
}

$uid = isset($_GET['u']) ? (int) $_GET['u'] : 0;
$hash = isset($_GET['h']) ? $_GET['h'] : '';
$hello_id = isset($_GET['hid']) ? (int) $_GET['hid'] : 0;
$test_mode = isset($_GET['test']) && (int) $_GET['test'] === 1;

$action = isset($_GET['action']) ? $_GET['action'] : 'unsubscribe';

if ($uid > 1 && !empty($hash)) {
    // Get user email to verify hash
    $email = DB_getItem($_TABLES['users'], 'email', "uid = $uid");

    if ($email && MD5($email . "fFersh66") === $hash) {
        if ($action === 'resubscribe') {
            if (!$test_mode && $uid != 2) {
                if (isset($_TABLES['user_attributes'])) {
                    DB_query("UPDATE {$_TABLES['user_attributes']} SET emailfromadmin = 1 WHERE uid = $uid");
                } else {
                    DB_query("UPDATE {$_TABLES['userprefs']} SET emailfromadmin = 1 WHERE uid = $uid");
                }
            }

            $content = COM_startBlock($LANG_HELLO01['resub_title']);
            $content .= '<p style="text-align:center; margin-top:20px; color:green; font-weight:bold;">'
                . $LANG_HELLO01['resub_success'] . '</p>';
            if ($test_mode) {
                $content .= '<div role="status" style="margin:20px auto; max-width:720px; padding:14px 16px; border:2px solid #2e7d32; background:#edf7ed; color:#1b5e20; font-weight:bold; text-align:center;">'
                    . $LANG_HELLO01['resub_test_success'] . '</div>';
            }
            $content .= COM_endBlock();

            $display = COM_createHTMLDocument($content, array('pagetitle' => $LANG_HELLO01['resub_title']));
            COM_output($display);
            exit;
        }

        $is_post = isset($_SERVER['REQUEST_METHOD'])
            && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';

        // RFC 8058 providers unsubscribe by POSTing to the URL from the
        // List-Unsubscribe header. Browser GETs show a confirmation page so
        // security scanners cannot unsubscribe a user merely by following it.
        if (!$is_post) {
            $post_url = $_CONF['site_url'] . '/hello/unsubscribe.php?h='
                . rawurlencode($hash) . '&amp;u=' . $uid . '&amp;hid=' . $hello_id;
            if ($test_mode) {
                $post_url .= '&amp;test=1';
            }

            $content = COM_startBlock($LANG_HELLO01['unsub_title']);
            if ($test_mode) {
                $content .= '<div role="alert" style="margin:20px auto; max-width:720px; padding:14px 16px; border:2px solid #e0a800; background:#fff8db; color:#5d4700; font-weight:bold; text-align:center;">'
                    . $LANG_HELLO01['unsub_test_warning'] . '</div>';
            }
            $content .= '<p style="text-align:center; margin-top:20px;">'
                . $LANG_HELLO01['unsub_confirm_msg'] . '</p>';
            $content .= '<form method="post" action="' . $post_url . '" style="text-align:center; margin-top:20px;">';
            // Keep browser confirmation separate from RFC 8058 one-click POSTs.
            // Otherwise the human confirmation would receive the plain-text
            // response intended for mailbox providers.
            $content .= '<input type="hidden" name="hello_confirm_unsubscribe" value="1" />';
            $content .= '<button type="submit">' . $LANG_HELLO01['unsub_confirm_btn'] . '</button>';
            $content .= '</form>';
            $content .= COM_endBlock();

            $display = COM_createHTMLDocument($content, array('pagetitle' => $LANG_HELLO01['unsub_title']));
            COM_output($display);
            exit;
        }

        if (!$test_mode && $uid != 2) {
            if (isset($_TABLES['user_attributes'])) {
                DB_query("UPDATE {$_TABLES['user_attributes']} SET emailfromadmin = 0 WHERE uid = $uid");
            } else {
                DB_query("UPDATE {$_TABLES['userprefs']} SET emailfromadmin = 0 WHERE uid = $uid");
            }

            if ($hello_id > 0) {
                DB_query("INSERT INTO {$_TABLES['hello_stats']} (hello_id, uid, unsubscribed) "
                    . "VALUES ($hello_id, $uid, 1) ON DUPLICATE KEY UPDATE unsubscribed = 1");
            }
        }

        // RFC 8058 automated one-click requests do not need a full themed page.
        // Browser confirmations use hello_confirm_unsubscribe instead.
        if (!$test_mode && isset($_POST['List-Unsubscribe']) && $_POST['List-Unsubscribe'] === 'One-Click') {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Content-Type: text/plain; charset=utf-8');
            echo "Unsubscribed\n";
            exit;
        }

        $content = COM_startBlock($LANG_HELLO01['unsub_success_heading']);
        if ($test_mode) {
            $content .= '<div role="status" style="margin:20px auto; max-width:720px; padding:14px 16px; border:2px solid #2e7d32; background:#edf7ed; color:#1b5e20; font-weight:bold; text-align:center;">'
                . $LANG_HELLO01['unsub_test_success'] . '</div>';
        } else {
            $content .= '<p style="text-align:center; margin-top:20px;">'
                . $LANG_HELLO01['unsub_success_msg'] . '</p>';
        }

        $resubscribe_url = $_CONF['site_url'] . '/hello/unsubscribe.php?h='
            . rawurlencode($hash) . '&amp;u=' . $uid . '&amp;hid=' . $hello_id
            . '&amp;action=resubscribe';
        if ($test_mode) {
            $resubscribe_url .= '&amp;test=1';
        }

        $content .= '<div style="text-align:center; margin-top:40px; padding:20px; background-color:#f9f9f9; border-radius:8px;">';
        $content .= '<p style="margin-bottom:15px; color:#555;">' . $LANG_HELLO01['unsub_mistake'] . '</p>';
        $content .= '<a href="' . $resubscribe_url . '" style="display:inline-block; padding:10px 20px; background-color:#2c3e50; color:#fff; text-decoration:none; border-radius:5px;">'
            . $LANG_HELLO01['resub_btn'] . '</a>';
        $content .= '</div>';
        $content .= COM_endBlock();

        $display = COM_createHTMLDocument($content, array('pagetitle' => $LANG_HELLO01['unsub_title']));
        COM_output($display);
        exit;
    }
}

$content = COM_startBlock($LANG_HELLO01['invalid_link_title']);
$content .= '<p style="text-align:center; margin-top: 20px;">' . $LANG_HELLO01['invalid_link_msg'] . '</p>';
$content .= COM_endBlock();

$display = COM_createHTMLDocument($content, array('pagetitle' => $LANG_HELLO01['invalid_link_title']));
COM_output($display);
exit;
?>