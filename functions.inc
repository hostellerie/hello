<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | functions.inc                                                             |
// |                                                                           |
// | This file does two things: 1) it implements the necessary Geeklog Plugin  |
// | API method and 2) implements all the common code needed by the hello      |
// | plugin' PHP files.                                                        |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.inc') !== false) {
    die('This file can not be used on its own.');
}

global $_CONF, $_TABLES, $_DB_table_prefix;

$pi_name    = 'hello';

// database table names 
$_TABLES['hello']               = $_DB_table_prefix . 'hello';
$_TABLES['hello_queue']    = $_DB_table_prefix . 'hello_queue';
$_TABLES['hello_stats']    = $_DB_table_prefix . 'hello_stats';
$_TABLES['hello_urls_clicked'] = $_DB_table_prefix . 'hello_urls_clicked';
$_TABLES['hello_links'] = $_DB_table_prefix . 'hello_links';
	
// path to the plugin
$plugin_path = $_CONF['path'] . 'plugins/hello/';

// Set lang file or english by default
$langfile = $plugin_path . 'language/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    require_once $langfile;
} else {
    require_once $plugin_path . 'language/english.php';
}


// Check and see if we need to load the plugin configuration. HE and he refert to hello plugin
 
if (!isset($_HE_CONF['max_email'])) {
    require_once $_CONF['path_system'] . 'classes/config.class.php';
    
    $he_config = config::get_instance();
    $_HE_CONF = $he_config->get_config('hello');
}


// +---------------------------------------------------------------------------+
// | Geeklog Plugin API Implementations                                        |
// +---------------------------------------------------------------------------+


// Returns the administrative option for this plugin
// Adds the plugin to the Admin menu
// @return   array   Array containing (plugin name, plugin admin url, # of items in plugin or '')

function plugin_getadminoption_hello() {
    
    global $_CONF, $LANG_HELLO01, $_TABLES, $_SCRIPTS;
    
	$blink = '';
	$alert = '';
	
    if (SEC_hasRights('hello.edit')) {
        if (SEC_hasRights('user.mail')) {
			if ($_CONF['emailstories'] == 1) {
				$lastrun = DB_getItem ($_TABLES['vars'], 'value', "name = 'lastemailedstories'");
				if ($lastrun == '') $lastrun = 0;
				$storysql = "SELECT sid FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= NOW() AND date >= '{$lastrun}'";
				$result = DB_query ($storysql);
				$count = DB_numRows ($result);
			} else {
				$count = 0;
			}

			if ($count > 0) {
				
				$alert = ' <strong><span style="color:red">' . $count . '</span></strong>';
			}
		}
		
		return array($LANG_HELLO01['plugin_name'] . $alert, $_CONF['site_admin_url'] . '/plugins/hello/index.php');
    } else {
       return;
    }
}

function plugin_commentsupport_hello()
{
    // hello will use comments
    return false;
}

// This will put an option for hello in the command and control block on
// moderation.php

function plugin_cclabel_hello()
{
    global $_CONF, $LANG_HELLO01;

    if (SEC_hasRights ('hello.edit')) {
        return array ($LANG_HELLO01['plugin_name'],
                $_CONF['site_admin_url'] . '/plugins/hello/index.php',
                plugin_geticon_hello ());
    }

    return false;
}


// Returns the URL of the plugin's icon
// @return   string      URL of the icon

function plugin_geticon_hello ()
{
    global $_CONF;
    return $_CONF['site_admin_url']  .'/plugins/hello/images/hello.png';
}

// Return the current version of code.
// Used in the Plugin Editor to show the registered version and code version


function plugin_chkVersion_hello()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/hello/autoinstall.php';

    $inst_parms = plugin_autoinstall_hello('hello');

    return $inst_parms['info']['pi_version'];
}

// Automatic uninstall function for plugins
// @return   array
// This code is automatically uninstalling the plugin.
// It passes an array to the core code function that removes
// tables, groups, features and php blocks from the tables.
// Additionally, this code can perform special actions that cannot be
// foreseen by the core code (interactions with other plugins for example)

function plugin_autouninstall_hello ()
{
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('hello', 'hello_queue', 'hello_stats', 'hello_urls_clicked', 'hello_links'),
        /* give the full name of the group, as in the db */
        'groups' => array('hello Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('hello.edit'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars'=> array('hello_hourly_count', 'hello_hourly_reset')
    );

    return $out;
}


// Get path for the template files.
//* @param    string  $path   subdirectory within the base template path
// @return   string          full path to template directory

function hello_templatePath ($path = '')
{
    global $_CONF;

    if (empty ($path)) {
        $layout_path = $_CONF['path_layout'] . $pi_name;
    } else {
        $layout_path = $_CONF['path_layout'] . $pi_name .  '/' . $path;
    }

    if (is_dir ($layout_path)) {
        $retval = $layout_path;
    } else {
        $retval = $_CONF['path'] . 'plugins/' . $pi_name . '/templates';
        if (!empty ($path)) {
            $retval .= '/' . $path;
        }
    }

    	return $retval;
}

// Update the hello plugin

