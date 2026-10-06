<?php

/**
* @package hello
*/
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.3.0                                                        |
// +---------------------------------------------------------------------------+
// | install_defaults.php                                                      |
// |                                                                           |
// | Initial Installation Defaults used when loading the online configuration  |
// | records. These settings are only used during the initial installation     |
// | and not referenced any more once the plugin is installed.                 |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2016-2026 by the following authors:                         |
// |                                                                           |
// | Authors: Ben        - ben AT geeklog DOT fr                               |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'install_defaults.php') !== false) {
    die('This file can not be used on its own!');
}

/*
 * hello default settings
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 *
 */
  
global $_HE_DEFAULT;
$_HE_DEFAULT = array();

$_HE_DEFAULT['max_email'] = 10;
$_HE_DEFAULT['hourly_limit'] = 150;
$_HE_DEFAULT['track_clicks'] = 1;
$_HE_DEFAULT['track_opens'] = 1;
$_HE_DEFAULT['enable_auto_cron'] = 0;
$_HE_DEFAULT['digest_item_limit'] = 15;


/**
* Initialize hello plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist. 
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_hello()
{
    global $_CONF, $_HE_DEFAULT;

    $c = config::get_instance();
    if (!$c->group_exists('hello')) {

        $c->add('sg_0', NULL, 'subgroup', 0, 0, NULL, 0, true, 'hello');
        $c->add('tab_main', NULL, 'tab', 0, 0, NULL, 0, true, 'hello', 0);
        $c->add('fs_01', NULL, 'fieldset', 0, 0, NULL, 0, true, 'hello', 0);
        $c->add('max_email', $_HE_DEFAULT['max_email'],
                'text', 0, 0, NULL, 10, true, 'hello', 0);
        $c->add('hourly_limit', $_HE_DEFAULT['hourly_limit'],
                'text', 0, 0, NULL, 20, true, 'hello', 0);
        $c->add('track_clicks', $_HE_DEFAULT['track_clicks'],
                'select', 0, 0, 0, 30, true, 'hello', 0);
        $c->add('track_opens', $_HE_DEFAULT['track_opens'],
                'select', 0, 0, 0, 40, true, 'hello', 0);
        $c->add('enable_auto_cron', $_HE_DEFAULT['enable_auto_cron'],
                'select', 0, 0, 0, 50, true, 'hello', 0);
        $c->add('digest_item_limit', $_HE_DEFAULT['digest_item_limit'],
                'text', 0, 0, NULL, 60, true, 'hello', 0);
    }

    return true;
}

?>