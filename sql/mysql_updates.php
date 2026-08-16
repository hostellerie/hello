<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | mysql_updates.php
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
$_UPDATES = array(
    '2.1.1' => array(
        "CREATE TABLE IF NOT EXISTS {$_TABLES['hello_stats']} (
          stat_id int(11) NOT NULL auto_increment,
          hello_id int(11) NOT NULL default '0',
          uid mediumint(8) NOT NULL default '0',
          sent tinyint(1) NOT NULL default '0',
          opened tinyint(1) NOT NULL default '0',
          unsubscribed tinyint(1) NOT NULL default '0',
          PRIMARY KEY  (stat_id),
          UNIQUE KEY hello_uid (hello_id, uid)
        ) ENGINE=MyISAM",
        
        "CREATE TABLE IF NOT EXISTS {$_TABLES['hello_urls_clicked']} (
          click_id int(11) NOT NULL auto_increment,
          hello_id int(11) NOT NULL default '0',
          uid mediumint(8) NOT NULL default '0',
          url varchar(255) NOT NULL default '',
          click_time datetime NOT NULL,
          PRIMARY KEY  (click_id),
          KEY hello_id (hello_id),
          KEY uid (uid)
        ) ENGINE=MyISAM"
    ),
    '2.2.0' => array(
        "CREATE TABLE IF NOT EXISTS {$_TABLES['hello_links']} (
          link_id int(11) NOT NULL auto_increment,
          token char(32) NOT NULL,
          hello_id int(11) NOT NULL default '0',
          uid mediumint(8) NOT NULL default '0',
          url text NOT NULL,
          created datetime NOT NULL,
          PRIMARY KEY (link_id),
          UNIQUE KEY token (token),
          KEY hello_id (hello_id),
          KEY uid (uid)
        ) ENGINE=MyISAM"
    )
);

?>