function plugin_upgrade_hello()
{
    global $_CONF, $_TABLES, $_DB_table_prefix, $_DB_dbms;

    $installed_version = DB_getItem($_TABLES['plugins'], 'pi_version',
                                    "pi_name = 'hello'");
    
    $code_version = plugin_chkVersion_hello();
    
    if ($installed_version == $code_version) {
        return true;
    }

    require_once $_CONF['path'] . 'plugins/hello/autoinstall.php';
    require_once $_CONF['path'] . 'plugins/hello/install_defaults.php';
    require_once $_CONF['path_system'] . 'classes/config.class.php';

    if (! plugin_compatible_with_this_version_hello('hello')) {
        return 3002;
    }

    $update_file = $_CONF['path'] . 'plugins/hello/sql/' . $_DB_dbms . '_updates.php';
    if (file_exists($update_file)) {
        require_once $update_file;
    }
    
    $install_updates_file = $_CONF['path'] . 'plugins/hello/install_updates.php';
    if (file_exists($install_updates_file)) {
        require_once $install_updates_file;
    }

    $current_version = $installed_version;
    $done = false;
    $current_config = false;
    
    while (! $done) {
        switch( $current_version ) {
            
            case '1.0.0':
                //rename db hello_file_envoi
                DB_query("RENAME TABLE $_DB_table_prefix"."hello_file_envoi TO {$_TABLES['hello_queue']};");
                
                //upgrade config values
                $c = config::get_instance();
                $c->del('site_name', 'classifieds');
                $c->del('hellologinrequired', 'hello');
                $c->del('hidehellomenu', 'hello');
                $c->del('showleftblocks1', 'hello');
                $c->del('showrightblocks1', 'hello');	
                $c->del('showleftblocks2', 'hello');
                $c->del('showrightblocks2', 'hello');
                
                $c->add('max_email', $_HE_DEFAULT['max_email'],
                    'text', 0, 0, 0, 10, true, 'hello');
                    
                $current_version = '2.0';
                break;
                
            case '2.0':
                $current_version = '2.1';
                break;
                
            case '2.1':
                $current_version = '2.1.1';
                break;
                
            case '2.1.1':
            case '2.1.2':
                if (isset($_UPDATES[$current_version])) {
                    $_SQL = $_UPDATES[$current_version];
                    foreach ($_SQL as $sql) {
                        DB_query($sql);
                    }
                }
                
                // Add status column for campaign pause/resume
                $check_status = DB_query("SHOW COLUMNS FROM {$_TABLES['hello']} LIKE 'status'");
                if (DB_numRows($check_status) == 0) {
                    DB_query("ALTER TABLE {$_TABLES['hello']} ADD COLUMN status tinyint(1) NOT NULL DEFAULT 0");
                }
                
                // Add strict_throttling (hourly_limit) if missing
                require_once $_CONF['path_system'] . 'classes/config.class.php';
                $c = config::get_instance();
                if (!isset($_CONF['hello']['hourly_limit'])) {
                    $c->add('hourly_limit', 150, 'text', 0, 0, 0, 20, true, 'hello', 0);
                }
                
                $current_version = '2.1.3';
                break;
                
            case '2.1.3':
                $current_version = '2.1.4';
                break;
            case '2.1.4':
                $current_version = '2.2.0';
                break;
                
            case '2.2.0':
                if (isset($_UPDATES['2.2.0'])) {
                    foreach ($_UPDATES['2.2.0'] as $sql) {
                        DB_query($sql);
                    }
                }

                $c = config::get_instance();
                $c->add('track_clicks', 1, 'select', 0, 0, 0, 30, true, 'hello');
                $c->add('track_opens', 1, 'select', 0, 0, 0, 40, true, 'hello');

                $current_version = '2.2.1';
                break;

            case '2.2.1':
                $done = true;
                break;
                
            default:
                $done = true;
                break;
        }
    }

    // update plugin version number
    $inst_parms = plugin_autoinstall_hello('hello');
    $pi_gl_version = $inst_parms['info']['pi_gl_version'];

    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '$code_version', pi_gl_version = '$pi_gl_version' WHERE pi_name = 'hello'");
    return true;
}

/**
 * Returns admin menu display
 *
 * Generates the admin menu from the template and returns the result as a string of HTML
 *
 * @return string HTML of admin menu
 * @todo add support for pending (physical) transaction, not fully implemented
 */
function hello_admin_menu($title = '', $instructions = '', $icon = '') {
    global $_CONF, $_TABLES, $LANG_HELLO01;
    require_once $_CONF['path_system'] . 'lib-admin.php';

    if (empty($title)) {
        $title = $LANG_HELLO01['block_headline'];
    }

    $display = COM_startBlock($title, '', COM_getBlockTemplate('_admin_block', 'header'));

    $menu_arr = array(
        array('url'  => $_CONF['site_admin_url'] . '/plugins/hello/index.php',
              'text' => $LANG_HELLO01['homepage_label']),
        array('url'  => $_CONF['site_admin_url'] . '/plugins/hello/email_group.php',
              'text' => $LANG_HELLO01['send_email_group']),
        array('url'  => $_CONF['site_admin_url'] . '/plugins/hello/read_email.php',
              'text' => $LANG_HELLO01['read_email']),
        array('url'  => $_CONF['site_admin_url'] . '/plugins/hello/subscribers.php',
              'text' => $LANG_HELLO01['crm_dashboard']),
        array('url'  => $_CONF['site_admin_url'] . '/plugins/hello/manual.php',
              'text' => $LANG_HELLO01['manual']),
        array('url'  => $_CONF['site_admin_url'] . '/plugins/hello/bounces.php',
              'text' => $LANG_HELLO01['bounces']),
        array('url'  => $_CONF['site_admin_url'] . '/configuration.php?conf_group=hello',
              'text' => $LANG_HELLO01['configuration'])
    );
    
    // Add CSRF token for config form submission ? No, ADMIN_createMenu generates anchor links.
    // Wait, the configuration link in Geeklog admin might need to be a POST if it's the config page.
    // Actually, GET /configuration.php?conf_group=hello works fine in Geeklog!
    
    if (empty($icon)) {
        $icon = $_CONF['site_admin_url'] . '/plugins/hello/images/hello.png';
    }
    
    $display .= ADMIN_createMenu($menu_arr, $instructions, $icon);

    // return results
    return $display;
}

