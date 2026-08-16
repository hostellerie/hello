<?php

/**
* @package hello
*/
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | email_group.php                                                           |
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
function display_mailform ($vars=array())
{
    global $_CONF, $_SCRIPTS, $_TABLES, $_USER, $LANG31, $LANG24, $LANG_HELLO01;

    $retval = '';

    if ($_CONF['advanced_editor'] == 1) {
        $postmode = 'html';
        
        if (function_exists('COM_setupAdvancedEditor')) {
            COM_setupAdvancedEditor('');
            $js = "
jQuery(window).on('load', function() {
    if (typeof AdvancedEditor !== 'undefined' && typeof AdvancedEditor.newEditor !== 'undefined') {
        AdvancedEditor.newEditor({
            TextareaId: [ {plain:'message_html', advanced:'message_html'} ],
            toolbar: 1
        });
    }
});";
            $_SCRIPTS->setJavaScript($js, true, true);
        } else {
            $_SCRIPTS->setJavaScriptLibrary('jquery');
            $_SCRIPTS->setJavaScriptFile('ckeditor', $_CONF['site_url'] . '/editors/ckeditor/ckeditor.js');
            $ckeditor = '
            jQuery(function() {
                if (typeof CKEDITOR !== "undefined") {
                    CKEDITOR.replace( "message_html", {
                     customConfig: "' .  $_CONF['site_url'] . '/editors/ckeditor/config.js",
                     toolbar: "toolbar1",
                     height:500
                    });
                }
            });';
            $_SCRIPTS->setJavaScript($ckeditor , true);
        }
    } elseif (empty ($postmode)) {
        $postmode = $_CONF['postmode'];
    }
	
    $mail_templates = new Template ($_CONF['path'] . 'plugins/hello/templates/admin/');
    if (($_CONF['advanced_editor'] == 1)) {
        $mail_templates->set_file('form','mailform_advanced.thtml');
    } else {
        $mail_templates->set_file('form','mailform.thtml');
	}
    $mail_templates->set_var('geeklogStyleBasePath',$_CONF['site_url'] . '/fckeditor');
	
    if ($postmode == 'html') {
        $mail_templates->set_var ('show_texteditor', 'none');
        $mail_templates->set_var ('show_htmleditor', '');
    } else {
        $mail_templates->set_var ('show_texteditor', '');
        $mail_templates->set_var ('show_htmleditor', 'none');
    }
    $mail_templates->set_var('lang_postmode', $LANG24[4]);
    $mail_templates->set_var('postmode_options', COM_optionList($_TABLES['postmodes'],'code,name',$postmode));
    $mail_templates->set_var ('site_url', $_CONF['site_url']);
    $mail_templates->set_var ('site_admin_url', $_CONF['site_admin_url']);
    $mail_templates->set_var ('layout_url', $_CONF['layout_url']);
    $mail_templates->set_var ('startblock_email', '');
    $mail_templates->set_var ('php_self', $_CONF['site_admin_url'] . '/plugins/hello/email_group.php');
    $mail_templates->set_var ('lang_note', $LANG31[19]);
    $mail_templates->set_var ('lang_to', $LANG31[18]);
	

	
    $mail_templates->set_var ('lang_selectgroup', $LANG31[25]);
    $group_options = '';
    $result = DB_query("SELECT grp_id, grp_name FROM {$_TABLES['groups']} WHERE grp_name <> 'All Users'");
    $nrows = DB_numRows ($result);
    $groups = array ();
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray ($result);
        $groups[$A['grp_id']] = ucwords ($A['grp_name']);
    }
    asort ($groups);
    foreach ($groups as $groupID => $groupName) {
        $selected = (isset($vars['to_group']) && $vars['to_group'] == $groupID) ? ' selected="selected"' : '';
        $group_options .= '<option value="' . $groupID . '"' . $selected . '>' . $groupName
                       . '</option>';
    }
    $mail_templates->set_var ('group_options', $group_options);
    $mail_templates->set_var ('lang_from', $LANG31[2]);
    $mail_templates->set_var ('site_name', $_CONF['site_name']);
    $mail_templates->set_var ('lang_replyto', $LANG31[3]);
    $mail_templates->set_var ('site_mail', $_CONF['site_mail']);
    $mail_templates->set_var ('lang_subject', $LANG31[4]);
    $mail_templates->set_var ('lang_body', $LANG31[5]);
    $mail_templates->set_var ('lang_sendto', $LANG31[6]);
    $mail_templates->set_var ('lang_allusers', $LANG31[7]);
    $mail_templates->set_var ('lang_admin', $LANG31[8]);
    $mail_templates->set_var ('lang_options', $LANG31[9]);
    $mail_templates->set_var ('lang_HTML', $LANG31[10]);
    $mail_templates->set_var ('lang_urgent', $LANG31[11]);
    $mail_templates->set_var ('lang_ignoreusersettings', $LANG31[14]);
    $mail_templates->set_var ('lang_send', $LANG31[12]);
    $mail_templates->set_var ('end_block', '');
    $mail_templates->set_var ('xhtml', XHTML);
    $mail_templates->set_var('gltoken_name', CSRF_TOKEN);
    $mail_templates->set_var('gltoken', SEC_createToken());
    $mail_templates->set_var('subject', isset($vars['subject']) ? $vars['subject'] : '');
    $mail_templates->set_var('message_html', isset($vars['content']) ? $vars['content'] : '');
    
    //Date time selector
    $start_stamp = time ();
    
    $start_month = isset($vars['start_month']) ? $vars['start_month'] : date('m', $start_stamp);
    $start_day = isset($vars['start_day']) ? $vars['start_day'] : date('d', $start_stamp);
    $start_year = isset($vars['start_year']) ? $vars['start_year'] : date('Y', $start_stamp);

    $start_hour = isset($vars['start_hour']) ? $vars['start_hour'] : date ('H', $start_stamp);
    $start_minute = isset($vars['start_minute']) ? $vars['start_minute'] : intval (date ('i', $start_stamp) / 15) * 15;
    
    if ($start_hour >= 12) {
        $startampm = 'pm';
    } else {
        $startampm = 'am';
    }
    $start_hour_24 = $start_hour % 24;
    if ($start_hour > 12) {
        $start_hour = $start_hour - 12;
    } else if ($start_hour == 0) {
        $start_hour = 12;
    }
    
    $month_options = COM_getMonthFormOptions ($start_month);
    $mail_templates->set_var ('startmonth_options', $month_options);

    $day_options = COM_getDayFormOptions ($start_day);
    $mail_templates->set_var ('startday_options', $day_options);

    $year_options = COM_getYearFormOptions ($start_year);
    $mail_templates->set_var ('startyear_options', $year_options);

    $hour_options = COM_getHourFormOptions ($start_hour_24, 24);
    $mail_templates->set_var ('starthour_options', $hour_options);

    $mail_templates->set_var ('hour_mode', 24);

    $mail_templates->set_var ('startminute_options',
                               COM_getMinuteFormOptions ($start_minute, 15));

    $mail_templates->parse ('output', 'form');
    $retval = $mail_templates->finish ($mail_templates->get_var ('output'));

    return $retval;
}

