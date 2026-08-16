<?php

/**
* @package hello
*/
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | index.php                                                                 |
// |                                                                           |
// | Geeklog hello administration page                                         |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2016-2026 by the following authors:                         |
// |                                                                           |
// | Authors: ::Ben - ben AT geeklog DOT fr                                    |
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
//

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

$display = '';

if (!SEC_hasRights ('hello.edit')) {
    $display .= COM_startBlock ($MESSAGE[30], '', COM_getBlockTemplate ('_msg_block', 'header'));
    $display .= $MESSAGE[36];
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display = COM_createHTMLDocument($display);
    COM_accessLog ("User {$_USER['username']} tried to illegally access the hello administration screen.");
    COM_output($display);
    exit;
}

function display_documentation() {
    global $_CONF, $LANG_HELLO01;
    $display = '<div style="margin-top: 30px; margin-bottom: 30px;">';
    $display .= '<details style="background: #f5f5f5; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">';
    $display .= '<summary style="font-weight: bold; cursor: pointer; font-size: 1.1em;">' . $LANG_HELLO01['doc_title'] . '</summary>';
    $display .= '<div style="padding-top: 15px;">';
    $display .= '<p>' . $LANG_HELLO01['overview'] . '</p>';
    $display .= '<h3>' . $LANG_HELLO01['doc_send_title'] . '</h3>' . $LANG_HELLO01['doc_send_body'];
    $display .= '<h3>' . $LANG_HELLO01['doc_crm_title'] . '</h3>' . $LANG_HELLO01['doc_crm_body'];
    $display .= '<h3>' . $LANG_HELLO01['doc_config_title'] . '</h3>' . $LANG_HELLO01['doc_config_body'];
    $display .= '<h3>' . $LANG_HELLO01['doc_mdigest_title'] . '</h3>' . $LANG_HELLO01['doc_mdigest_body'];
    $display .= '<h3>' . $LANG_HELLO01['doc_ddigest_title'] . '</h3>' . $LANG_HELLO01['doc_ddigest_body'];
    $cron_command = '*/2 * * * * /usr/bin/php ' . $_CONF['path'] . 'plugins/hello/cron.php ' . $_SERVER['HTTP_HOST'] . ' > /dev/null 2>&1';
    $display .= '<h3>' . $LANG_HELLO01['cron_title'] . '</h3>';
    $display .= '<p>' . $LANG_HELLO01['cron_desc'] . '</p>';
    $display .= '<pre style="background: #fff; padding: 10px; border: 1px solid #ccc; overflow-x: auto;">' . htmlspecialchars($cron_command) . '</pre>';
    $display .= '</div></details></div>';
    return $display;
}

function HELLO_search_form ($query = '')
{
    global $_CONF, $LANG_HELLO01;
    $display = '<form action="' . $_CONF['site_admin_url'] . '/plugins/hello/search.php" method="GET">' . LB;
    $display .= '<p>' . $LANG_HELLO01['search_text'] . '</p>' . LB;
    $display .= '<input type="text" size="40" name="query" value="' . htmlspecialchars($query, ENT_QUOTES) . '">' . LB;
    $display .= '<input type="submit" value="' . $LANG_HELLO01['search_button'] . '">' . LB;
    $display .= '<input type="hidden" name="mode" value="search">' . LB;
    $display .= '</form>' . LB;
    return $display;
}

