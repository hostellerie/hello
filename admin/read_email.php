<?php

/**
* @package hello
*/
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | read_mail.php                                                             |
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

    COM_accessLog ("User {$_USER['username']} tried to illegally access the hello administration screen.");
    
    $display = COM_createHTMLDocument($display);
    COM_output($display);
    exit;
}

/**
* Shows the form the admin uses to send Geeklog members a message. Right now
* you can only email an entire group.
*
* @return   string      HTML for the email form
*
*/
function list_hello ()
{
    global $_CONF, $_TABLES, $LANG_HELLO01, $LANG28;

    require_once $_CONF['path_system'] . 'lib-admin.php';

    $retval = '';

    //Build header list
    $header_arr = array(      # display 'text' and use table field 'field'
                    array('text' => $LANG_HELLO01['see_email'], 'field' => 'see_hello', 'sort' => false),
                    array('text' => $LANG_HELLO01['id'], 'field' => 'hello_id', 'sort' => true),
                    array('text' => $LANG_HELLO01['subjet'], 'field' => 'subject', 'sort' => true),
                    array('text' => $LANG_HELLO01['creation'], 'field' => 'creation', 'sort' => true),
                    array('text' => $LANG_HELLO01['group'], 'field' => 'email_group', 'sort' => true),
                    array('text' => $LANG_HELLO01['quantity'], 'field' => 'quantity', 'sort' => true),
                    array('text' => $LANG_HELLO01['status_actions'], 'field' => 'status_actions', 'sort' => false)
    );


    $defsort_arr = array('field'     => $_TABLES['hello'] . '.hello_id',
                         'direction' => 'DESC');

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/hello/read_email.php',
        'help_url'   => ''
    );


    // Ensure status column exists (backward compatibility)
    $check_status = DB_query("SHOW COLUMNS FROM {$_TABLES['hello']} LIKE 'status'");
    if (DB_numRows($check_status) == 0) {
        DB_query("ALTER TABLE {$_TABLES['hello']} ADD COLUMN status tinyint(1) NOT NULL DEFAULT 0");
    }

    $sql = "SELECT {$_TABLES['hello']}.hello_id,subject,creation,email_group,quantity,status "
         . "FROM {$_TABLES['hello']} WHERE 1=1";

    $query_arr = array('table' => 'hello',
                       'sql' => $sql,
                       'query_fields' => array('hello_id', 'subject', 'creation', 'email_group', 'quantity', 'status'),
                       'default_filter' => "");

    $retval .= ADMIN_list('hello', 'HELLO_getListField_hello', $header_arr,
                          $text_arr, $query_arr, $defsort_arr);

    return $retval;
}

function HELLO_getListField_hello ($fieldname, $fieldvalue, $A, $icon_arr) {

    global $_CONF, $_TABLES;
	
	switch ($fieldname) {
        case 'see_hello':
            $retval = '';
	        $retval .= COM_createLink($icon_arr['list'], "{$_CONF['site_admin_url']}/plugins/hello/read_email.php?mode=view&amp;hello_id={$A['hello_id']}");
		    break;
		case 'creation':
		    $creation = COM_getUserDateTimeFormat(strtotime($A['creation']));
			$retval = $creation[0];
		    break;
		case 'status_actions':
            global $LANG_HELLO01;
            // Count remaining emails in queue for this campaign
            $queue_count = (int) DB_getItem($_TABLES['hello_queue'], 'COUNT(*)', "hello_id = {$A['hello_id']}");
            if ($queue_count > 0) {
                // Campaign is in progress
                $status = isset($A['status']) ? (int)$A['status'] : 0;
                
                $token = SEC_createToken();
                $form_start = '<form action="' . $_CONF['site_admin_url'] . '/plugins/hello/read_email.php" method="post" style="display:inline;">'
                    . '<input type="hidden" name="hello_id" value="' . (int) $A['hello_id'] . '">'
                    . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '">';

                if ($status == 1) {
                    $retval = '<span style="color:orange; font-weight:bold;">' . $LANG_HELLO01['status_paused'] . '</span> (' . $queue_count . ' ' . $LANG_HELLO01['emails_remaining'] . ')<br>';
                    $retval .= $form_start . '<input type="hidden" name="mode" value="resume">'
                        . '<button type="submit" style="color:green; border:0; background:none; padding:0; cursor:pointer;">&#9654; ' . $LANG_HELLO01['action_resume'] . '</button></form> | ';
                } else {
                    $retval = '<span style="color:blue; font-weight:bold;">' . $LANG_HELLO01['status_active'] . '</span> (' . $queue_count . ' ' . $LANG_HELLO01['emails_remaining'] . ')<br>';
                    $retval .= $form_start . '<input type="hidden" name="mode" value="pause">'
                        . '<button type="submit" style="color:orange; border:0; background:none; padding:0; cursor:pointer;">&#9208; ' . $LANG_HELLO01['action_pause'] . '</button></form> | ';
                }
                $retval .= $form_start . '<input type="hidden" name="mode" value="stop">'
                    . '<button type="submit" onclick="return confirm(\'' . addslashes($LANG_HELLO01['action_stop_confirm']) . '\');" style="color:red; border:0; background:none; padding:0; cursor:pointer;">&#9209; ' . $LANG_HELLO01['action_stop'] . '</button></form>';
            } else {
                $retval = '<span style="color:green; font-weight:bold;">' . $LANG_HELLO01['status_finished'] . '</span>';
            }
            break;
	    default:
            $retval = stripslashes($fieldvalue);
            break;
    }
	
	return $retval;
}

