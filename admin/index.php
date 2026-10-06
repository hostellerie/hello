<?php

/**
* @package hello
*/
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.3.0                                                        |
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
    $display .= COM_startBlock ($MESSAGE[30], '',
                                COM_getBlockTemplate ('_msg_block', 'header'));
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
    
    // Add usage instructions
    $display .= '<h3>' . $LANG_HELLO01['doc_send_title'] . '</h3>';
    $display .= $LANG_HELLO01['doc_send_body'];
    
    $display .= '<h3>' . $LANG_HELLO01['doc_crm_title'] . '</h3>';
    $display .= $LANG_HELLO01['doc_crm_body'];
    
    $display .= '<h3>' . $LANG_HELLO01['doc_config_title'] . '</h3>';
    $display .= $LANG_HELLO01['doc_config_body'];
    
    $display .= '<h3>' . $LANG_HELLO01['doc_mdigest_title'] . '</h3>';
    $display .= $LANG_HELLO01['doc_mdigest_body'];
    
    $display .= '</div>';
    $display .= '</details>';
    $display .= '</div>';
	
	return $display;
}

//From mdigest plugin
function HELLO_search_form ($query = '')
{
    global $_CONF, $LANG_HELLO01, $PHP_SELF;

    $display = '';

    $display .= '<form action="' . $_CONF['site_admin_url'] . '/plugins/hello/search.php" method="GET">' . LB;
    $display .= '<p>' . $LANG_HELLO01['search_text'] . '</p>' . LB;
    $display .= '<input type="text" size="40" name="query" value="' . $query . '">' . LB;
    $display .= '<input type="submit" value="' . $LANG_HELLO01['search_button'] . '">' . LB;
    $display .= '<input type="hidden" name="mode" value="search">' . LB;
    $display .= '</form>' . LB;

    return $display;
}