function HELLO_undoSpecialChars( $string )
{
    $string = (string) $string;
    $string = str_replace( '&#36;',     '$', $string );
    $string = str_replace( '&#123;',    '{', $string );
    $string = str_replace( '&#125;',    '}', $string );
    $string = str_replace( '&gt;',      '>', $string );
    $string = str_replace( '&lt;',      '<', $string );
    $string = str_replace( '&quot;',    '"', $string );
    $string = str_replace( '&nbsp;',    ' ', $string );
    $string = str_replace( '&amp;',     '&', $string );
    $string = str_replace( '&Agrave;',  'A', $string );
    $string = str_replace( '&agrave;',  'Ã ', $string );
    $string = str_replace( '&Acirc;',   'A', $string );
    $string = str_replace( '&acirc;',   'Ã¢', $string );
    $string = str_replace( '&Ccedil;',  'C', $string );
    $string = str_replace( '&ccedil;',  'Ã§', $string );
    $string = str_replace( '&Egrave;',  'E', $string );
    $string = str_replace( '&egrave;',  'Ã¨', $string );
    $string = str_replace( '&Eacute;',  'E', $string );
    $string = str_replace( '&eacute;',  'Ã©', $string );
    $string = str_replace( '&Ecirc;',   'E', $string );
    $string = str_replace( '&ecirc;',   'Ãª', $string );
    $string = str_replace( '&Euml;',    'E', $string );
    $string = str_replace( '&euml;',    'Ã«', $string );
    $string = str_replace( '&Icirc;',   'I', $string );
    $string = str_replace( '&icirc;',   'Ã®', $string );
    $string = str_replace( '&Iuml;',    'I', $string );
    $string = str_replace( '&iuml;',    'Ã¯', $string );
    $string = str_replace( '&Ocirc;',   'O', $string );
    $string = str_replace( '&ocirc;',   'Ã´', $string );
    $string = str_replace( '&OElig;',  'OE', $string );
    $string = str_replace( '&oelig;',  'oe', $string );
    $string = str_replace( '&Ugrave;',  'U', $string );
    $string = str_replace( '&ugrave;',  'Ã¹', $string );
    $string = str_replace( '&Ucirc;',   'U', $string );
    $string = str_replace( '&ucirc;',   'Ã»', $string );
    $string = str_replace( '&Uuml;',    'U', $string );
    $string = str_replace( '&uuml;',    'Ã¼', $string );
    $string = str_replace( '&#376;',    'Y', $string );
    $string = str_replace( '&yuml;',    'Ã¿', $string );
    $string = str_replace( '&rsquo;',  '\'', $string );
 
    return( $string );
}

/**
* Send an email.
*
* All emails sent by hello plugin are sent through this function.
*
* NOTE: Please note that using CC: will expose the email addresses of
*       all recipients. Use with care.
*
* @param    string      $to         recipients name and email address
* @param    string      $subject    subject of the email
* @param    string      $message    the text of the email
* @param    string      $from       (optional) sender of the the email
* @param    boolean     $html       (optional) true if to be sent as HTML email
* @param    int         $priority   (optional) add X-Priority header, if > 0
* @param    mixed       $optional   (optional) other headers or CC:
* @return   boolean                 true if successful,  otherwise false
*
*/



function HELLO_send_hello($manual = false) {
    global $_CONF, $_TABLES, $_USER, $LANG_HELLO01, $_HE_CONF;

    $results = 0;
    $sujet = '';
    $h = 0;

    set_time_limit(260);
    $heure_script = date('YmdHi', time());
    
    // Check hourly limit guardrail
    $hourly_limit = isset($_HE_CONF['hourly_limit']) ? (int) $_HE_CONF['hourly_limit'] : 150;
    $current_time = time();
    $reset_time = (int) DB_getItem($_TABLES['vars'], 'value', "name='hello_hourly_reset'");
    $count = (int) DB_getItem($_TABLES['vars'], 'value', "name='hello_hourly_count'");
    
    // Reset counter if time has passed
    if (empty($reset_time) || $current_time > $reset_time) {
        $reset_time = $current_time + 3600; // Reset in 1 hour
        DB_query("REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('hello_hourly_reset', '$reset_time')");
        DB_query("REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('hello_hourly_count', '0')");
        $count = 0;
    }
    
    $allowed_to_send = $hourly_limit - $count;
    
    if ($allowed_to_send <= 0) {
        // We hit the hard limit for this hour, abort.
        return ($manual) ? '<p>' . sprintf($LANG_HELLO01['hourly_quota_reached'], $hourly_limit) . '</p>' : null;
    }
    
    // Dynamic batch size to never exceed the limit
    $max_email_pref = isset($_HE_CONF['max_email']) ? (int) $_HE_CONF['max_email'] : 10;
    $batch_size = min($max_email_pref, $allowed_to_send);

    $sql = "SELECT q.* FROM {$_TABLES['hello_queue']} q "
         . "INNER JOIN {$_TABLES['hello']} h ON q.hello_id = h.hello_id "
         . "WHERE q.creation <= " . $heure_script . " AND h.status = 0 "
         . "ORDER BY q.uid ASC, q.creation ASC LIMIT 0, $batch_size";

    $res = DB_query($sql);
    $successful_ids = array();

    while ($A = DB_fetchArray($res)) {
        if ($sujet != '' && $A['subject'] != $sujet) {
            COM_errorLog($sujet . ' | ' . $LANG_HELLO01['hello_sent'] . ' ' . $results . ' ' . $LANG_HELLO01['contacts']);
        }

        $destinataire = $A['destinataire'];
        $sujet = $A['subject'];
        $message = $A['content'];

        $html = true;
        $priority = isset($A['priority']) ? (int) $A['priority'] : 0;
        $from = isset($A['expediteur']) ? $A['expediteur'] : '';

        // Generate unsubscribe link for headers
        $uid = isset($A['uid']) ? (int)$A['uid'] : 0;
        $hello_id = isset($A['hello_id']) ? (int)$A['hello_id'] : 0;
        $hash = MD5($destinataire."fFersh66");
        $unsubscribe_url = $_CONF['site_url'] . '/hello/unsubscribe.php?h=' . $hash . '&u=' . $uid . '&hid=' . $hello_id;
        
        // --- 1. Variable Replacement ---
        if ($uid > 1) {
            $userinfo = DB_fetchArray(DB_query("SELECT username, fullname FROM {$_TABLES['users']} WHERE uid = $uid"));
            $username = $userinfo['username'];
            $fullname = empty($userinfo['fullname']) ? $userinfo['username'] : $userinfo['fullname'];
            $message = str_replace(array('[USERNAME]', '[FULLNAME]'), array($username, $fullname), $message);
        }
        
        // --- 2. URL Rewriting for Clicks ---
        // Store destinations server-side and expose only a short opaque token.
        // This avoids long Base64 redirect URLs that can look suspicious to
        // anti-phishing filters and may exceed SMTP line-length limits.
        $track_clicks = !isset($_HE_CONF['track_clicks']) || (bool) $_HE_CONF['track_clicks'];
        if ($track_clicks) {
            $message = HELLO_rewriteTrackedLinks($message, $hello_id, $uid);
        }

        // Keep HTML source readable and SMTP-friendly. Long one-line HTML can
        // exceed the RFC 5322 line limit and be folded inside attributes.
        $message = HELLO_prepareHtmlForEmail($message);
        
        $optional = array(
            'List-Unsubscribe' => '<' . $unsubscribe_url . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click'
        );

        $result = HELLO_mail($destinataire, $sujet, $message, $from, $html, $priority, $optional);

        if ($result) {
            $results++;
            $successful_ids[] = (int) $A['id'];
            
            // Log that the email was successfully sent for CRM stats
            if ($hello_id > 0 && $uid > 1) {
                DB_query("INSERT INTO {$_TABLES['hello_stats']} (hello_id, uid, sent) VALUES ($hello_id, $uid, 1) ON DUPLICATE KEY UPDATE sent = 1");
            }
        } else {
            COM_errorLog('Hello plugin - Error sending email to ' . $destinataire, 1);
        }
        $h++;
    }

    if (!empty($successful_ids)) {
        $id_list = implode(',', $successful_ids);
        DB_query("DELETE FROM {$_TABLES['hello_queue']} WHERE id IN ($id_list)");
        
        // Increment our hourly guardrail counter
        $new_count = $count + $results;
        DB_query("REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('hello_hourly_count', '$new_count')");
    }

    if ($manual) {
        return '<p>' . $results . ' ' . $LANG_HELLO01['email_sent'] . '</p>';
    }
}