function display_hello($hello_id) {

    global $_CONF, $_TABLES, $LANG_HELLO01;

	$hello_id = (int) $hello_id;
	$requete ="SELECT content, subject, quantity FROM {$_TABLES['hello']} WHERE hello_id = " . $hello_id . " limit 1";
	$result_objet_cherche = DB_query($requete);
    $objet_cherche = DB_fetchArray($result_objet_cherche);
	
    $content = $objet_cherche ? (string)$objet_cherche['content'] : '';
    $subject = $objet_cherche ? (string)$objet_cherche['subject'] : '';
    $quantity = $objet_cherche ? (int)$objet_cherche['quantity'] : 0;
    
    $display = '<h3>' . $LANG_HELLO01['email'] . ' #' . $hello_id . ' : ' . $subject . '</h3>';

    // generate the display from the template
    $display_hello = new Template($_CONF['path'] . 'plugins/hello/templates/admin');
    $display_hello->set_file(array('display_hello' => 'hello_display.thtml'));
    
    // Get Statistics
    $stats_query = "SELECT SUM(sent) as total_sent, SUM(opened) as total_opened, SUM(unsubscribed) as total_unsub FROM {$_TABLES['hello_stats']} WHERE hello_id = $hello_id";
    $stats_result = DB_query($stats_query);
    $stats = DB_fetchArray($stats_result);
    $sent = (int) $stats['total_sent'];
    $opens = (int) $stats['total_opened'];
    $unsubs = (int) $stats['total_unsub'];
    
    // Get unique clickers
    $unique_clicks_query = DB_query("SELECT COUNT(DISTINCT uid) as unique_clickers FROM {$_TABLES['hello_urls_clicked']} WHERE hello_id = $hello_id");
    $unique_clicks_res = DB_fetchArray($unique_clicks_query);
    $unique_clickers = (int) $unique_clicks_res['unique_clickers'];
    
    // Calculate Rates
    $open_rate = ($sent > 0) ? round(($opens / $sent) * 100, 1) : 0;
    $unsub_rate = ($sent > 0) ? round(($unsubs / $sent) * 100, 1) : 0;
    $click_rate = ($sent > 0) ? round(($unique_clickers / $sent) * 100, 1) : 0;
    
    // Get click details grouped by URL
    $clicks_query = "SELECT url, COUNT(DISTINCT uid) as clicks_count FROM {$_TABLES['hello_urls_clicked']} WHERE hello_id = $hello_id GROUP BY url ORDER BY clicks_count DESC";
    $clicks_result = DB_query($clicks_query);
    
    $clicks_html = '';
    if (DB_numRows($clicks_result) > 0) {
        $clicks_html = '<ul style="margin-top: 5px; font-size: 12px; max-height: 150px; overflow-y: auto;">';
        while ($click = DB_fetchArray($clicks_result)) {
            $clicks_html .= '<li><strong>' . $click['clicks_count'] . '</strong> ' . $LANG_HELLO01['crm_unique_clicks'] . ' <em><a href="' . $click['url'] . '" target="_blank">' . htmlspecialchars($click['url']) . '</a></em></li>';
        }
        $clicks_html .= '</ul>';
    } else {
        $clicks_html = '<br/><em>' . $LANG_HELLO01['crm_no_clicks'] . '</em>';
    }
    
    $stats_html = '<div style="background:#f9f9f9; padding: 10px; margin-bottom: 20px; border:1px solid #ddd;">';
    
    // Check if it's a legacy campaign (no stats recorded but emails were sent)
    if ($sent == 0 && $quantity > 0) {
        $stats_html .= '<h4 style="color:#d32f2f;">' . $LANG_HELLO01['crm_old_campaign'] . '</h4>';
        $stats_html .= '<p><em>' . sprintf($LANG_HELLO01['crm_old_campaign_desc'], $quantity) . '</em></p>';
    } else {
        $stats_html .= '<strong>' . $LANG_HELLO01['crm_campaign_stats'] . '</strong><br/>';
        $stats_html .= $LANG_HELLO01['crm_sent'] . ': <strong>' . $sent . '</strong> ' . $LANG_HELLO01['crm_subscribers'] . '<br/>';
        $stats_html .= $LANG_HELLO01['crm_unique_opens'] . ': <strong>' . $opens . '</strong> (' . $open_rate . ' %)<br/>';
        $stats_html .= $LANG_HELLO01['crm_unique_clicks'] . ': <strong>' . $unique_clickers . '</strong> (' . $click_rate . ' %)<br/>';
        $stats_html .= $LANG_HELLO01['crm_unsubs'] . ': <strong>' . $unsubs . '</strong> (' . $unsub_rate . ' %)<br/><br/>';
        $stats_html .= '<strong>' . $LANG_HELLO01['crm_click_details'] . '</strong>';
        $stats_html .= $clicks_html;
        $stats_html .= '<br/><a class="uk-button uk-button-primary" style="padding: 5px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; display:inline-block; margin-bottom:5px;" href="' . $_CONF['site_admin_url'] . '/plugins/hello/read_email.php?mode=campaign_clicks&amp;hello_id=' . $hello_id . '">' . $LANG_HELLO01['crm_view_table'] . '</a>';
        $stats_html .= '<br/><a class="uk-button uk-button-primary" style="padding: 5px 10px; background: #28a745; color: white; text-decoration: none; border-radius: 3px; display:inline-block;" href="' . $_CONF['site_admin_url'] . '/plugins/hello/read_email.php?mode=campaign_opens&amp;hello_id=' . $hello_id . '">' . $LANG_HELLO01['crm_view_opens'] . '</a>';
    }
    $stats_html .= '</div>';
    
    $display_hello->set_var('hello_display', $stats_html . $content);
	
    $display .= $display_hello->parse('output', 'display_hello');


    // return results
    return $display;
}

