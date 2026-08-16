<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | track.php
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

$uid = isset($_GET['u']) ? (int) $_GET['u'] : 0;
$hello_id = isset($_GET['h']) ? (int) $_GET['h'] : 0;
$test_mode = isset($_GET['test']) && (int) $_GET['test'] === 1;
$test_context = isset($_GET['k']) && $_GET['k'] === 'digest' ? 'digest' : 'campaign';

if ((!isset($_HE_CONF['track_opens']) || (bool) $_HE_CONF['track_opens']) && $uid > 1) {
    if ($test_mode && $hello_id === 0) {
        HELLO_recordTestTracking($uid, $test_context, 'open');
    } else if (!$test_mode && $hello_id > 0) {
        DB_query("INSERT INTO {$_TABLES['hello_stats']} (hello_id, uid, opened) "
            . "VALUES ($hello_id, $uid, 1) ON DUPLICATE KEY UPDATE opened = 1");
    }
}

// Return a 1x1 transparent GIF without allowing shared caches to hide opens.
header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
exit;
?>