function HELLO_send_digest ()
{
    global $_CONF, $_TABLES, $LANG_HELLO01, $PHP_SELF;
    $display = '';
    if ($_CONF['emailstories'] == 1) {
        if (isset ($_POST['sendit']) && !empty ($_POST['sendit']) && SEC_checkToken()) {
            $display .= '<p>' . $LANG_HELLO01['digest_sent'] . '</p>' . LB;
            $display .= HELLO_emailUserTopics(true, false);
        } else if (isset ($_POST['testit']) && !empty ($_POST['testit']) && SEC_checkToken()) {
            $display .= '<p style="color:green; font-weight:bold;">' . $LANG_HELLO01['test_sent'] . '</p>' . LB;
            $display .= HELLO_emailUserTopics(true, true);
        } else if (isset ($_POST['resetit']) && !empty ($_POST['resetit']) && SEC_checkToken()) {
            DB_query ("UPDATE {$_TABLES['vars']} SET value = NOW() WHERE name = 'lastemailedstories'");
            $display .= '<p>' . $LANG_HELLO01['digest_reset'] . '</p>' . LB;
        } else {
            $display .= '<p>' . $LANG_HELLO01['digest_intro'] . '</p>' . LB;
            $display .= '<p>' . $LANG_HELLO01['explain_reset'] . '</p>' . LB;
            $lastrun = DB_getItem ($_TABLES['vars'], 'value', "name = 'lastemailedstories'");
            if (empty ($lastrun)) {
                $display .= '<p>' . $LANG_HELLO01['digest_last_sent'] . ' ' . $LANG_HELLO01['never'] . '</p>' . LB;
                $lastrun = 0;
            } else {
                $display .= '<p>' . $LANG_HELLO01['digest_last_sent'] . ' <b>' . $lastrun . '</b></p>' . LB;
            }
            $sql = "SELECT sid FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= NOW() AND date >= '{$lastrun}'";
            $result = DB_query ($sql);
            $count = DB_numRows ($result);
            if ($count == 0) {
                $display .= '<p>' . $LANG_HELLO01['no_stories'] . '</p>' . LB;
            } else {
                $display .= '<p>' . sprintf($LANG_HELLO01['num_stories'], $count) . '</p>' . LB;
                $display .= '<form action="' . $PHP_SELF . '" method="POST"><div>';
                $display .= '<input type="submit" name="sendit" value="' . $LANG_HELLO01['send_button'] . '">';
                $display .= '&nbsp;<input type="submit" name="testit" value="' . $LANG_HELLO01['btn_test'] . '" title="' . $LANG_HELLO01['btn_test_title'] . '">';
                $display .= '&nbsp;<input type="submit" name="resetit" value="' . $LANG_HELLO01['reset_button'] . '">';
                $display .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . SEC_createToken() . '">';
                $display .= '</div></form>' . LB;
            }
        }
    } else {
        $display .= '<p>' . $LANG_HELLO01['not_enabled1'] . '</p>' . LB;
        $display .= '<blockquote><code>$_CONF[\'emailstories\'] = 1;</code></blockquote>' . LB;
        $display .= '<p>' . $LANG_HELLO01['not_enabled2'] . '</p>' . LB;
    }
    return $display;
}