function display_campaign_clicks($hello_id) {
    global $_CONF, $_TABLES, $LANG_HELLO01;
    require_once $_CONF['path_system'] . 'lib-admin.php';
    $hello_id = (int) $hello_id;
    
    $header_arr = array(
        array('text' => $LANG_HELLO01['crm_user'], 'field' => 'username', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_email'], 'field' => 'email', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_clicked_url'], 'field' => 'url', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_click_date'], 'field' => 'click_time', 'sort' => true)
    );
    
    $defsort_arr = array('field' => 'click_time', 'direction' => 'DESC');
    
    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . "/plugins/hello/read_email.php?mode=campaign_clicks&amp;hello_id=$hello_id",
        'help_url'   => ''
    );
    
    $sql = "SELECT c.click_id, u.username, u.email, c.url, c.click_time 
            FROM {$_TABLES['hello_urls_clicked']} c 
            LEFT JOIN {$_TABLES['users']} u ON c.uid = u.uid 
            WHERE c.hello_id = $hello_id";
            
    $query_arr = array(
        'table' => 'hello_urls_clicked',
        'sql' => $sql,
        'query_fields' => array('u.username', 'u.email', 'c.url'),
        'default_filter' => ""
    );
    
    $display = '<p><a href="' . $_CONF['site_admin_url'] . '/plugins/hello/read_email.php?mode=view&amp;hello_id=' . $hello_id . '">&laquo; ' . $LANG_HELLO01['back_to_campaign'] . '</a></p>';
    $display .= ADMIN_list('hello_urls_clicked', 'HELLO_getListField_campaignclicks', $header_arr, $text_arr, $query_arr, $defsort_arr);
    
    return $display;
}

