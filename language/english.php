<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.2.1                                                        |
// +---------------------------------------------------------------------------+
// | english.php
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

// Language for plugin users
$LANG_HELLO01 = array(
    'plugin_name'             => 'Hello',
    'overview'                => 'The Hello Plugin allows you to compose, schedule, and send targeted HTML email campaigns to your user groups. It features full CRM capabilities including variable personalization, Open & Click tracking, and automatic RFC 8058 One-Click Unsubscribe compliance. The plugin also handles hourly sending quotas automatically to protect your server reputation.',
	'email_save'              => 'Email Save',
	'email_schedule'          => 'email(s) scheduled.',
	'ready_to_send'           => '(ready to be sent)',
    'email_sent'              => 'email(s) sent',
    'why_email'               => 'You are receiving this email as a member of %s',
    'sender'                  => 'Sender',
    'unsubscribe'             => 'Unsubscribe',
    'unsub_confirm_msg'       => 'Please confirm that you no longer want to receive these emails.',
    'unsub_confirm_btn'       => 'Confirm unsubscribe',
    'email_fail'              => 'failed email(s)',
	'menu_label'              => 'Menu',
	'homepage_label'          => 'Hello',
	'send_email_group'        => 'New',
    'manual'                  => 'Queue',
	'manual_intro'            => 'This page allows you to manually trigger the email queue if you do not have a cron job set up. Click the button below to process the next batch of emails.',
	'configuration'           => 'Config',
	'read_email'              => 'Campaigns',
    'bounces'                 => 'Bounces',
	'see_email'               => 'See',
	'id'                      => 'ID',
	'subjet'                  => 'Subject',
    'creation'                => 'Date',
    'group'                   => 'Group/Source',
    'quantity'                => 'Quantity',
	'email_display'           => '',
	'email'                   => 'Email',
    'max'                     => 'max (see config to change this)',
    'send_next'               => 'Send emails now',
	'import_message'          => 'Select the user group to target. Members who have unsubscribed will be automatically excluded from this mailing.',
	'select_file'             => 'Select a .csv file',
	'csv_file'                => 'csv file',
	'separator'               => 'Select delimiter',
	'hello_sent'              => 'Hello sent to',
    'cron_title'              => 'Scheduled Task (CRON) Configuration',
    'cron_desc'               => 'To automate the sending of the email queue, add the following command to your server\'s task scheduler (CRON). Note: Depending on your server (e.g., cPanel), the PHP executable path might be <code>/usr/local/bin/php -q</code> instead of <code>/usr/bin/php</code>.',
    'cron_note'               => 'Run this script using the PHP CLI. You no longer need a secret key because it cannot be executed from a web browser.',
	'contacts'                => 'contacts',
    'crm_dashboard'           => 'CRM',
    'back_to_list'            => 'Back to list',
    'back_to_campaign'        => 'Back to campaign',
    'back_to_campaign_stats'  => 'Back to campaign statistics',
    'back_to_subscribers'     => 'Back to subscribers list',
    'emails_received_by'      => 'Emails received by ',
    'emails_opened_by'        => 'Emails opened by ',
    'status_updated_success'  => 'Status updated successfully.',
    'emails_received'         => 'Emails Received',
    'force_unsubscribe'       => 'Force Unsubscribe',
    'resubscribe'             => 'Resubscribe',
    'col_username'            => 'Username',
    'col_email'               => 'Email',
    'col_subscription'        => 'Subscription',
    'col_opens'               => 'Opens',
    'col_clicks'              => 'Clicks',
    'col_action'              => 'Action',
	'mdigest'                 => 'Manual Digest',
	'ddigest'                 => 'Daily Digest',
    'doc_title'               => 'Hello Plugin Documentation & Usage',
    'doc_send_title'          => 'How to send a campaign',
    'doc_send_body'           => '<ul><li>Go to the <strong>New email</strong> tab.</li><li>Select the target group. Unsubscribed users will be ignored automatically.</li><li>Write your message. You can use HTML if the advanced editor is enabled.</li><li>Click Send. Messages are first placed in a <strong>queue</strong>.</li></ul>',
    'doc_crm_title'           => 'CRM Dashboard and Analytics (Subscribers, Opens, Clicks, Bounces)',
    'doc_crm_body'            => '<p>The plugin includes a full analytics system:</p>
                                  <ul>
                                  <li><strong>CRM Dashboard (Subscribers):</strong> Displays all users, the number of emails they received, opened, and clicked. You can force subscribe/unsubscribe a member, and click on numbers to see the exact email history.</li>
                                  <li><strong>Campaign Statistics:</strong> The archive page displays open and click rates for each campaign. You will find detailed tables listing exactly who opened and who clicked which link.</li>
                                  <li><strong>Bounces Management:</strong> The Bounces tab allows you to quickly mass-unsubscribe emails that returned an error, simply by pasting a list of bounced emails.</li>
                                  </ul>',
    'doc_config_title'        => 'Configuration Options',
    'doc_config_body'         => '<ul><li><strong>max_email</strong> : Maximum number of emails sent per batch (to protect your server reputation).</li><li><strong>email_per_hour</strong> : Maximum number of emails sent per hour.</li><li><strong>sleep_email</strong> : Pause in seconds between each email sent in a batch.</li><li><strong>keep_email</strong> : Number of days to keep email archives before automatic deletion (0 = never delete).</li></ul>',
    'doc_mdigest_title'       => 'Manual Digest',
    'doc_support_link'        => 'Support Link',
    'stat_campaigns'          => 'Campaigns',
    'stat_sent'               => 'Emails Sent',
    'stat_open_rate'          => 'Open Rate',
    'stat_click_rate'         => 'Click Rate',
    'stat_members'            => 'Members',
    'stat_emails'             => 'With Email',
    'stat_subscribed'         => 'Subscribed',
    'test_sent'               => 'Test message sent to your email address (if articles were available)!',
    'btn_test'                => 'Send Test to Me',
    'btn_test_title'          => 'Send only to your address to check the rendering',
    'queue_empty'             => 'Queue empty (No email waiting to be sent)',
    'queue_status'            => 'Queue Status',
    'queue_remaining'         => 'emails remaining in queue',
    'queue_sent_pct'          => '% sent',
    'top_readers'             => 'Top 5 Readers (Opens)',
    'top_opens'               => 'opens',
    'top_no_data'             => 'Not enough data yet.',
    'top_domains'             => 'Top 5 Domains',
    'bounces_title'           => 'Bounces Management',
    'bounces_desc'            => 'Import emails that bounced here to unsubscribe them.',
    'bounces_success'         => '%d user(s) successfully unsubscribed.',
    'bounces_warning'         => '%d email address(es) not found in the database.',
    'bounces_empty'           => 'No valid email address found in your input.',
    'bounces_import_title'    => 'Manual Bounces Import',
    'bounces_import_desc'     => 'Paste below the list of email addresses that bounced (Hard Bounces). You can separate addresses with a <strong>comma</strong> or a <strong>line break</strong>.',
    'bounces_btn'             => 'Unsubscribe these users',
    'status_actions'          => 'Status / Actions',
    'status_active'           => 'Active',
    'status_paused'           => 'Paused',
    'status_finished'         => 'Finished',
    'action_pause'            => 'Pause',
    'action_resume'           => 'Resume',
    'action_stop'             => 'Stop',
    'action_stop_confirm'     => 'Are you sure you want to delete the remaining emails for this campaign? This action is definitive.',
    'emails_remaining'        => 'remaining',
    'doc_mdigest_body'        => '<ul><li>Allows you to manually send a digest of recent stories to users. This requires <code>$_CONF[\'emailstories\'] = 1;</code> to be enabled in your site configuration.</li></ul>',
    'doc_ddigest_title'       => 'Daily Digest Maintenance',
    'doc_ddigest_body'        => '<ul><li>Allows you to reset or manage the daily digest scheduler.</li></ul>',
    'access_denied'           => 'Access Denied',
    'access_denied_msg'       => 'You are illegally trying access one of the Manual Digest administration pages.  Please note that all attempts to illegally access this page are logged',
    'installation_failed'     => 'Installation Failed',
    'installation_failed_msg' => 'The installation of the Manual Digest plugin failed.  Please see your Geeklog error.log file for diagnostic information.',
    'uninstall_failed'        => 'Uninstall Failed',
    'uninstall_failed_msg'    => 'The uninstall of the Manual Digest plugin failed.  Please see your Geeklog error.log file for diagnostic information.',

    'digest_sent'             => 'Digest has been sent. <a href="' . $_CONF['site_admin_url'] . '/plugins/hello/index.php">Back</a>.',
    'digest_intro_email'      => 'Hello %s. Here are the latest articles published on the site %s.',
    'digest_intro'            => 'This function allows you to inform your site members of the publication of a new article. It is independent of the automated cronjob function and allows you to send an additional notification whenever you want. Click the "Send" button below (only available if you have new articles to report) to send the notice of publication of a new article to members who want it.',
    'digest_last_sent'        => 'Last Digest sent:',
    'never'                   => '(never)',
    'no_stories'              => '<b>No new stories found</b>',
    'num_stories'             => '<b>%d</b> stories will be sent',
	'num_stories_digest'      => '%d stories have been sent via manual digest',
    'send_button'             => 'Send!',
    'not_enabled1'            => '<strong>Warning:</strong> Daily Digest is not enabled. Makesure you have',
    'not_enabled2'            => 'in your config.',

    'search_text'             => 'Search for a user name, email address or user id.',
    'search_button'           => 'Search',
    'new_search'              => 'New Search',
    'inspect_text'            => "Click on the user name to inspect the user's daily digest settings.",
    'uid_not_found'           => 'There is no user account with user id %d.',
    'not_found'               => 'There were no matches for <b>%s</b>.',
    'try_again'               => 'Please try again.',
    'user'                    => 'User',
    'topics'                  => 'Topics',
    'all_topics'              => 'all topics',
    'no_topics'               => 'none',
    'reset_button'            => 'Reset',
    'success'                 => 'Daily Digest settings for user <b>%s</b> have been reset.',
    'block_headline'          => 'Daily Digest Maintenance',
    'digest_reset'            => 'Digest has been reset. <a href="' . $_CONF['site_admin_url'] . '/plugins/hello/index.php">Back</a>.',
    'explain_reset'           => 'If you don\'t want the outstanding stories to be sent, use the "Reset" button.',

    'forums'                  => 'Forums',
    'no_forums'               => 'None',
    'forum_topics'            => 'forum topics',
    'inst_index'              => 'Manage the manual dispatch of the daily stories digest and search for members. Documentation is available at the bottom of this page.',
    'inst_group'              => 'Write and send a new targeted email campaign to a group of users.',
    'inst_read'               => 'View your campaign archives and access detailed statistics.',
    'inst_crm'                => 'Analyze subscriber engagement, open rates and clicks.',
    'inst_manual'             => 'Check and manually send scheduled emails in the queue.',
    'read_article'            => 'Read the article: ',
    'unsub_error_title'       => 'Unsubscribe Error',
    'unsub_error_heading'     => 'Action not allowed',
    'unsub_admin_error'       => 'The main site administrator cannot unsubscribe via this link. Please modify your preferences directly in your account.',
    'resub_title'             => 'Resubscription Successful',
    'resub_success'           => 'You have been successfully resubscribed. You will receive our emails again.',
    'unsub_title'             => 'Unsubscribed',
    'unsub_success_heading'   => 'Unsubscribed Successfully',
    'unsub_success_msg'       => 'Your unsubscription has been taken into account. We will no longer send you these emails. <br><br><em>Note that you can reactivate these notifications at any time in your account settings.</em>',
    'unsub_mistake'           => 'Clicked by mistake?',
    'resub_btn'               => 'Resubscribe in one click',
    'invalid_link_title'      => 'Invalid link',
    'invalid_link_msg'        => 'Invalid or expired unsubscribe link.',
    'test_email_success'      => 'Test email successfully sent',
    'test_email_footer'       => 'TEST MESSAGE — The unsubscribe link below is a simulation. You can test the complete unsubscribe flow without changing any subscriber preference.',
    'unsubscribe_test'        => 'Unsubscribe (Test)',
    'unsub_test_warning'      => 'TEST MODE — This is a simulated unsubscribe. Confirming will not change your subscription or email preferences.',
    'unsub_test_success'      => 'TEST MODE — The unsubscribe workflow completed successfully, but no subscription or email preference was changed.',
    'resub_test_success'      => 'TEST MODE — The resubscribe workflow completed successfully, but no subscription or email preference was changed.',
    'test_email_failed'       => 'The test email could not be sent. Please check the Geeklog error log and your mail configuration.',
    'manual_paused_note'      => 'Note: %d additional emails are paused or scheduled for later and will not be sent now.',
    'crm_unique_clicks'       => 'unique click(s) on: ',
    'crm_old_campaign'        => 'Old Campaign',
    'crm_old_campaign_desc'   => 'Detailed statistics (opens, clicks) were not recorded for campaigns sent before version 2.2.0 of the plugin. This campaign was sent to %d subscribers.',
    'crm_click_details'       => 'Click Details:',
    'crm_yes'                 => 'Yes',
    'crm_no'                  => 'No',
    'crm_clicks_count'        => 'click(s)',
    'sub_active'              => 'Active',
    'sub_unsubscribed'        => 'Unsubscribed',
    'hourly_quota_reached'    => 'Hourly quota reached (%d). Please wait for the next hour to send more.',
    'crm_unique_clickers'     => 'Unique Clickers: ',
    'crm_unsubs'              => 'Unsubscribes: ',
    'crm_view_table'          => 'View detailed click table for this campaign',
    'crm_detail_campaign'     => 'Click details for campaign #',
    'crm_user_history'        => 'Engagement history for user #',
    'crm_user'                => 'User',
    'crm_email'               => 'Email',
    'crm_clicked_url'         => 'Clicked URL',
    'crm_click_date'          => 'Click Date',
    'crm_campaign_id'         => 'Campaign (ID)',
    'crm_subject'             => 'Subject',
    'crm_campaign'            => 'Campaign',
    'crm_send_date'           => 'Send Date',
    'crm_opened'              => 'Opened',
    'crm_click'               => 'Click',
    'crm_no_clicks'           => 'No clicks recorded yet.',
    'crm_campaign_stats'      => 'Campaign Statistics:',
    'crm_sent'                => 'Sent to',
    'crm_subscribers'         => 'subscribers',
    'crm_unique_opens'        => 'Unique Opens',
    'crm_view_opens'          => 'View detailed opens table for this campaign',
    'crm_detail_opens'        => 'Open details for campaign #'
);

