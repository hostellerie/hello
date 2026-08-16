<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | bounces.php
// |                                                                           |
// | Geeklog hello plugin file                                                 |
// +---------------------------------------------------------------------------+

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_hasRights('hello.edit')) {
    $display = COM_startBlock($MESSAGE[30], '', COM_getBlockTemplate('_msg_block', 'header'));
    $display .= $MESSAGE[36];
    $display .= COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
    COM_accessLog("User {$_USER['username']} tried to illegally access the hello bounces screen.");
    COM_output(COM_createHTMLDocument($display));
    exit;
}

require_once $_CONF['path_system'] . 'lib-admin.php';

$display = '';
$display .= hello_admin_menu($LANG_HELLO01['bounces_title'], $LANG_HELLO01['bounces_desc']);

if (isset($_POST['process_bounces']) && SEC_checkToken()) {
    $emails_raw = isset($_POST['bounced_emails']) ? $_POST['bounced_emails'] : '';
    
    // Normalize separators (commas, semicolons, newlines) to spaces, then split
    $emails_raw = str_replace(array(',', ';', "\r\n", "\n", "\r"), ' ', $emails_raw);
    $email_list = explode(' ', $emails_raw);
    
    $processed = 0;
    $not_found = 0;
    
    $table_pref = isset($_TABLES['user_attributes']) ? $_TABLES['user_attributes'] : $_TABLES['userprefs'];
    
    foreach ($email_list as $email) {
        $email = trim($email);
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_sql = DB_escapeString($email);
            
            // Find user id by email
            $uid_query = DB_query("SELECT uid FROM {$_TABLES['users']} WHERE email = '$email_sql'");
            
            if (DB_numRows($uid_query) > 0) {
                while ($row = DB_fetchArray($uid_query)) {
                    $uid = (int) $row['uid'];
                    DB_query("UPDATE $table_pref SET emailfromadmin = 0 WHERE uid = $uid");
                    $processed++;
                }
            } else {
                $not_found++;
            }
        }
    }
    
    if ($processed > 0) {
        $display .= COM_showMessageText(sprintf($LANG_HELLO01['bounces_success'], $processed), 'success');
    }
    if ($not_found > 0) {
        $display .= COM_showMessageText(sprintf($LANG_HELLO01['bounces_warning'], $not_found), 'warning');
    }
    if ($processed == 0 && $not_found == 0) {
        $display .= COM_showMessageText($LANG_HELLO01['bounces_empty'], 'warning');
    }
}

$display .= '<div style="background:#f9f9f9; padding: 20px; border:1px solid #ddd; border-radius:4px; margin-top:20px;">';
$display .= '<h3>' . $LANG_HELLO01['bounces_import_title'] . '</h3>';
$display .= '<p>' . $LANG_HELLO01['bounces_import_desc'] . '</p>';

$display .= '<form action="' . $_CONF['site_admin_url'] . '/plugins/hello/bounces.php" method="POST">';
$display .= '<textarea name="bounced_emails" style="width: 100%; height: 200px; padding: 10px; font-family: monospace;" placeholder="jean.dupont@example.com, marie@test.fr..."></textarea>';
$display .= '<br/><br/>';
$display .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . SEC_createToken() . '">';
$display .= '<input type="submit" name="process_bounces" value="' . $LANG_HELLO01['bounces_btn'] . '" class="uk-button uk-button-danger" style="padding: 10px 20px; background: #dc3545; color: white; border: none; cursor: pointer; border-radius: 3px;">';
$display .= '</form>';
$display .= '</div>';

$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
$display = COM_createHTMLDocument($display);
COM_output($display);

?>