function HELLO_getListField_campaignclicks($fieldname, $fieldvalue, $A, $icon_arr) {
    $retval = '';
    switch ($fieldname) {
        case 'url':
            $retval = '<a href="' . $fieldvalue . '" target="_blank">' . htmlspecialchars(strlen($fieldvalue) > 60 ? substr($fieldvalue, 0, 60).'...' : $fieldvalue) . '</a>';
            break;
        default:
            $retval = stripslashes($fieldvalue);
            break;
    }
    return $retval;
}

function display_user_clicks($uid) {
    global $_CONF, $_TABLES, $LANG_HELLO01;
    require_once $_CONF['path_system'] . 'lib-admin.php';
    $uid = (int) $uid;
    
    // Get username
    $username = DB_getItem($_TABLES['users'], 'username', "uid=$uid");
    
    $header_arr = array(
        array('text' => $LANG_HELLO01['crm_campaign_id'], 'field' => 'hello_id', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_subject'], 'field' => 'subject', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_clicked_url'], 'field' => 'url', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_click_date'], 'field' => 'click_time', 'sort' => true)
    );
    
    $defsort_arr = array('field' => 'click_time', 'direction' => 'DESC');
    
    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . "/plugins/hello/read_email.php?mode=user_clicks&amp;uid=$uid",
        'help_url'   => ''
    );
    
    $sql = "SELECT c.click_id, h.hello_id, h.subject, c.url, c.click_time 
            FROM {$_TABLES['hello_urls_clicked']} c 
            LEFT JOIN {$_TABLES['hello']} h ON c.hello_id = h.hello_id 
            WHERE c.uid = $uid";
            
    $query_arr = array(
        'table' => 'hello_urls_clicked',
        'sql' => $sql,
        'query_fields' => array('h.subject', 'c.url'),
        'default_filter' => ""
    );
    
    $retval = '';
    $retval .= '<p><a href="' . $_CONF['site_admin_url'] . '/plugins/hello/subscribers.php">&laquo; ' . $LANG_HELLO01['back_to_list'] . '</a></p>';
    $retval .= ADMIN_list('hello_urls_clicked', 'HELLO_getListField_userclicks', $header_arr, $text_arr, $query_arr, $defsort_arr);
    
    return $retval;
}

function HELLO_getListField_userclicks($fieldname, $fieldvalue, $A, $icon_arr) {
    global $_CONF;
    $retval = '';
    switch ($fieldname) {
        case 'url':
            $retval = '<a href="' . $fieldvalue . '" target="_blank">' . htmlspecialchars(strlen($fieldvalue) > 60 ? substr($fieldvalue, 0, 60).'...' : $fieldvalue) . '</a>';
            break;
        case 'hello_id':
            $retval = '<a href="' . $_CONF['site_admin_url'] . '/plugins/hello/read_email.php?mode=view&amp;hello_id=' . $fieldvalue . '">' . $fieldvalue . '</a>';
            break;
        default:
            $retval = stripslashes($fieldvalue);
            break;
    }
    return $retval;
}

