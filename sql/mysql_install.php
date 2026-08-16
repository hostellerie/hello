<?php

/**
* @package hello
*/
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | Installation SQL                                                          |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2016-2026 by the following authors:                         |
// |                                                                           |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                    |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net    |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com            |
// |          Dirk Haun         - dirk AT haun-online DOT de                   |
// |          Trinity Bays      - trinity93 AT gmail DOT com                   |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is licensed under the terms of the GNU General Public License|
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                      |
// | See the GNU General Public License for more details.                      |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//


//database queries
$_SQL[] = "
CREATE TABLE {$_TABLES['hello']} (
  hello_id int(11) NOT NULL auto_increment,
  subject varchar(100) NOT NULL default '',
  creation varchar(12) NOT NULL default '',
  email_group varchar(50) NOT NULL default '',
  quantity int(11) NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  content text NOT NULL,
  PRIMARY KEY  (hello_id)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['hello_queue']} (
  id int(11) NOT NULL auto_increment,
  expediteur varchar(100) NOT NULL default '',
  destinataire varchar(100) NOT NULL default '',
  creation varchar(12) NOT NULL default '',
  hello_id int(11) NOT NULL default '0',
  subject varchar(100) NOT NULL default '',
  content blob NOT NULL,
  priority tinyint(1) default 0,
  uid mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (id)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['hello_stats']} (
  stat_id int(11) NOT NULL auto_increment,
  hello_id int(11) NOT NULL default '0',
  uid mediumint(8) NOT NULL default '0',
  sent tinyint(1) NOT NULL default '0',
  opened tinyint(1) NOT NULL default '0',
  unsubscribed tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (stat_id),
  UNIQUE KEY hello_uid (hello_id, uid)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['hello_urls_clicked']} (
  click_id int(11) NOT NULL auto_increment,
  hello_id int(11) NOT NULL default '0',
  uid mediumint(8) NOT NULL default '0',
  url varchar(255) NOT NULL default '',
  click_time datetime NOT NULL,
  PRIMARY KEY  (click_id),
  KEY hello_id (hello_id),
  KEY uid (uid)
) ENGINE=MyISAM
";


$_SQL[] = "
CREATE TABLE {$_TABLES['hello_links']} (
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
) ENGINE=MyISAM
";

?>