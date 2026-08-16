<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | subscribers.php
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
require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_hasRights('hello.edit')) {
    $display = COM_startBlock($MESSAGE[30], '', COM_getBlockTemplate('_msg_block', 'header'));
    $display .= $MESSAGE[36];
    $display .= COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
    COM_accessLog("User {$_USER['username']} tried to illegally access the hello subscribers screen.");
    COM_output(COM_createHTMLDocument($display));
    exit;
}

require_once $_CONF['path_system'] . 'lib-admin.php';

$display = '';

// Handle quick manual unsubscribe/subscribe actions
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$uid = isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : 0;
if ($action == 'toggle' && $uid > 1 && SEC_checkToken()) {
    $new_state = (isset($_REQUEST['state']) && $_REQUEST['state'] == '1') ? 1 : 0;
    if (isset($_TABLES['user_attributes'])) {
        DB_query("UPDATE {$_TABLES['user_attributes']} SET emailfromadmin = $new_state WHERE uid = $uid");
    } else {
        DB_query("UPDATE {$_TABLES['userprefs']} SET emailfromadmin = $new_state WHERE uid = $uid");
    }
    $display .= COM_showMessageText($LANG_HELLO01['status_updated_success'], 'success');
}

// Build Subscribers List
$header_arr = array(
    array('text' => $LANG_HELLO01['col_username'], 'field' => 'username', 'sort' => true),
    array('text' => $LANG_HELLO01['col_email'], 'field' => 'email', 'sort' => true),
    array('text' => $LANG_HELLO01['col_subscription'], 'field' => 'emailfromadmin', 'sort' => true),
    array('text' => $LANG_HELLO01['emails_received'], 'field' => 'total_sent', 'sort' => true),
    array('text' => $LANG_HELLO01['col_opens'], 'field' => 'total_opened', 'sort' => true),
    array('text' => $LANG_HELLO01['col_clicks'], 'field' => 'total_clicks', 'sort' => true),
    array('text' => $LANG_HELLO01['col_action'], 'field' => 'action_toggle', 'sort' => false)
);

$defsort_arr = array('field' => 'username', 'direction' => 'ASC');

$text_arr = array(
    'has_extras' => true,
    'form_url'   => $_CONF['site_admin_url'] . '/plugins/hello/subscribers.php',
    'help_url'   => ''
);

// We need to build a complex query joining users, user attributes, hello_stats, and hello_urls_clicked.
// Since Geeklog's ADMIN_list doesn't handle complex group-by well out of the box in some versions,
// we will use subqueries in the SELECT clause to keep the main FROM clause clean.

$emailfromadmin_field = isset($_TABLES['user_attributes']) ? 'ua.emailfromadmin' : 'up.emailfromadmin';
$prefs_table = isset($_TABLES['user_attributes']) ? $_TABLES['user_attributes'] . ' ua' : $_TABLES['userprefs'] . ' up';

$sql = "SELECT u.uid, u.username, u.email, $emailfromadmin_field AS emailfromadmin,
        (SELECT SUM(sent) FROM {$_TABLES['hello_stats']} WHERE uid = u.uid) as total_sent,
        (SELECT SUM(opened) FROM {$_TABLES['hello_stats']} WHERE uid = u.uid) as total_opened,
        (SELECT COUNT(click_id) FROM {$_TABLES['hello_urls_clicked']} WHERE uid = u.uid) as total_clicks
        FROM {$_TABLES['users']} u
        LEFT JOIN $prefs_table ON u.uid = ua.uid 
        WHERE u.uid > 1 AND u.status = 3";
        
// Fix join condition alias based on table
if (!isset($_TABLES['user_attributes'])) {
    $sql = str_replace('ua.uid', 'up.uid', $sql);
}

$query_arr = array(
    'table' => 'users',
    'sql' => $sql,
    'query_fields' => array('u.username', 'u.email'),
    'default_filter' => ""
);

function HELLO_getListField_subscribers($fieldname, $fieldvalue, $A, $icon_arr) {
    global $_CONF, $LANG_HELLO01;
    $retval = '';
    
    switch ($fieldname) {
        case 'emailfromadmin':
            $retval = ($fieldvalue == 1) ? '<span style="color:green; font-weight:bold;">' . $LANG_HELLO01['sub_active'] . '</span>' : '<span style="color:red;">' . $LANG_HELLO01['sub_unsubscribed'] . '</span>';
            break;
        case 'total_sent':
            $sent = (int) $fieldvalue;
            if ($sent > 0) {
                $url = $_CONF['site_admin_url'] . "/plugins/hello/read_email.php?mode=user_emails&amp;uid={$A['uid']}";
                $retval = "<a href=\"$url\"><strong>$sent</strong></a>";
            } else {
                $retval = "$sent";
            }
            break;
        case 'total_opened':
            $sent = (int) $A['total_sent'];
            $opened = (int) $fieldvalue;
            $rate = ($sent > 0) ? round(($opened / $sent) * 100) : 0;
            if ($opened > 0) {
                $url = $_CONF['site_admin_url'] . "/plugins/hello/read_email.php?mode=user_opens&amp;uid={$A['uid']}";
                $retval = "<a href=\"$url\"><strong>$opened</strong></a> <em>($rate%)</em>";
            } else {
                $retval = "$opened <em>($rate%)</em>";
            }
            break;
        case 'total_clicks':
            $sent = (int) $A['total_sent'];
            $clicks = (int) $fieldvalue;
            $rate = ($sent > 0) ? round(($clicks / $sent) * 100) : 0;
            if ($clicks > 0) {
                $url = $_CONF['site_admin_url'] . "/plugins/hello/read_email.php?mode=user_clicks&amp;uid={$A['uid']}";
                $retval = "<a href=\"$url\"><strong>$clicks</strong></a> <em>($rate%)</em>";
            } else {
                $retval = "$clicks <em>($rate%)</em>";
            }
            break;
        case 'action_toggle':
            $token = SEC_createToken();
            $new_state = ($A['emailfromadmin'] == 1) ? 0 : 1;
            $label = ($A['emailfromadmin'] == 1) ? $LANG_HELLO01['force_unsubscribe'] : $LANG_HELLO01['resubscribe'];
            $url = $_CONF['site_admin_url'] . "/plugins/hello/subscribers.php?action=toggle&amp;uid={$A['uid']}&amp;state=$new_state&amp;" . CSRF_TOKEN . "=$token";
            $retval = '<a href="' . $url . '" style="font-size:11px;">[' . $label . ']</a>';
            break;
        default:
            $retval = stripslashes($fieldvalue);
            break;
    }
    return $retval;
}

$display .= hello_admin_menu($LANG_HELLO01['crm_dashboard'], $LANG_HELLO01['inst_crm']);
$display .= ADMIN_list('users', 'HELLO_getListField_subscribers', $header_arr, $text_arr, $query_arr, $defsort_arr);
$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

$display = COM_createHTMLDocument($display);
COM_output($display);
?>