function display_campaign_opens($hello_id) {
    global $_CONF, $_TABLES, $LANG_HELLO01;
    require_once $_CONF['path_system'] . 'lib-admin.php';
    $hello_id = (int) $hello_id;
    
    $header_arr = array(
        array('text' => $LANG_HELLO01['crm_user'], 'field' => 'username', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_email'], 'field' => 'email', 'sort' => true)
    );
    
    $defsort_arr = array('field' => 'username', 'direction' => 'ASC');
    
    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . "/plugins/hello/read_email.php?mode=campaign_opens&amp;hello_id=$hello_id",
        'help_url'   => ''
    );
    
    $sql = "SELECT hs.stat_id, u.username, u.email 
            FROM {$_TABLES['hello_stats']} hs 
            LEFT JOIN {$_TABLES['users']} u ON hs.uid = u.uid 
            WHERE hs.hello_id = $hello_id AND hs.opened >= 1";
            
    $query_arr = array(
        'table' => 'hello_stats',
        'sql' => $sql,
        'query_fields' => array('u.username', 'u.email'),
        'default_filter' => ""
    );

    $retval = '';
    $retval .= '<p><a href="' . $_CONF['site_admin_url'] . '/plugins/hello/read_email.php?mode=view&amp;hello_id=' . $hello_id . '">&laquo; ' . $LANG_HELLO01['back_to_campaign_stats'] . '</a></p>';
    $retval .= ADMIN_list('hello_stats', 'HELLO_getListField_campaign_opens', $header_arr, $text_arr, $query_arr, $defsort_arr);
    return $retval;
}

function HELLO_getListField_campaign_opens($fieldname, $fieldvalue, $A, $icon_arr) {
    switch ($fieldname) {
        default:
            return stripslashes($fieldvalue);
    }
}

function display_user_emails($uid, $only_opened = false) {
    global $_CONF, $_TABLES, $LANG_HELLO01;
    require_once $_CONF['path_system'] . 'lib-admin.php';
    $uid = (int) $uid;
    
    $header_arr = array(
        array('text' => $LANG_HELLO01['crm_campaign'], 'field' => 'hello_id', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_subject'], 'field' => 'subject', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_send_date'], 'field' => 'creation', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_opened'], 'field' => 'opened', 'sort' => true),
        array('text' => $LANG_HELLO01['crm_click'], 'field' => 'clicked', 'sort' => true)
    );
    
    $defsort_arr = array('field' => 'creation', 'direction' => 'DESC');
    
    $mode_str = $only_opened ? 'user_opens' : 'user_emails';
    
    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $_CONF['site_admin_url'] . "/plugins/hello/read_email.php?mode=$mode_str&amp;uid=$uid",
        'help_url'   => ''
    );
    
    $where = "hs.uid = $uid AND hs.sent = 1";
    if ($only_opened) {
        $where .= " AND hs.opened >= 1";
    }
    
    $sql = "SELECT hs.stat_id, h.hello_id, h.subject, h.creation, hs.opened, 
                   (SELECT COUNT(*) FROM {$_TABLES['hello_urls_clicked']} c WHERE c.uid = hs.uid AND c.hello_id = h.hello_id) as clicked
            FROM {$_TABLES['hello_stats']} hs 
            JOIN {$_TABLES['hello']} h ON hs.hello_id = h.hello_id 
            WHERE $where";
            
    $query_arr = array(
        'table' => 'hello_stats',
        'sql' => $sql,
        'query_fields' => array('h.subject'),
        'default_filter' => ""
    );
    
    $retval = '';
    $retval .= '<p><a href="' . $_CONF['site_admin_url'] . '/plugins/hello/subscribers.php">&laquo; ' . $LANG_HELLO01['back_to_subscribers'] . '</a></p>';
    $retval .= ADMIN_list('hello_stats', 'HELLO_getListField_user_emails', $header_arr, $text_arr, $query_arr, $defsort_arr);
    return $retval;
}

function HELLO_getListField_user_emails($fieldname, $fieldvalue, $A, $icon_arr) {
    global $_CONF;
    switch ($fieldname) {
        case 'creation':
            $creation = COM_getUserDateTimeFormat(strtotime($fieldvalue));
            return $creation[0];
        case 'opened':
            return ((int)$fieldvalue >= 1) ? '<span style="color:green; font-weight:bold;">' . $LANG_HELLO01['crm_yes'] . '</span>' : '<span style="color:red;">' . $LANG_HELLO01['crm_no'] . '</span>';
        case 'clicked':
            return ((int)$fieldvalue > 0) ? '<span style="color:green; font-weight:bold;">' . (int)$fieldvalue . ' ' . $LANG_HELLO01['crm_clicks_count'] . '</span>' : '<span style="color:red;">' . $LANG_HELLO01['crm_no'] . '</span>';
        case 'hello_id':
            return '<a href="' . $_CONF['site_admin_url'] . '/plugins/hello/read_email.php?mode=view&amp;hello_id=' . $fieldvalue . '">' . $fieldvalue . '</a>';
        default:
            return stripslashes($fieldvalue);
    }
}

