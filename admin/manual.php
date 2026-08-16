<?php

/**
* @package hello
*/
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | manual.php                                                                |
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

function HELLO_count_hello () {

    global $_CONF, $_TABLES, $_USER, $LANG_HELLO01, $_HE_CONF;
	
    $heure_script = date('YmdHi', time());
    $sql = "SELECT COUNT(*) FROM {$_TABLES['hello_queue']} q "
         . "INNER JOIN {$_TABLES['hello']} h ON q.hello_id = h.hello_id "
         . "WHERE q.creation <= " . $heure_script . " AND h.status = 0";
    $result = DB_query($sql);
    $row = DB_fetchArray($result);
    $hellos = (int) $row[0];
    
    $total_queue = (int) DB_count($_TABLES['hello_queue'], '1', '1');
    $paused_or_future = $total_queue - $hellos;
    
    $retval = '<p>' . $LANG_HELLO01['manual_intro'] . '</p>';
    $retval .= '<p><strong>' . $hellos . ' ' . $LANG_HELLO01['email_schedule'] . '</strong> ' . $LANG_HELLO01['ready_to_send'] . '</p>';
    
    if ($paused_or_future > 0) {
        $retval .= '<p><small><em>' . sprintf($LANG_HELLO01['manual_paused_note'], $paused_or_future) . '</em></small></p>';
    }
    
    if ($hellos > 0) {
        $token = SEC_createToken();
        $retval .= '<form action="' . $_CONF['site_admin_url'] . '/plugins/hello/manual.php" method="post">';
        $retval .= '<input type="hidden" name="action" value="go">';
        $retval .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '">';
        $retval .= '<button type="submit">' . $LANG_HELLO01['send_next'] . '</button> ';
        $retval .= $_HE_CONF['max_email'] . ' ' . $LANG_HELLO01['max'];
        $retval .= '</form>';
    }  
    
    return $retval;
}

// MAIN
$action = isset($_POST['action']) ? $_POST['action'] : '';

$display .= hello_admin_menu($LANG_HELLO01['manual'], $LANG_HELLO01['inst_manual']);

if ($action === 'go') {
    if (SEC_checkToken()) {
        $display .= HELLO_send_hello(true);
    } else {
        $display .= COM_showMessageText($MESSAGE[37], $MESSAGE[30]);
    }
} else {
    $display .= HELLO_count_hello ();
}

$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
$display = COM_createHTMLDocument($display);
COM_output($display);

?>