/**
 * Prepare HTML for transport without altering rendered content.
 *
 * Breaking only between adjacent tags keeps individual lines comfortably
 * below SMTP's 998-character hard limit in normal newsletter content.
 */
function HELLO_prepareHtmlForEmail($html)
{
    $html = str_replace(array("\r\n", "\r"), "\n", (string) $html);
    $html = preg_replace('/>\s*</', ">\r\n<", $html);
    return $html;
}


/**
 * Build compact Geeklog vars keys for isolated administrator tracking tests.
 */
function HELLO_testTrackingVarName($uid, $context, $event)
{
    $uid = (int) $uid;
    $context_code = ($context === 'digest') ? 'd' : 'c';
    $event_code = ($event === 'open') ? 'o' : 'c';

    return 'he_t' . $context_code . $event_code . '_' . $uid;
}

function HELLO_resetTestTracking($uid, $context)
{
    global $_TABLES;

    $uid = (int) $uid;
    if ($uid <= 1) {
        return;
    }

    $open_name = DB_escapeString(HELLO_testTrackingVarName($uid, $context, 'open'));
    $click_name = DB_escapeString(HELLO_testTrackingVarName($uid, $context, 'click'));

    DB_query("DELETE FROM {$_TABLES['vars']} WHERE name IN ('$open_name', '$click_name')");
}

function HELLO_recordTestTracking($uid, $context, $event)
{
    global $_TABLES;

    $uid = (int) $uid;
    if ($uid <= 1 || ($context !== 'digest' && $context !== 'campaign')
        || ($event !== 'open' && $event !== 'click')) {
        return false;
    }

    $name = DB_escapeString(HELLO_testTrackingVarName($uid, $context, $event));
    $value = date('YmdHis');

    DB_query("REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('$name', '$value')");

    return !DB_error();
}

function HELLO_getTestTrackingStatus($uid, $context)
{
    global $_TABLES;

    $uid = (int) $uid;
    $status = array('open' => '', 'click' => '');

    if ($uid <= 1) {
        return $status;
    }

    foreach (array('open', 'click') as $event) {
        $name = DB_escapeString(HELLO_testTrackingVarName($uid, $context, $event));
        $status[$event] = DB_getItem($_TABLES['vars'], 'value', "name = '$name'");
    }

    return $status;
}

function HELLO_testTrackingStatusHtml($uid, $context)
{
    global $LANG_HELLO01, $_HE_CONF;

    $status = HELLO_getTestTrackingStatus($uid, $context);
    $context_label = ($context === 'digest')
        ? $LANG_HELLO01['tracking_test_digest']
        : $LANG_HELLO01['tracking_test_campaign'];

    $format_event = function ($value, $success_label, $waiting_label) {
        if (empty($value) || !preg_match('/^\d{14}$/', $value)) {
            return '<span style="color:#8a6d3b;">&#9679; ' . $waiting_label . '</span>';
        }

        $date = substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2)
            . ' ' . substr($value, 8, 2) . ':' . substr($value, 10, 2) . ':' . substr($value, 12, 2);

        return '<span style="color:#1b5e20;">&#10003; ' . $success_label . ' — '
            . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '</span>';
    };

    $open_enabled = !isset($_HE_CONF['track_opens']) || (bool) $_HE_CONF['track_opens'];
    $click_enabled = !isset($_HE_CONF['track_clicks']) || (bool) $_HE_CONF['track_clicks'];

    $open_status = $open_enabled
        ? $format_event($status['open'], $LANG_HELLO01['tracking_test_open_ok'], $LANG_HELLO01['tracking_test_open_wait'])
        : '<span style="color:#777;">&#8212; ' . $LANG_HELLO01['tracking_test_open_disabled'] . '</span>';
    $click_status = $click_enabled
        ? $format_event($status['click'], $LANG_HELLO01['tracking_test_click_ok'], $LANG_HELLO01['tracking_test_click_wait'])
        : '<span style="color:#777;">&#8212; ' . $LANG_HELLO01['tracking_test_click_disabled'] . '</span>';

    $html = '<div style="margin:15px 0; padding:14px 16px; border:1px solid #d6d8db; background:#f8f9fa; border-radius:4px;">';
    $html .= '<strong>' . $LANG_HELLO01['tracking_test_title'] . ' — ' . $context_label . '</strong>';
    $html .= '<div style="margin-top:8px;">' . $open_status . '</div>';
    $html .= '<div style="margin-top:5px;">' . $click_status . '</div>';
    $html .= '<div style="margin-top:8px; font-size:12px; color:#666;">'
        . $LANG_HELLO01['tracking_test_help'] . '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * Create a cryptographically opaque click token and store its destination.
 */