// MAIN

$mode = '';
if (isset($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
}

if ($mode == 'pause' || $mode == 'resume' || $mode == 'stop') {
    $is_post = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
    $hello_id = isset($_POST['hello_id']) ? (int) $_POST['hello_id'] : 0;

    if (!$is_post || !SEC_checkToken()) {
        $display .= hello_admin_menu($LANG_HELLO01['read_email'], $LANG_HELLO01['inst_read']);
        $display .= COM_showMessageText($MESSAGE[37], $MESSAGE[30]);
        $display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
        $display = COM_createHTMLDocument($display);
        COM_output($display);
        exit;
    }

    if ($hello_id > 0) {
        if ($mode == 'pause') {
            DB_query("UPDATE {$_TABLES['hello']} SET status = 1 WHERE hello_id = $hello_id");
        } else if ($mode == 'resume') {
            DB_query("UPDATE {$_TABLES['hello']} SET status = 0 WHERE hello_id = $hello_id");
        } else if ($mode == 'stop') {
            DB_query("DELETE FROM {$_TABLES['hello_queue']} WHERE hello_id = $hello_id");
            DB_query("UPDATE {$_TABLES['hello']} SET status = 0 WHERE hello_id = $hello_id");
        }
    }

    echo COM_refresh($_CONF['site_admin_url'] . '/plugins/hello/read_email.php');
    exit;
}

if ($mode == 'view' || $mode == 'edit') {
    $hello_id = (int) $_REQUEST['hello_id'];
    $display .= hello_admin_menu($LANG_HELLO01['read_email'], $LANG_HELLO01['inst_read']);
    $display .= display_hello($hello_id);
} else if ($mode == 'campaign_clicks') {
    $hello_id = (int) $_REQUEST['hello_id'];
    $display .= hello_admin_menu($LANG_HELLO01['crm_detail_campaign'] . $hello_id, $LANG_HELLO01['inst_read']);
    $display .= display_campaign_clicks($hello_id);
} else if ($mode == 'user_clicks') {
    $uid = (int) $_REQUEST['uid'];
    $username = DB_getItem($_TABLES['users'], 'username', "uid=$uid");
    $display .= hello_admin_menu($LANG_HELLO01['crm_user_history'] . htmlspecialchars($username), $LANG_HELLO01['inst_read']);
    $display .= display_user_clicks($uid);
} else if ($mode == 'campaign_opens') {
    $hello_id = (int) $_REQUEST['hello_id'];
    $display .= hello_admin_menu($LANG_HELLO01['crm_detail_opens'] . $hello_id, $LANG_HELLO01['inst_read']);
    $display .= display_campaign_opens($hello_id);
} else if ($mode == 'user_emails') {
    $uid = (int) $_REQUEST['uid'];
    $username = DB_getItem($_TABLES['users'], 'username', "uid=$uid");
    $display .= hello_admin_menu($LANG_HELLO01['emails_received_by'] . htmlspecialchars($username), $LANG_HELLO01['inst_read']);
    $display .= display_user_emails($uid, false);
} else if ($mode == 'user_opens') {
    $uid = (int) $_REQUEST['uid'];
    $username = DB_getItem($_TABLES['users'], 'username', "uid=$uid");
    $display .= hello_admin_menu($LANG_HELLO01['emails_opened_by'] . htmlspecialchars($username), $LANG_HELLO01['inst_read']);
    $display .= display_user_emails($uid, true);
} else {
    $display .= hello_admin_menu($LANG_HELLO01['read_email'], $LANG_HELLO01['inst_read']);
    $display .= list_hello();
}

$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
$display = COM_createHTMLDocument($display);
COM_output($display);

?>