/**
* This function record in the hello queue the message to send to the specified group or to csv list
*
* @param    array   $vars   Same as $_POST, holds all the email info
* @return   string          HTML with success or error message
*
*/
function send_messages($vars)
{
    global $_CONF, $_TABLES, $LANG31, $LANG_HELLO01, $_HE_CONF;

    require_once($_CONF['path_system'] . 'lib-user.php');

    $retval = '';

    if (empty ($vars['subject']) OR empty ($vars['content']) ) {
        $retval .= COM_startBlock ($LANG31[1], '',
                        COM_getBlockTemplate ('_msg_block', 'header'));
        $retval .= $LANG31[26];
        $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
        
        $retval .= $display .= display_mailform ($vars);

        return $retval;
    }

    $priority = 3; // Default normal priority

    if (!empty ($vars['to_group'])) {
    
        // ----------------------------------------------------
        // TEST EMAIL MODE
        // ----------------------------------------------------
        if (isset($_POST['test_email'])) {
            global $_USER;
            $destinataire = $_USER['email'];
            $username = $_USER['username'];
            $fullname = empty($_USER['fullname']) ? $_USER['username'] : $_USER['fullname'];
            
            $sujet = $vars['subject'];
            if (strpos($sujet, '[TEST] ') !== 0) {
                $sujet = '[TEST] ' . $sujet;
            }
            $content = $vars['content'];
            
            // Variable Replacement
            $content = str_replace(array('[USERNAME]', '[FULLNAME]'), array($username, $fullname), $content);
            
            // Test footer with an active but non-destructive unsubscribe simulation.
            // unsubscribe.php recognizes test=1 and never writes preferences or stats.
            $testFooter = '<br><br><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top: 1px solid #eaeaea; margin-top: 30px; padding-top: 15px; font-family: sans-serif; font-size: 11px; color: #777;"><tr><td align="center">';
            $testFooter .= sprintf($LANG_HELLO01['why_email'], $_CONF['site_name']);
            $testFooter .= '<br><strong>' . $LANG_HELLO01['test_email_footer'] . '</strong><br>';
            $testFooter .= HELLO_unsubscribeLink((int) $_USER['uid'], $destinataire, 0, true);

            $content .= $testFooter;

            $from = $_CONF['site_mail'];

            $mailSent = HELLO_mail($destinataire, $sujet, $content, $from, true, $priority);

            if ($mailSent) {
                $retval .= '<div role="status" style="margin: 0 0 20px 0; padding: 16px 18px; border: 2px solid #2e7d32; background: #edf7ed; color: #1b5e20; font-size: 16px; line-height: 1.5; border-radius: 4px;">';
                $retval .= '<strong style="font-size: 18px;">&#10003; ' . $LANG_HELLO01['test_email_success'] . '</strong><br>';
                $retval .= '<span style="font-size: 15px;">' . htmlspecialchars($destinataire, ENT_QUOTES, 'UTF-8') . '</span>';
                $retval .= '</div>';
            } else {
                $retval .= '<div role="alert" style="margin: 0 0 20px 0; padding: 16px 18px; border: 2px solid #b71c1c; background: #fdecea; color: #7f0000; font-size: 16px; line-height: 1.5; border-radius: 4px;">';
                $retval .= '<strong>' . $LANG_HELLO01['test_email_failed'] . '</strong>';
                $retval .= '</div>';
            }
            
            // Preserve form after test
            $retval .= display_mailform($vars);
            return $retval;
        }
        // ----------------------------------------------------
	    $groupList = implode (',', USER_getChildGroups($vars['to_group']));
		//Group name
		$group_name = DB_query("SELECT grp_name FROM {$_TABLES['groups']} WHERE grp_id =" . $vars['to_group'] . " ");
		$group_name = DB_fetchArray ($group_name);
		$email_group = $group_name[0];
		
        if (isset($_TABLES['user_attributes'])) {
            $sql = "SELECT DISTINCT username,fullname,email,emailfromadmin,{$_TABLES['users']}.uid FROM {$_TABLES['users']},{$_TABLES['user_attributes']},{$_TABLES['group_assignments']} WHERE {$_TABLES['users']}.uid > 1";
            $sql .= " AND {$_TABLES['users']}.status = 3 AND ((email is not null) and (email != ''))";
            $sql .= " AND {$_TABLES['users']}.uid = {$_TABLES['user_attributes']}.uid AND emailfromadmin = 1";
            $sql .= " AND ug_uid = {$_TABLES['users']}.uid AND ug_main_grp_id IN ({$groupList})";
        } else {
            $sql = "SELECT DISTINCT username,fullname,email,emailfromadmin,{$_TABLES['users']}.uid FROM {$_TABLES['users']},{$_TABLES['userprefs']},{$_TABLES['group_assignments']} WHERE {$_TABLES['users']}.uid > 1";
            $sql .= " AND {$_TABLES['users']}.status = 3 AND ((email is not null) and (email != ''))";
            $sql .= " AND {$_TABLES['users']}.uid = {$_TABLES['userprefs']}.uid AND emailfromadmin = 1";
            $sql .= " AND ug_uid = {$_TABLES['users']}.uid AND ug_main_grp_id IN ({$groupList})";
        }
		$result = DB_query ($sql);
		$nrows = DB_numRows ($result);
		$quantity = $nrows;
	} else {
        $retval .= COM_startBlock ($LANG31[1], '', COM_getBlockTemplate ('_msg_block', 'header'));
        $retval .= 'Error: No group selected.';
        $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
        return $retval;
    }

    $retval .= COM_startBlock ($LANG31[1]);

    // register hello
	

    //$creation = date ('YmdHi', time ());
    $creation = sprintf('%04d%02d%02d%02d%02d', $vars['start_year'], $vars['start_month'], $vars['start_day'], $vars['start_hour'], $vars['start_minute']);
    
	$subject = DB_escapeString($vars['subject']);
	$content = DB_escapeString($vars['content']);
	$from = $_CONF['site_mail'];

	$sql_ajout_hello = "INSERT INTO {$_TABLES['hello']} (subject, creation, email_group, quantity, content) VALUES ('$subject', '$creation', '$email_group', '$quantity','$content')";
	DB_query ($sql_ajout_hello);
	$new_hello_id = DB_insertId();	
    

    // Loop through and send the messages in the DB!
    $successes = 0;
    $failures = 0;
	if (!empty ($vars['to_group'])) {
        $insert_values = array();
		for ($i = 0; $i < $quantity; $i++) {
			$A = DB_fetchArray ($result);
			$destinataire = DB_escapeString($A['email']);
			$expediteur = DB_escapeString($from);
			$explication = '<br><br><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top: 1px solid #eaeaea; margin-top: 30px; padding-top: 15px; font-family: sans-serif; font-size: 11px; color: #777;"><tr><td align="center">';
            $explication .= sprintf($LANG_HELLO01['why_email'], $_CONF['site_name']) . ' &bull; ';
			
			// Administrative bulk newsletters always include an unsubscribe link.
            $maillink = HELLO_unsubscribeLink($A['uid'], $A['email'], $new_hello_id);
			
            // Add tracking pixel only when open tracking is enabled.
            $tracking_pixel = '';
            if (!isset($_HE_CONF['track_opens']) || (bool) $_HE_CONF['track_opens']) {
                $tracking_pixel = '<img src="' . $_CONF['site_url'] . '/hello/track.php?h=' . $new_hello_id . '&amp;u=' . $A['uid'] . '" width="1" height="1" alt="" style="display:none;" />';
            }
            
            $raw_content = $vars['content'];
			$contentfinal = DB_escapeString($raw_content . $explication . $maillink . $tracking_pixel);
            
            $uid = (int) $A['uid'];
		
            $insert_values[] = "('$expediteur', '$destinataire', '$creation', '$new_hello_id', '$subject', '$contentfinal', '$priority', '$uid')";
            
            if (count($insert_values) >= 100 || $i == $quantity - 1) {
                if (!empty($insert_values)) {
                    $sql_ajout_hello = "INSERT INTO {$_TABLES['hello_queue']} (expediteur, destinataire, creation, hello_id, subject, content, priority, uid) VALUES " . implode(', ', $insert_values);
                    if (DB_query($sql_ajout_hello)) {
                        $successes += count($insert_values);
                    } else {
                        $failures += count($insert_values);
                    }
                    $insert_values = array();
                }
            }
		}
	}

	if ($successes >= 0) {
        // Automatically redirect to the Campaigns page (read_email.php) after queueing
        echo COM_refresh($_CONF['site_admin_url'] . '/plugins/hello/read_email.php?msg=queued');
        exit;
	} 
	if ($failures > 0) {
	    $retval .= 'Oups... There was ' . $failures . ' failure(s)';
	}
  
    $retval .= COM_endBlock ();

    return $retval;
}

// MAIN


$display .= hello_admin_menu($LANG_HELLO01['send_email_group'], $LANG_HELLO01['inst_group']);

if (isset($_POST['mail']) && ($_POST['mail'] == 'mail') && SEC_checkToken()) {
    $display .= send_messages ($_POST);
} else {
    $display .= display_mailform ();
}

$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

$display = COM_createHTMLDocument($display);
COM_output($display);

?>