$LANG_configsections['hello'] = array(
    'label' => 'Hello',
    'title' => 'Hello Configuration'
);

$LANG_confignames['hello'] = array(
    'max_email' => 'Number of emails to send per run <abbr title="Maximum number of messages processed during one queue run. This is a batch size, not the hourly sending limit.">?</abbr>',
    'hourly_limit' => 'Strict hourly maximum limit (Throttling) <abbr title="Hard safety limit for the total number of newsletter messages sent during one hour. Hello reduces the batch automatically when this limit is close to being reached. Set to 0 only if you intentionally want no hourly limit.">?</abbr>',
    'track_clicks' => 'Track clicks with short first-party links <abbr title="Replaces eligible links with short links on this site so Hello can count clicks. Disable this if you prefer direct destination URLs and no click statistics.">?</abbr>',
    'track_opens' => 'Track newsletter opens with a 1×1 pixel <abbr title="Adds a tiny invisible image to estimate newsletter opens. Open counts are approximate because some email clients block images or use privacy proxies.">?</abbr>',
);

$LANG_configsubgroups['hello'] = array(
    'sg_0' => 'Main Settings',
);

$LANG_fs['hello'] = array(
    'fs_01' => 'Hello plugin'
);

$LANG_configselects['hello'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE)
);
?>