function HELLO_getGlobalStats() {
    global $_TABLES, $LANG_HELLO01;
    $total_campaigns = (int) DB_getItem($_TABLES['hello'], 'COUNT(*)');
    $total_sent = (int) DB_getItem($_TABLES['hello_stats'], 'COUNT(*)', "sent = 1");
    $total_opens = (int) DB_getItem($_TABLES['hello_stats'], 'COUNT(*)', "opened >= 1");
    $result_clicks = DB_query("SELECT COUNT(DISTINCT uid) AS unique_clicks FROM {$_TABLES['hello_urls_clicked']}");
    $row_clicks = DB_fetchArray($result_clicks);
    $total_clicks = (int) $row_clicks['unique_clicks'];
    $open_rate = ($total_sent > 0) ? round(($total_opens / $total_sent) * 100) : 0;
    if ($open_rate > 100) $open_rate = 100;
    $click_rate = ($total_sent > 0) ? round(($total_clicks / $total_sent) * 100) : 0;
    if ($click_rate > 100) $click_rate = 100;
    $total_members = (int) DB_getItem($_TABLES['users'], 'COUNT(*)', "uid > 1 AND status = 3");
    $emailfromadmin_field = isset($_TABLES['user_attributes']) ? 'ua.emailfromadmin' : 'up.emailfromadmin';
    $prefs_table = isset($_TABLES['user_attributes']) ? $_TABLES['user_attributes'] . ' ua' : $_TABLES['userprefs'] . ' up';
    $prefs_join = isset($_TABLES['user_attributes']) ? 'u.uid = ua.uid' : 'u.uid = up.uid';
    $result_subscribed = DB_query("SELECT COUNT(*) AS count FROM {$_TABLES['users']} u LEFT JOIN $prefs_table ON $prefs_join WHERE u.uid > 1 AND u.status = 3 AND u.email != '' AND $emailfromadmin_field = 1");
    $row_subscribed = DB_fetchArray($result_subscribed);
    $total_subscribed = (int) $row_subscribed['count'];
    $subscribed_rate = ($total_members > 0) ? round(($total_subscribed / $total_members) * 100) : 0;
    $result_domains = DB_query("SELECT SUBSTRING_INDEX(u.email, '@', -1) AS domain, COUNT(*) AS count FROM {$_TABLES['users']} u LEFT JOIN $prefs_table ON $prefs_join WHERE u.uid > 1 AND u.status = 3 AND u.email != '' AND $emailfromadmin_field = 1 GROUP BY domain ORDER BY count DESC LIMIT 5");
    $top_domains_html = '<div style="text-align:left; font-size:14px; margin-top:10px;">';
    while ($row_domain = DB_fetchArray($result_domains)) {
        $dom_count = (int)$row_domain['count'];
        $dom_pct = ($total_subscribed > 0) ? round(($dom_count / $total_subscribed) * 100) : 0;
        $top_domains_html .= '<div style="margin-bottom:4px;"><strong>' . htmlspecialchars($row_domain['domain']) . '</strong> ' . $dom_count . ' <span style="color:#7f8c8d; font-size:12px;">(' . $dom_pct . '%)</span></div>';
    }
    $top_domains_html .= '</div>';
    $html = '<div style="display:flex; flex-wrap:wrap; justify-content:space-around; background:#f9f9f9; border:1px solid #e3e3e3; padding:20px; border-radius:8px; margin-bottom:25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
    $html .= '<div style="width:100%; display:flex; justify-content:space-around; margin-bottom:20px;">';
    $html .= '<div style="text-align:center; padding:10px;"><h2 style="margin:0 0 5px 0; font-size:28px; color:#2c3e50;">' . $total_campaigns . '</h2><span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_campaigns'] . '</span></div>';
    $html .= '<div style="text-align:center; padding:10px;"><h2 style="margin:0 0 5px 0; font-size:28px; color:#2980b9;">' . number_format($total_sent, 0, ',', ' ') . '</h2><span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_sent'] . '</span></div>';
    $html .= '<div style="text-align:center; padding:10px;"><h2 style="margin:0 0 5px 0; font-size:28px; color:#27ae60;">' . $open_rate . '%</h2><span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_open_rate'] . '</span></div>';
    $html .= '<div style="text-align:center; padding:10px;"><h2 style="margin:0 0 5px 0; font-size:28px; color:#8e44ad;">' . $click_rate . '%</h2><span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_click_rate'] . '</span></div></div>';
    $queue_remaining = (int) DB_getItem($_TABLES['hello_queue'], 'COUNT(*)');
    if ($queue_remaining == 0) {
        $queue_html = '<div style="margin-top:10px; padding:10px; background:#e8f8f5; border-left:4px solid #1abc9c; color:#16a085; font-size:14px; font-weight:bold;">' . $LANG_HELLO01['queue_empty'] . '</div>';
    } else {
        $result_q = DB_query("SELECT SUM(h.quantity) AS total_quantity FROM {$_TABLES['hello']} h WHERE h.hello_id IN (SELECT DISTINCT hello_id FROM {$_TABLES['hello_queue']})");
        $row_q = DB_fetchArray($result_q);
        $total_planned = (int) $row_q['total_quantity'];
        if ($total_planned < $queue_remaining) $total_planned = $queue_remaining;
        $total_sent_in_queue = $total_planned - $queue_remaining;
        $queue_pct = ($total_planned > 0) ? round(($total_sent_in_queue / $total_planned) * 100) : 0;
        $queue_html = '<div style="margin-top:10px;"><div style="display:flex; justify-content:space-between; font-size:13px; color:#34495e; margin-bottom:5px;"><span><strong>' . $queue_remaining . '</strong> ' . $LANG_HELLO01['queue_remaining'] . '</span><span>' . $queue_pct . $LANG_HELLO01['queue_sent_pct'] . '</span></div><div style="width:100%; background-color:#e0e0e0; border-radius:10px; overflow:hidden; height:14px;"><div style="width:' . $queue_pct . '%; background-color:#3498db; height:100%; transition:width 0.5s;"></div></div></div>';
    }
    $result_readers = DB_query("SELECT u.username, SUM(s.opened) as score FROM {$_TABLES['hello_stats']} s INNER JOIN {$_TABLES['users']} u ON s.uid = u.uid WHERE s.opened > 0 GROUP BY s.uid ORDER BY score DESC LIMIT 5");
    $top_readers_html = '<div style="text-align:left; font-size:14px; margin-top:10px;">';
    while ($row_reader = DB_fetchArray($result_readers)) {
        $top_readers_html .= '<div style="margin-bottom:4px;"><strong>' . htmlspecialchars($row_reader['username']) . '</strong> <span style="color:#7f8c8d; font-size:12px;">(' . (int)$row_reader['score'] . ' ' . $LANG_HELLO01['top_opens'] . ')</span></div>';
    }
    if (DB_numRows($result_readers) == 0) $top_readers_html .= '<div style="color:#7f8c8d; font-size:13px;">' . $LANG_HELLO01['top_no_data'] . '</div>';
    $top_readers_html .= '</div>';
    $html .= '<div style="width:100%; height:1px; background:#e3e3e3; margin:10px 0;"></div><div style="width:100%; display:flex; justify-content:space-between; align-items:flex-start; margin-top:20px;">';
    $html .= '<div style="width:50%; padding:10px; padding-right:20px;"><h3 style="margin:0 0 10px 0; font-size:15px; color:#34495e; text-transform:uppercase;">' . $LANG_HELLO01['queue_status'] . '</h3>' . $queue_html . '</div>';
    $html .= '<div style="width:40%; padding:10px; border-left:1px solid #e3e3e3; padding-left:20px;"><h3 style="margin:0 0 5px 0; font-size:15px; color:#34495e; text-transform:uppercase;">' . $LANG_HELLO01['top_readers'] . '</h3>' . $top_readers_html . '</div></div>';
    $html .= '<div style="width:100%; height:1px; background:#e3e3e3; margin:10px 0;"></div><div style="width:100%; display:flex; justify-content:space-around; align-items:flex-start; margin-top:20px;">';
    $html .= '<div style="text-align:center; padding:10px;"><h2 style="margin:0 0 5px 0; font-size:28px; color:#34495e;">' . number_format($total_members, 0, ',', ' ') . '</h2><span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_members'] . '</span></div>';
    $html .= '<div style="text-align:center; padding:10px;"><h2 style="margin:0 0 5px 0; font-size:28px; color:#e67e22;">' . number_format($total_subscribed, 0, ',', ' ') . ' <span style="font-size:16px;">(' . $subscribed_rate . '%)</span></h2><span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_subscribed'] . '</span></div>';
    $html .= '<div style="text-align:center; padding:10px;"><h3 style="margin:0 0 5px 0; font-size:15px; color:#34495e; text-transform:uppercase;">' . $LANG_HELLO01['top_domains'] . '</h3>' . $top_domains_html . '</div></div></div>';
    return $html;
}

$display .= hello_admin_menu($LANG_HELLO01['block_headline'], $LANG_HELLO01['inst_index']);
$display .= HELLO_getGlobalStats();
if (SEC_hasRights ('user.mail')) {
    $display .= '<h3>' . $LANG_HELLO01['mdigest'] . '</h3>';
    $display .= HELLO_send_digest();
}
if (SEC_hasRights ('hello.edit')) {
    $display .= '<h3>' . $LANG_HELLO01['block_headline'] . '</h3>';
    $display .= HELLO_search_form();
}
$display .= display_documentation();
$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
$display = COM_createHTMLDocument($display);
COM_output($display);
?>