function HELLO_send_digest()
{
    global $_CONF, $_TABLES, $LANG_HELLO01, $PHP_SELF, $_USER;

    $digest_item_limit = HELLO_getDigestItemLimit();

    $display = '';

    if ($_CONF['emailstories'] != 1) {
        $display .= '<p>' . $LANG_HELLO01['not_enabled1'] . '</p>' . LB;
        $display .= '<blockquote><code>$_CONF[\'emailstories\'] = 1;</code></blockquote>' . LB;
        $display .= '<p>' . $LANG_HELLO01['not_enabled2'] . '</p>' . LB;
        return $display;
    }

    if (isset($_USER['uid']) && (int) $_USER['uid'] > 1) {
        $display .= HELLO_testTrackingStatusHtml((int) $_USER['uid'], 'digest');
    }

    $last_sent = HELLO_getDigestBoundary();

    $since = isset($_POST['digest_since']) ? trim($_POST['digest_since']) : $last_sent;
    $subject = isset($_POST['digest_subject'])
        ? trim($_POST['digest_subject'])
        : '[' . $_CONF['site_name'] . '] ' . $LANG_HELLO01['digest_default_subject'];
    $intro = isset($_POST['digest_intro_text']) ? trim($_POST['digest_intro_text']) : '';

    $available_sources = HELLO_getAvailableDigestSources();
    $sources = array('stories');
    if (isset($_POST['digest_source']) && is_array($_POST['digest_source'])) {
        $sources = array();
        foreach ($_POST['digest_source'] as $source_id) {
            $source_id = strtolower(trim((string) $source_id));
            if (isset($available_sources[$source_id])) {
                $sources[] = $source_id;
            }
        }
        $sources = array_values(array_unique($sources));
    }

    $selected = array();
    if (isset($_POST['digest_item']) && is_array($_POST['digest_item'])) {
        foreach ($_POST['digest_item'] as $item_key) {
            $item_key = trim((string) $item_key);
            if (strpos($item_key, ':') !== false) {
                $selected[] = $item_key;
            }
        }
        $selected = array_values(array_unique($selected));
    }

    if (isset($_POST['digest_story']) && is_array($_POST['digest_story'])) {
        foreach ($_POST['digest_story'] as $sid) {
            $sid = trim((string) $sid);
            if ($sid !== '') {
                $selected[] = $sid;
            }
        }
        foreach (array_values(array_unique($selected)) as $legacy_sid) {
            if (strpos($legacy_sid, ':') === false) {
                $selected[] = 'stories:' . $legacy_sid;
            }
        }
        $selected = array_values(array_unique($selected));
    }

    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $since);
    if (!$dt || $dt->format('Y-m-d H:i:s') !== $since) {
        $display .= COM_showMessageText($LANG_HELLO01['digest_invalid_since'], 'error');
        $since = $last_sent;
    }

    $options = array(
        'since' => $since,
        'subject' => $subject,
        'intro' => $intro,
        'sources' => $sources,
        'selected_items' => $selected,
    );

    if ((isset($_POST['sendit']) || isset($_POST['testit'])) && SEC_checkToken()) {
        if (empty($selected)) {
            $display .= COM_showMessageText($LANG_HELLO01['digest_select_one'], 'warning');
        } elseif (count($selected) > $digest_item_limit) {
            $display .= COM_showMessageText(
                sprintf($LANG_HELLO01['digest_limit_exceeded'], $digest_item_limit),
                'warning'
            );
        } elseif (isset($_POST['testit'])) {
            $display .= '<p style="color:green; font-weight:bold;">' . $LANG_HELLO01['test_sent'] . '</p>' . LB;
            $display .= HELLO_emailUserTopics(true, true, $options);
        } else {
            $display .= HELLO_emailUserTopics(true, false, $options);
            return $display;
        }
    }

    $stories = HELLO_getDigestCandidates($sources, $since, (int) $_USER['uid']);

    $display .= '<p>' . $LANG_HELLO01['digest_intro'] . '</p>';
    $display .= '<p><strong>' . $LANG_HELLO01['digest_last_sent'] . '</strong> '
        . ($last_sent !== '' ? htmlspecialchars($last_sent, ENT_QUOTES, 'UTF-8') : $LANG_HELLO01['never'])
        . '</p>';

    $digest_action = COM_getCurrentURL();
    $display .= '<form action="' . htmlspecialchars($digest_action, ENT_QUOTES, 'UTF-8') . '" method="post">';
    $display .= '<div style="display:grid; grid-template-columns:180px minmax(240px,1fr); gap:12px; max-width:900px; align-items:start;">';

    $display .= '<label for="digest_since"><strong>' . $LANG_HELLO01['digest_since_label'] . '</strong></label>';
    $display .= '<input id="digest_since" type="text" name="digest_since" value="'
        . htmlspecialchars($since, ENT_QUOTES, 'UTF-8') . '" placeholder="YYYY-MM-DD HH:MM:SS" />';

    $display .= '<label for="digest_subject"><strong>' . $LANG_HELLO01['digest_subject_label'] . '</strong></label>';
    $display .= '<input id="digest_subject" type="text" name="digest_subject" value="'
        . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '" maxlength="100" />';

    $display .= '<label for="digest_intro_text"><strong>' . $LANG_HELLO01['digest_intro_label'] . '</strong></label>';
    $display .= '<textarea id="digest_intro_text" name="digest_intro_text" rows="5">'
        . htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') . '</textarea>';

    $display .= '</div>';

    $display .= '<h4 style="margin-top:22px;">' . $LANG_HELLO01['digest_sources_label'] . '</h4>';
    $available_sources = HELLO_getAvailableDigestSources();
    foreach ($available_sources as $source_id => $source_label) {
        $label = $source_label;
        if ($source_id === 'stories') {
            $label = $LANG_HELLO01['digest_source_stories'];
        } elseif ($source_id === 'staticpages') {
            $label = $LANG_HELLO01['digest_source_staticpages'];
        }
        $display .= '<label style="margin-right:18px;"><input type="checkbox" name="digest_source[]" value="'
            . htmlspecialchars($source_id, ENT_QUOTES, 'UTF-8') . '"'
            . (in_array($source_id, $sources, true) ? ' checked' : '') . ' /> '
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>';
    }

    $display .= '<p style="margin:16px 0 6px 0;">';
    $display .= '<input type="submit" name="refreshdigest" value="'
        . $LANG_HELLO01['digest_refresh_button'] . '" />';
    $display .= '</p>';

    $display .= '<h4 style="margin-top:22px;">' . $LANG_HELLO01['digest_articles_label'] . '</h4>';
    $display .= '<p style="margin:0 0 6px 0; color:#666;">' . $LANG_HELLO01['digest_candidate_filter'] . '</p>';
    $display .= '<p style="margin:0 0 10px 0; color:#666;">'
        . sprintf($LANG_HELLO01['digest_limit_hint'], $digest_item_limit)
        . ' <strong><span id="digest-selected-count">0</span> / '
        . (int) $digest_item_limit . '</strong></p>';
    if (empty($stories)) {
        $display .= '<p>' . $LANG_HELLO01['no_stories'] . '</p>';
        $display .= '<p style="color:#666;">' . $LANG_HELLO01['digest_refresh_hint'] . '</p>';
    } else {
        $display .= '<div style="max-width:900px; border:1px solid #ddd; padding:10px 14px;">';
        $auto_selected = 0;
        foreach ($stories as $story) {
            $item_key = (string) $story['source'] . ':' . (string) $story['id'];
            $checked = '';
            if (empty($_POST)) {
                if ($auto_selected < $digest_item_limit) {
                    $checked = ' checked';
                    $auto_selected++;
                }
            } elseif (in_array($item_key, $selected, true)) {
                $checked = ' checked';
            }
            $display .= '<label style="display:block; padding:7px 0; border-bottom:1px solid #eee;">';
            $display .= '<input class="hello-digest-item" type="checkbox" name="digest_item[]" value="'
                . htmlspecialchars($item_key, ENT_QUOTES, 'UTF-8') . '"' . $checked . ' /> ';
            $display .= '<strong>' . htmlspecialchars($story['title'], ENT_QUOTES, 'UTF-8') . '</strong>';
            $display .= ' <small>(' . htmlspecialchars($story['source_label'], ENT_QUOTES, 'UTF-8')
                . ' — ' . htmlspecialchars($story['date'], ENT_QUOTES, 'UTF-8') . ')</small>';
            $display .= '</label>';
        }
        $display .= '</div>';

        $display .= '<p style="margin-top:14px;">';
        $display .= '<input type="submit" name="previewit" value="' . $LANG_HELLO01['digest_preview_button'] . '" /> ';
        $display .= '<input type="submit" name="testit" value="' . $LANG_HELLO01['btn_test'] . '" /> ';
        $display .= '<input type="submit" name="sendit" value="' . $LANG_HELLO01['digest_queue_button'] . '" />';
        $display .= '</p>';
    }

    $display .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . SEC_createToken() . '" />';
    $display .= '</form>';

    $display .= '<script>(function(){'
        . 'var limit=' . (int) $digest_item_limit . ';'
        . 'var boxes=document.querySelectorAll(".hello-digest-item");'
        . 'var counter=document.getElementById("digest-selected-count");'
        . 'function update(){'
        . 'var count=0; for(var i=0;i<boxes.length;i++){if(boxes[i].checked){count++;}}'
        . 'if(counter){counter.textContent=count;}'
        . 'for(var j=0;j<boxes.length;j++){boxes[j].disabled=!boxes[j].checked&&count>=limit;}'
        . '}'
        . 'for(var k=0;k<boxes.length;k++){boxes[k].addEventListener("change",update);}'
        . 'update();'
        . '})();</script>';

    if (isset($_POST['previewit']) && SEC_checkToken() && !empty($selected)
        && count($selected) <= $digest_item_limit) {
        $display .= '<div style="max-width:900px; margin-top:20px; padding:18px; border:1px solid #bbb; background:#fafafa;">';
        $display .= '<h4>' . $LANG_HELLO01['digest_preview_title'] . '</h4>';
        $display .= '<p><strong>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</strong></p>';
        if ($intro !== '') {
            $display .= '<p>' . nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        $display .= '<ul>';
        foreach ($stories as $story) {
            if (in_array((string) $story['source'] . ':' . (string) $story['id'], $selected, true)) {
                $display .= '<li style="margin-bottom:12px;">';
                if (!empty($story['image'])) {
                    $display .= '<img src="' . htmlspecialchars($story['image'], ENT_QUOTES, 'UTF-8')
                        . '" alt="" style="max-width:140px; height:auto; display:block; margin:0 0 6px 0;" />';
                }
                $display .= '<strong>' . htmlspecialchars($story['title'], ENT_QUOTES, 'UTF-8') . '</strong>';
                if (!empty($story['source_label'])) {
                    $display .= ' <small>(' . htmlspecialchars($story['source_label'], ENT_QUOTES, 'UTF-8') . ')</small>';
                }
                $display .= '</li>';
            }
        }
        $display .= '</ul></div>';
    }

    return $display;
}