function HELLO_createClickToken($url, $hello_id, $uid, $test_mode = false)
{
    global $_TABLES;

    $hello_id = (int) $hello_id;
    $uid = (int) $uid;
    $url = html_entity_decode((string) $url, ENT_QUOTES, 'UTF-8');

    if ($uid <= 1 || !filter_var($url, FILTER_VALIDATE_URL) || (!$test_mode && $hello_id <= 0) || ($test_mode && $hello_id !== 0)) {
        return '';
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if ($scheme !== 'http' && $scheme !== 'https') {
        return '';
    }

    for ($i = 0; $i < 5; $i++) {
        if (function_exists('random_bytes')) {
            try {
                $token = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $token = '';
            }
        } else {
            $token = '';
        }

        if ($token === '') {
            if (function_exists('openssl_random_pseudo_bytes')) {
                $bytes = openssl_random_pseudo_bytes(16);
                if ($bytes !== false) {
                    $token = bin2hex($bytes);
                }
            }
        }

        if ($token === '') {
            $token = md5(uniqid((string) mt_rand(), true) . microtime(true));
        }

        $safe_token = DB_escapeString($token);
        $exists = DB_getItem($_TABLES['hello_links'], 'link_id', "token = '$safe_token'");
        if (empty($exists)) {
            $safe_url = DB_escapeString($url);
            $created = date('Y-m-d H:i:s');
            DB_query("INSERT INTO {$_TABLES['hello_links']} (token, hello_id, uid, url, created) "
                . "VALUES ('$safe_token', $hello_id, $uid, '$safe_url', '$created')");
            if (!DB_error()) {
                return $token;
            }
        }
    }

    return '';
}

/**
 * Replace HTTP(S) links with short first-party tracked links.
 */
function HELLO_rewriteTrackedLinks($html, $hello_id, $uid, $test_mode = false, $test_context = 'campaign')
{
    global $_CONF;

    $cache = array();

    return preg_replace_callback("/href=([\'\"])(https?:\/\/[^\'\"]+)\\1/i",
        function ($matches) use ($hello_id, $uid, $test_mode, $test_context, $_CONF, &$cache) {
            $url = html_entity_decode($matches[2], ENT_QUOTES, 'UTF-8');

            if (strpos($url, '/hello/unsubscribe.php') !== false
                || strpos($url, '/hello/track.php') !== false
                || strpos($url, '/hello/click.php') !== false) {
                return $matches[0];
            }

            if (!isset($cache[$url])) {
                $cache[$url] = HELLO_createClickToken($url, $hello_id, $uid, $test_mode);
            }

            if ($cache[$url] === '') {
                return $matches[0];
            }

            $tracked = $_CONF['site_url'] . '/hello/click.php?t=' . rawurlencode($cache[$url]);
            if ($test_mode) {
                $tracked .= '&test=1&k=' . (($test_context === 'digest') ? 'digest' : 'campaign');
            }
            return 'href="' . htmlspecialchars($tracked, ENT_QUOTES, 'UTF-8') . '"';
        }, $html);
}

function HELLO_tagToPlain ($content)
{
    $content = (string) $content;
    if (empty($content)) {
        return '';
    }
    
    $dom = new DOMDocument();
    @$dom->loadHTML(htmlspecialchars($content));

    $anchors = $dom->getElementsByTagName('a');
    $len = $anchors->length;

    if ( $len > 0 ) {
        $i = $len-1;
        while ( $i > -1 ) {
        $anchor = $anchors->item( $i );

        if ( $anchor->hasAttribute('href') ) {
            $href = $anchor->getAttribute('href');
            $regex = '/^http/';

            if ( !preg_match ( $regex, $href ) ) { 
            $i--;
            continue;
            }

            $text = $anchor->nodeValue;
            $textNode = $dom->createTextNode( $text . ' ' . $href);

            $strong = $dom->createElement('a');
            $strong->appendChild( $textNode );

            $anchor->parentNode->replaceChild( $strong, $anchor );
        }
        $i--;
        }
    }

    $altBody = $dom->saveHTML();
    $altBody = str_replace(array("<p>", "</p>", "<br/>"), "\r\n", $altBody);
    $altBody  =  HELLO_undoSpecialChars( strip_tags($altBody ));
	$altBody  =  preg_replace('/\n(\s*\n){2,}/', "\n\n", $altBody);
    return $altBody;
}

/**
* This will email new stories in the topics that the user is interested in
*
* In account information the user can specify which topics for which they
* will receive any new article for in a daily digest.
*
* @return   void
*/
 

/**
 * Make story images safe for email clients.
 *
 * Fixed pixel dimensions from article HTML can reserve an oversized box when
 * remote images are blocked. Remove width/height attributes and CSS dimensions,
 * then apply responsive email-safe styling.
 */
function HELLO_emailSafeImages($html)
{
    return preg_replace_callback('/<img\b[^>]*>/i', function ($matches) {
        $tag = $matches[0];

        // Remove HTML width/height attributes.
        $tag = preg_replace('/\s+(width|height)\s*=\s*(["\']).*?\2/i', '', $tag);
        $tag = preg_replace('/\s+(width|height)\s*=\s*[^\s>]+/i', '', $tag);

        // Remove fixed width/height declarations from an existing style.
        if (preg_match('/\sstyle\s*=\s*(["\'])(.*?)\1/i', $tag, $style_match)) {
            $style = $style_match[2];
            $style = preg_replace('/(?:^|;)\s*(?:width|height|max-width|min-width|max-height|min-height)\s*:[^;]*/i', '', $style);
            $style = trim(preg_replace('/;{2,}/', ';', $style), " ;");
            $safe_style = 'max-width:100%; height:auto; display:block; border:0;';
            if ($style !== '') {
                $safe_style = $style . '; ' . $safe_style;
            }
            $tag = preg_replace('/\sstyle\s*=\s*(["\'])(.*?)\1/i',
                ' style="' . $safe_style . '"', $tag, 1);
        } else {
            $tag = preg_replace('/\s*\/?>$/', ' style="max-width:100%; height:auto; display:block; border:0;">', $tag);
        }

        return $tag;
    }, $html);
}

function HELLO_emailUserTopics($online=true, $test_mode=false)
{
    global $_CONF, $_TABLES, $LANG04, $LANG08, $LANG24, $LANG_HELLO01, $_USER;
 
    $subject = addslashes(strip_tags( '[' . $_CONF['site_name'] . '] ' . $LANG08[30] . strftime( $_CONF['shortdate'], time() )));
    
    if ($test_mode) {
        $subject = '[TEST] ' . $subject;
        HELLO_resetTestTracking((int) $_USER['uid'], 'digest');
    }
 
    $authors = array();
    $nrows = 0;
    $nsrows = 0;
    $successes = 0;
    $failures = 0;
 
    // Get users who want stories emailed to them and are subscribed
    if ($test_mode) {
        // En mode test, on n'envoie qu'Ã  l'utilisateur en cours
        $usersql = "SELECT DISTINCT uid AS uuid, username, fullname, email, '' AS etids FROM {$_TABLES['users']} WHERE uid = {$_USER['uid']}";
    } else {
        $prefs_table = isset($_TABLES['user_attributes']) ? $_TABLES['user_attributes'] : $_TABLES['userprefs'];
        
        $has_etids = false;
        $check_res = DB_query("SHOW COLUMNS FROM {$prefs_table} LIKE 'etids'", 1);
        if ($check_res && DB_numRows($check_res) > 0) {
            $has_etids = true;
        }

        $etids_select = $has_etids ? ", etids" : ", '' AS etids";
        $etids_where  = $has_etids ? " AND (etids <> '-' OR etids IS NULL)" : "";

        $usersql = "SELECT DISTINCT {$_TABLES['users']}.uid AS uuid, username, fullname, email{$etids_select} "
            . "FROM {$_TABLES['users']}, {$prefs_table} "
            . "WHERE {$_TABLES['users']}.uid > 1 AND {$_TABLES['users']}.status = 3 AND {$_TABLES['users']}.email <> '' "
            . "AND {$prefs_table}.uid = {$_TABLES['users']}.uid "
            . "AND emailfromadmin = 1{$etids_where} ORDER BY {$_TABLES['users']}.uid";
    }
 
    $users = DB_query( $usersql );
    $nrows = DB_numRows( $users );
 
    $lastrun = DB_getItem( $_TABLES['vars'], 'value', "name = 'lastemailedstories'" );
	
	$sql = "SELECT sid FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= NOW() AND date >= '{$lastrun}'";
    $result = DB_query ($sql);
    $count = DB_numRows ($result);
	
	// Prevent multi sending
    if (!$test_mode) {
	    DB_query( "UPDATE {$_TABLES['vars']} SET value = NOW() WHERE name = 'lastemailedstories'" );
    }
	
	//record hello
	$creation = date ('YmdHi', time ());
	($online) ? $email_group = $LANG_HELLO01['mdigest'] : $email_group = $LANG_HELLO01['ddigest'];
	
	//Qty will be fixe later
	$quantity = 0;

	$content  = addslashes(sprintf ($LANG_HELLO01['num_stories_digest'] . ' > ' . $lastrun, $count));
	
	if (!$test_mode) {
	    $sql_ajout_hello = "INSERT INTO {$_TABLES['hello']} (subject, creation, email_group, quantity, content) VALUES ('$subject', '$creation', '$email_group', '$quantity','$content')";
	    DB_query ($sql_ajout_hello);
	    $new_hello_id = DB_insertId();
	} else {
	    $new_hello_id = 0;
	}
 
    // For each user, pull the stories they want and email it to them
	$successes = 0;
    $failures = 0;
	
	// Loop users
	for( $x = 1; $x <= $nrows; $x++ )
    {
        $U = DB_fetchArray( $users );
		
		//No email for this user
		if ($U['email'] == '') {
		    $failures = $failures + 1;
			continue;
		}
 
        $storysql = array();
        $storysql['mysql'] = "SELECT DISTINCT sid,uid,date AS day,title,introtext,bodytext,postmode";
 
        $commonsql = " FROM {$_TABLES['stories']} s, {$_TABLES['topic_assignments']} ta
            WHERE draft_flag = 0 AND date <= NOW() AND date >= '{$lastrun}'
            AND ta.type = 'article' AND ta.id = sid";
 
        $topicsql = "SELECT tid FROM {$_TABLES['topics']}"
                  . COM_getPermSQL( 'WHERE', $U['uuid'] );
        $tresult = DB_query( $topicsql );
        $trows = DB_numRows( $tresult );
 
        if( $trows == 0 )
        {
            // this user doesn't seem to have access to any topics ...
            continue;
        }
 
        $TIDS = array();
        for( $i = 1; $i <= $trows; $i++ )
        {
            $T = DB_fetchArray( $tresult );
            $TIDS[] = $T['tid'];
        }
 
        if( !empty( $U['etids'] ))
        {
            $ETIDS = explode( ' ', $U['etids'] );
            $TIDS = array_intersect( $TIDS, $ETIDS );
        }
 
        if( sizeof( $TIDS ) > 0)
        {// We have list of Daily Digest topic ids that user has access too and that the user wants a report on
             $commonsql .= " AND (ta.tid IN ('" . implode( "','", $TIDS ) . "'))";
        }
 
        $commonsql .= COM_getPermSQL( 'AND', $U['uuid'],2,'s' );
        $commonsql .= ' ORDER BY date DESC';
 
        $storysql['mysql'] .= $commonsql;
 
        $stories = DB_query( $storysql );
        $nsrows = DB_numRows( $stories );
 
        if( $nsrows == 0 )
        {
            continue;
        }
        
        $display_name = !empty($U['fullname']) ? $U['fullname'] : $U['username'];
        
        $mailtext = '<!DOCTYPE html><html><body style="margin:0; padding:0; background-color:#f4f4f4;">';
        $mailtext .= '<div style="background-color:#f4f4f4; padding:20px; font-family: sans-serif;">';
        $mailtext .= '<div style="max-width:600px; margin:0 auto; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 5px rgba(0,0,0,0.1);">';
        
        // Header
        $mailtext .= '<div style="background-color:#2c3e50; padding:25px 20px; text-align:center; color:#ffffff;">';
        $mailtext .= '<h1 style="margin:0; font-size:28px; letter-spacing:1px;">' . $_CONF['site_name'] . '</h1>';
        $mailtext .= '</div>';
        
        // Body
        $mailtext .= '<div style="padding:30px; color:#333333; line-height:1.6;">';
        
        // Use generic welcome text that fits the site language
        $mailtext .= '<p style="font-size:15px; color:#555;">' . sprintf($LANG_HELLO01['digest_intro_email'], '<strong>' . HELLO_undoSpecialChars(stripslashes($display_name)) . '</strong>', '<strong>' . $_CONF['site_name'] . '</strong>') . '</p>';
  
        //For each story
		for( $y = 0; $y < $nsrows; $y++ )
        {
            // Loop through stories building the requested email message
            $S = DB_fetchArray( $stories );
 
            $mailtext .= '<div style="border-bottom: 1px solid #eeeeee; padding: 20px 0;">';
			
            // Title
            $story_url = $_CONF['site_url'] . '/article.php?story=' . rawurlencode($S['sid']);

            // Keep exactly one promotional link per article in digests.
            // The title is plain text; only the final read-article CTA links.
            $mailtext .= '<h2 style="margin: 0 0 5px 0; font-size: 20px;">'
                . HELLO_undoSpecialChars(stripslashes($S['title'])) . '</h2>';
			
			// date & author
			$storyauthor = '';
            if( $_CONF['contributedbyline'] == 1 )
            {
                if( empty( $authors[$S['uid']] ))
                {
                    $storyauthor = COM_getDisplayName ($S['uid']);
                    $authors[$S['uid']] = $storyauthor;
                }
                else
                {
                    $storyauthor = $authors[$S['uid']];
                }
            }
            $auth_text = $storyauthor ? ' - ' . $storyauthor : '';
            $mailtext .= '<p style="margin: 0 0 15px 0; font-size: 12px; color: #7f8c8d;">' . strftime( $_CONF['date'], strtotime( $S['day' ])) . $auth_text . '</p>';
			
            // Content
			if( $_CONF['emailstorieslength'] > 0 )
            {
                if($S['postmode']==='wikitext'){
                    $storytext = HELLO_undoSpecialChars( strip_tags( COM_renderWikiText ( stripslashes( $S['introtext'] ))));
                } else {
                    $storytext = stripslashes($S['introtext']);
                }
 
                if( $_CONF['emailstorieslength'] > 1 )
                {
                    $storytext .= "<br /><br />" . stripslashes($S['bodytext']);
                }
                
                // Keep relative images usable in email, but remove every
                // hyperlink from the excerpt/body. Visible text and nested
                // content remain unchanged.
                $site_url = rtrim($_CONF['site_url'], '/');
                $storytext = preg_replace('/src="\/([^\"]*)"/i', 'src="' . $site_url . '/$1"', $storytext);
                $storytext = preg_replace('/<a\b[^>]*>/i', '', $storytext);
                $storytext = preg_replace('/<\/a\s*>/i', '', $storytext);
                $storytext = HELLO_emailSafeImages($storytext);
 
                $mailtext .= '<div style="font-size:14px; margin-bottom:15px;">' . $storytext . '</div>';
            }
 
            $mailtext .= '<div><a href="' . $story_url . '" style="display:inline-block; padding:8px 15px; background-color:#2980b9; color:#ffffff; text-decoration:none; border-radius:4px; font-size:13px;">' . $LANG_HELLO01['read_article'] . stripslashes($S['title']) . '</a></div>';
            $mailtext .= '</div>';
        }
        
        $mailtext .= '</div>'; // Close body padding div
 
        $mailto = addslashes($U['email']);
 
        if ($_CONF['site_mail'] !== $_CONF['noreply_mail']) {
            $mailfrom = $_CONF['site_mail']; // Force site_mail as sender to avoid No-Reply
            // $mailtext .= LB . LB . (isset($LANG04[159]) ? $LANG04[159] : '');
        } else {
            $mailfrom = $_CONF['site_mail'];
        }
		
        $explication = '<div style="background-color:#f9f9f9; padding:20px; text-align:center; font-size:12px; color:#95a5a6; border-top:1px solid #eeeeee;">';
        $explication .= sprintf($LANG_HELLO01['why_email'], $_CONF['site_name']);

        if ($test_mode) {
            // Provide a safe, active unsubscribe simulation for administrators.
            // test=1 is handled end-to-end by unsubscribe.php and never writes
            // subscriber preferences or unsubscribe statistics.
            $explication .= '<br><strong>' . $LANG_HELLO01['test_email_footer'] . '</strong><br>';
            $maillink = HELLO_unsubscribeLink($U['uuid'], $U['email'], 0, true);
            $maillink = str_replace('</td></tr></table>', '</div></div></div></body></html>', $maillink);
        } else {
            $explication .= ' &bull; ';
            $maillink = HELLO_unsubscribeLink($U['uuid'], $U['email'], $new_hello_id);
            $maillink = str_replace('</td></tr></table>', '</div></div></div></body></html>', $maillink);
        }

        $tracking_pixel = '';
        if (!isset($_HE_CONF['track_opens']) || (bool) $_HE_CONF['track_opens']) {
            if ($test_mode) {
                $tracking_pixel = '<img src="' . $_CONF['site_url'] . '/hello/track.php?h=0&amp;u='
                    . $U['uuid'] . '&amp;test=1&amp;k=digest" width="1" height="1" alt="" style="display:none;" />';
            } else {
                $tracking_pixel = '<img src="' . $_CONF['site_url'] . '/hello/track.php?h=' . $new_hello_id
                    . '&amp;u=' . $U['uuid'] . '" width="1" height="1" alt="" style="display:none;" />';
            }
        }
        $uid = (int) $U['uuid'];
        
        if (!$test_mode) {
		    //Record in the hello queue
		    $mailtext = addslashes($mailtext . $explication . $maillink . $tracking_pixel);
            
		    $sql_ajout_hello = "INSERT INTO {$_TABLES['hello_queue']} (expediteur, destinataire, creation, hello_id, subject, content, priority, uid) VALUES ('$mailfrom', '$mailto', '$creation', '$new_hello_id', '$subject', '$mailtext', '0', '$uid')";
		
		    if (DB_query ($sql_ajout_hello)) {
			    $successes = $successes + 1;
		    } else {
			    $failures = $failures + 1;
		    }
        } else {
            // Send directly for tests without queuing, but exercise the same
            // click and open endpoints in an isolated test namespace.
            $mailtext_direct = $mailtext . $explication . $maillink . $tracking_pixel;
            $track_clicks = !isset($_HE_CONF['track_clicks']) || (bool) $_HE_CONF['track_clicks'];
            if ($track_clicks) {
                $mailtext_direct = HELLO_rewriteTrackedLinks($mailtext_direct, 0, $uid, true, 'digest');
            }
            $mailtext_direct = HELLO_prepareHtmlForEmail($mailtext_direct);
            $real_subject = stripslashes($subject);
            $real_mailto = $U['email'];
            
            if (HELLO_mail($real_mailto, $real_subject, $mailtext_direct, $mailfrom, true, 3)) {
                $successes = $successes + 1;
            } else {
                $failures = $failures + 1;
            }
        }
    }
	
	$retval = '';
	
	$retval = '';
	
	if ($successes >= 0) {
        if ($test_mode) {
            $retval .= "<br><br><strong style='color:red;'>[MODE TEST] " . $successes . " e-mail(s) traitÃ©(s). Debug: $nrows utilisateurs trouvÃ©s, le dernier avait $nsrows articles, $failures erreurs.</strong><br><br>";
        } else {
            $retval .= $successes . ' ' . $LANG_HELLO01['email_schedule'];
        }
	} 
	if ($failures > 0) {
	    $retval .= 'Oups... There was ' . $failures . ' failure(s)';
	}
	
	if (!$test_mode) {
	    //Update number of hello to send
        DB_query( "UPDATE {$_TABLES['hello']} SET quantity = '$successes' WHERE hello_id = '$new_hello_id'" );
	
	    if ($online) {
	        echo COM_refresh($_CONF['site_admin_url'] . '/plugins/hello/read_email.php?msg=digest_queued');
	        exit;
        }
    }
}

// Return unsubscribe link for specific user
function HELLO_unsubscribeLink($uid, $email, $hello_id = 0, $test_mode = false)
{
    global $_CONF, $LANG_HELLO01;

    $hash = MD5($email . "fFersh66");
    $uid = (int) $uid;
    $hello_id = (int) $hello_id;

    $url = $_CONF['site_url'] . '/hello/unsubscribe.php?h=' . $hash
        . '&amp;u=' . $uid . '&amp;hid=' . $hello_id;
    $label = $LANG_HELLO01['unsubscribe'];

    if ($test_mode) {
        $url .= '&amp;test=1';
        $label = isset($LANG_HELLO01['unsubscribe_test'])
            ? $LANG_HELLO01['unsubscribe_test']
            : $LANG_HELLO01['unsubscribe'] . ' (Test)';
    }

    $maillink = '<a href="' . $url
        . '" style="color: #777; text-decoration: underline;">' . $label . '</a>';
    $maillink .= '</td></tr></table>';

    return $maillink;
}

//fire daily digest
function plugin_runScheduledTask_hello() {

    global $_CONF, $_TABLES;
	
	$lastrun = DB_getItem ($_TABLES['vars'], 'value', "name = 'lastemailedstories'");
	$sql = "SELECT sid FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= NOW() AND date >= '{$lastrun}'";
    $result = DB_query ($sql);
    $count = DB_numRows ($result);
    
	if ($_CONF['emailstories'] == 1 && $count > 0) {
	    HELLO_emailUserTopics(false);
	}
		// Prepare newsletter
}



// Hello CRON every 2 minutes (120 seconds)
/*
global $_PLUGINS;

if (in_array('hello', $_PLUGINS)) {

	$last_run = DB_getItem($_TABLES['vars'],'value',"name='last_hello_run'");

	if($last_run == '') {
		//set default value
		$next_run = time() - (120) ;
		DB_query("INSERT INTO {$_TABLES['vars']} SET name = 'last_hello_run', value = '$next_run'");
		$last_run = $next_run;
	}
	// every 120 secondes
	if ($last_run + 120 <= time()) {
		DB_query("UPDATE {$_TABLES['vars']} SET value=UNIX_TIMESTAMP() WHERE name='last_hello_run'");
		HELLO_send_hello ();
	}
}
*/
function plugin_check_hello()
{
    global $_TABLES, $_HE_CONF;

    // Default to true (Option B) if the configuration hasn't been saved yet by the admin
    $auto = isset($_HE_CONF['enable_auto_cron']) ? (bool) $_HE_CONF['enable_auto_cron'] : true;

    if ($auto) {
        $last_run = DB_getItem($_TABLES['vars'], 'value', "name='hello_last_cron'");
        // Only run every 2 minutes maximum
        if (empty($last_run) || $last_run < time() - 120) {
            DB_query("REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('hello_last_cron', '" . time() . "')");
            HELLO_send_hello();
        }
    }
}

/**
 * Send email bypassing COM_mail in Geeklog 2.2.0+ 
 * 
 * In Geeklog 2.2.0+, COM_mail() forcefully overwrites the $from address with $_CONF['noreply_mail'] 
 * if they are different, and appends a "do not reply" footer. 
 * For a marketing tool, we want the actual sender.
 */
function HELLO_mail($to, $subject, $message, $from = '', $html = false, $priority = 0, $optional = null) {
    global $_CONF;
    
    // Extract raw email if format is "Name <email@domain.com>"
    if (is_string($to) && preg_match('/<\s*([^>]+)\s*>$/', trim($to), $matches)) {
        $to = $matches[1];
    }
    if (is_string($from) && preg_match('/<\s*([^>]+)\s*>$/', trim($from), $matches)) {
        $from = $matches[1];
    }

    $to = trim($to);
    $from = trim($from);
    if (empty($from)) {
        $from = $_CONF['site_mail'];
    }

    if (COM_isEmail($to)) {
        if ($html && class_exists('\\Geeklog\\Mail')) {
            $body = array($message, HELLO_tagToPlain($message));
            return \Geeklog\Mail::send($to, $subject, $body, $from, true, $priority, $optional);
        }
        return COM_mail($to, $subject, $message, $from, $html, $priority, $optional);
    }
    
    return false;
}

?>