function HELLO_getGlobalStats() {
    global $_TABLES, $LANG_HELLO01;
    
    $total_campaigns = (int) DB_getItem($_TABLES['hello'], 'COUNT(*)');
    $total_sent = (int) DB_getItem($_TABLES['hello_stats'], 'COUNT(*)', "sent = 1");
    $total_opens = (int) DB_getItem($_TABLES['hello_stats'], 'COUNT(*)', "opened >= 1");
    
    // Pour les clics, on compte le nombre d'utilisateurs uniques ayant cliquÃ©
    $result_clicks = DB_query("SELECT COUNT(DISTINCT uid) AS unique_clicks FROM {$_TABLES['hello_urls_clicked']}");
    $row_clicks = DB_fetchArray($result_clicks);
    $total_clicks = (int) $row_clicks['unique_clicks'];
    
    $open_rate = ($total_sent > 0) ? round(($total_opens / $total_sent) * 100) : 0;
    if ($open_rate > 100) $open_rate = 100; // Cap at 100% just in case
    
    $click_rate = ($total_sent > 0) ? round(($total_clicks / $total_sent) * 100) : 0;
    if ($click_rate > 100) $click_rate = 100; // Cap at 100% just in case
    
    // Membres
    $total_members = (int) DB_getItem($_TABLES['users'], 'COUNT(*)', "uid > 1 AND status = 3");
    
    $emailfromadmin_field = isset($_TABLES['user_attributes']) ? 'ua.emailfromadmin' : 'up.emailfromadmin';
    $prefs_table = isset($_TABLES['user_attributes']) ? $_TABLES['user_attributes'] . ' ua' : $_TABLES['userprefs'] . ' up';
    $prefs_join = isset($_TABLES['user_attributes']) ? 'u.uid = ua.uid' : 'u.uid = up.uid';
    
    $result_subscribed = DB_query("SELECT COUNT(*) AS count FROM {$_TABLES['users']} u LEFT JOIN $prefs_table ON $prefs_join WHERE u.uid > 1 AND u.status = 3 AND u.email != '' AND $emailfromadmin_field = 1");
    $row_subscribed = DB_fetchArray($result_subscribed);
    $total_subscribed = (int) $row_subscribed['count'];
    
    $subscribed_rate = ($total_members > 0) ? round(($total_subscribed / $total_members) * 100) : 0;
    
    // Top 5 Domains
    $result_domains = DB_query("SELECT SUBSTRING_INDEX(u.email, '@', -1) AS domain, COUNT(*) AS count FROM {$_TABLES['users']} u LEFT JOIN $prefs_table ON $prefs_join WHERE u.uid > 1 AND u.status = 3 AND u.email != '' AND $emailfromadmin_field = 1 GROUP BY domain ORDER BY count DESC LIMIT 5");
    $top_domains_html = '<div style="text-align:left; font-size:14px; margin-top:10px;">';
    while ($row_domain = DB_fetchArray($result_domains)) {
        $dom_count = (int)$row_domain['count'];
        $dom_pct = ($total_subscribed > 0) ? round(($dom_count / $total_subscribed) * 100) : 0;
        $top_domains_html .= '<div style="margin-bottom:4px;"><strong>' . htmlspecialchars($row_domain['domain']) . '</strong> ' . $dom_count . ' <span style="color:#7f8c8d; font-size:12px;">(' . $dom_pct . '%)</span></div>';
    }
    $top_domains_html .= '</div>';
    
    $html = '<div style="display:flex; flex-wrap:wrap; justify-content:space-around; background:#f9f9f9; border:1px solid #e3e3e3; padding:20px; border-radius:8px; margin-bottom:25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
    
    // Row 1: Campaigns and Engagement
    $html .= '<div style="width:100%; display:flex; justify-content:space-around; margin-bottom:20px;">';
    
    $html .= '<div style="text-align:center; padding:10px;">';
    $html .= '<h2 style="margin:0 0 5px 0; font-size:28px; color:#2c3e50;">' . $total_campaigns . '</h2>';
    $html .= '<span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_campaigns'] . '</span>';
    $html .= '</div>';

    $html .= '<div style="text-align:center; padding:10px;">';
    $html .= '<h2 style="margin:0 0 5px 0; font-size:28px; color:#2980b9;">' . number_format($total_sent, 0, ',', ' ') . '</h2>';
    $html .= '<span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_sent'] . '</span>';
    $html .= '</div>';
    
    $html .= '<div style="text-align:center; padding:10px;">';
    $html .= '<h2 style="margin:0 0 5px 0; font-size:28px; color:#27ae60;">' . $open_rate . '%</h2>';
    $html .= '<span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_open_rate'] . '</span>';
    $html .= '</div>';
    
    $html .= '<div style="text-align:center; padding:10px;">';
    $html .= '<h2 style="margin:0 0 5px 0; font-size:28px; color:#8e44ad;">' . $click_rate . '%</h2>';
    $html .= '<span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_click_rate'] . '</span>';
    $html .= '</div>';
    
    $html .= '</div>'; // End Row 1
    // Row 2: Queue Progress & Top Readers
    // Queue Status
    $queue_remaining = (int) DB_getItem($_TABLES['hello_queue'], 'COUNT(*)');
    $queue_html = '';
    
    if ($queue_remaining == 0) {
        $queue_html = '<div style="margin-top:10px; padding:10px; background:#e8f8f5; border-left:4px solid #1abc9c; color:#16a085; font-size:14px; font-weight:bold;">' . $LANG_HELLO01['queue_empty'] . '</div>';
    } else {
        $result_q = DB_query("SELECT SUM(h.quantity) AS total_quantity FROM {$_TABLES['hello']} h WHERE h.hello_id IN (SELECT DISTINCT hello_id FROM {$_TABLES['hello_queue']})");
        $row_q = DB_fetchArray($result_q);
        $total_planned = (int) $row_q['total_quantity'];
        
        if ($total_planned < $queue_remaining) $total_planned = $queue_remaining; 
        
        $total_sent_in_queue = $total_planned - $queue_remaining;
        $queue_pct = ($total_planned > 0) ? round(($total_sent_in_queue / $total_planned) * 100) : 0;
        
        $queue_html = '<div style="margin-top:10px;">';
        $queue_html .= '<div style="display:flex; justify-content:space-between; font-size:13px; color:#34495e; margin-bottom:5px;"><span><strong>' . $queue_remaining . '</strong> ' . $LANG_HELLO01['queue_remaining'] . '</span><span>' . $queue_pct . $LANG_HELLO01['queue_sent_pct'] . '</span></div>';
        $queue_html .= '<div style="width:100%; background-color:#e0e0e0; border-radius:10px; overflow:hidden; height:14px;">';
        $queue_html .= '<div style="width:' . $queue_pct . '%; background-color:#3498db; height:100%; transition:width 0.5s;"></div>';
        $queue_html .= '</div>';
        $queue_html .= '</div>';
    }
    
    // Top 5 Readers
    $result_readers = DB_query("SELECT u.username, SUM(s.opened) as score FROM {$_TABLES['hello_stats']} s INNER JOIN {$_TABLES['users']} u ON s.uid = u.uid WHERE s.opened > 0 GROUP BY s.uid ORDER BY score DESC LIMIT 5");
    $top_readers_html = '<div style="text-align:left; font-size:14px; margin-top:10px;">';
    while ($row_reader = DB_fetchArray($result_readers)) {
        $top_readers_html .= '<div style="margin-bottom:4px;"><strong>' . htmlspecialchars($row_reader['username']) . '</strong> <span style="color:#7f8c8d; font-size:12px;">(' . (int)$row_reader['score'] . ' ' . $LANG_HELLO01['top_opens'] . ')</span></div>';
    }
    if (DB_numRows($result_readers) == 0) {
        $top_readers_html .= '<div style="color:#7f8c8d; font-size:13px;">' . $LANG_HELLO01['top_no_data'] . '</div>';
    }
    $top_readers_html .= '</div>';
    
    $html .= '<div style="width:100%; height:1px; background:#e3e3e3; margin:10px 0;"></div>';
    $html .= '<div style="width:100%; display:flex; justify-content:space-between; align-items: flex-start; margin-top:20px;">';
    
    $html .= '<div style="width:50%; padding:10px; padding-right:20px;">';
    $html .= '<h3 style="margin:0 0 10px 0; font-size:15px; color:#34495e; text-transform:uppercase;">' . $LANG_HELLO01['queue_status'] . '</h3>';
    $html .= $queue_html;
    $html .= '</div>';
    
    $html .= '<div style="width:40%; padding:10px; border-left:1px solid #e3e3e3; padding-left:20px;">';
    $html .= '<h3 style="margin:0 0 5px 0; font-size:15px; color:#34495e; text-transform:uppercase;">' . $LANG_HELLO01['top_readers'] . '</h3>';
    $html .= $top_readers_html;
    $html .= '</div>';
    
    $html .= '</div>'; // End Row 2
    
    // Row 3: Members
    $html .= '<div style="width:100%; height:1px; background:#e3e3e3; margin:10px 0;"></div>';
    $html .= '<div style="width:100%; display:flex; justify-content:space-around; align-items: flex-start; margin-top:20px;">';
    
    $html .= '<div style="text-align:center; padding:10px;">';
    $html .= '<h2 style="margin:0 0 5px 0; font-size:28px; color:#34495e;">' . number_format($total_members, 0, ',', ' ') . '</h2>';
    $html .= '<span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_members'] . '</span>';
    $html .= '</div>';
    
    $html .= '<div style="text-align:center; padding:10px;">';
    $html .= '<h2 style="margin:0 0 5px 0; font-size:28px; color:#e67e22;">' . number_format($total_subscribed, 0, ',', ' ') . ' <span style="font-size:16px;">(' . $subscribed_rate . '%)</span></h2>';
    $html .= '<span style="font-size:13px; color:#7f8c8d; text-transform:uppercase; font-weight:bold;">' . $LANG_HELLO01['stat_subscribed'] . '</span>';
    $html .= '</div>';
    
    $html .= '<div style="text-align:center; padding:10px;">';
    $html .= '<h3 style="margin:0 0 5px 0; font-size:15px; color:#34495e; text-transform:uppercase;">' . $LANG_HELLO01['top_domains'] . '</h3>';
    $html .= $top_domains_html;
    $html .= '</div>';
    
    $html .= '</div>'; // End Row 3
    
    $html .= '</div>';
    
    return $html;
}

// MAIN

$display .= hello_admin_menu($LANG_HELLO01['block_headline'], $LANG_HELLO01['inst_index']);

$display .= HELLO_getGlobalStats();
if (SEC_hasRights ('user.mail')) {
    $display .= '<h3>' . $LANG_HELLO01['mdigest'] . '</h3>';
    $display .= HELLO_send_digest();
}

if (SEC_hasRights ('user.edit')) {
    $display .= '<h3>' . $LANG_HELLO01['block_headline'] . '</h3>';
    $display .= HELLO_search_form();
}

$display .= display_documentation();
$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
$display = COM_createHTMLDocument($display);
COM_output($display);

?>