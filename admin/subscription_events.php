<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | hello Plugin 2.3.0                                                        |
// +---------------------------------------------------------------------------+
// | subscription_events.php                                                   |
// |                                                                           |
// | Subscription and unsubscribe event history.                               |
// +---------------------------------------------------------------------------+

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_hasRights('hello.edit')) {
    $display = COM_startBlock($MESSAGE[30], '', COM_getBlockTemplate('_msg_block', 'header'));
    $display .= $MESSAGE[36];
    $display .= COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
    COM_accessLog("User {$_USER['username']} tried to illegally access Hello subscription history.");
    COM_output(COM_createHTMLDocument($display));
    exit;
}

$scope = isset($_GET['scope']) ? $_GET['scope'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

$where = array();
if ($scope === 'campaign' || $scope === 'digest') {
    $where[] = "e.scope = '" . DB_escapeString($scope) . "'";
}
if ($action === 'subscribe' || $action === 'unsubscribe') {
    $where[] = "e.action = '" . DB_escapeString($action) . "'";
}

$where_sql = empty($where) ? '' : ' WHERE ' . implode(' AND ', $where);

$sql = "SELECT e.event_id, e.uid, e.scope, e.action, e.source, e.hello_id, "
    . "e.details, e.created_at, u.username, u.email, h.subject "
    . "FROM {$_TABLES['hello_subscription_events']} e "
    . "LEFT JOIN {$_TABLES['users']} u ON u.uid = e.uid "
    . "LEFT JOIN {$_TABLES['hello']} h ON h.hello_id = e.hello_id "
    . $where_sql
    . " ORDER BY e.created_at DESC, e.event_id DESC LIMIT 200";

$result = DB_query($sql);

$display = hello_admin_menu(
    $LANG_HELLO01['subscription_events_title'],
    $LANG_HELLO01['subscription_events_intro']
);

$base = $_CONF['site_admin_url'] . '/plugins/hello/subscription_events.php';
$display .= '<form method="get" action="' . $base . '" style="margin-bottom:16px;">';
$display .= '<label>' . $LANG_HELLO01['sub_event_scope'] . ' ';
$display .= '<select name="scope">';
$display .= '<option value="">' . $LANG_HELLO01['sub_event_all'] . '</option>';
$display .= '<option value="campaign"' . ($scope === 'campaign' ? ' selected' : '') . '>'
    . $LANG_HELLO01['sub_event_campaign_scope'] . '</option>';
$display .= '<option value="digest"' . ($scope === 'digest' ? ' selected' : '') . '>'
    . $LANG_HELLO01['sub_event_digest_scope'] . '</option>';
$display .= '</select></label> ';

$display .= '<label>' . $LANG_HELLO01['sub_event_action'] . ' ';
$display .= '<select name="action">';
$display .= '<option value="">' . $LANG_HELLO01['sub_event_all'] . '</option>';
$display .= '<option value="subscribe"' . ($action === 'subscribe' ? ' selected' : '') . '>'
    . $LANG_HELLO01['sub_event_subscribe'] . '</option>';
$display .= '<option value="unsubscribe"' . ($action === 'unsubscribe' ? ' selected' : '') . '>'
    . $LANG_HELLO01['sub_event_unsubscribe'] . '</option>';
$display .= '</select></label> ';
$display .= '<button type="submit">Filter</button>';
$display .= '</form>';

$display .= '<div style="overflow-x:auto;"><table class="admin-list" style="width:100%; border-collapse:collapse;">';
$display .= '<thead><tr>';
$display .= '<th>' . $LANG_HELLO01['sub_event_date'] . '</th>';
$display .= '<th>' . $LANG_HELLO01['sub_event_user'] . '</th>';
$display .= '<th>' . $LANG_HELLO01['sub_event_scope'] . '</th>';
$display .= '<th>' . $LANG_HELLO01['sub_event_action'] . '</th>';
$display .= '<th>' . $LANG_HELLO01['sub_event_source'] . '</th>';
$display .= '<th>' . $LANG_HELLO01['sub_event_campaign'] . '</th>';
$display .= '<th>' . $LANG_HELLO01['sub_event_details'] . '</th>';
$display .= '</tr></thead><tbody>';

while ($row = DB_fetchArray($result)) {
    $user = !empty($row['username']) ? $row['username'] : ('UID ' . (int) $row['uid']);
    if (!empty($row['email'])) {
        $user .= ' (' . $row['email'] . ')';
    }

    $campaign = '';
    if ((int) $row['hello_id'] > 0) {
        $campaign = '#' . (int) $row['hello_id'];
        if (!empty($row['subject'])) {
            $campaign .= ' — ' . $row['subject'];
        }
    }

    $display .= '<tr>';
    $display .= '<td>' . htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') . '</td>';
    $display .= '<td>' . htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . '</td>';
    $display .= '<td>' . htmlspecialchars($row['scope'], ENT_QUOTES, 'UTF-8') . '</td>';
    $display .= '<td>' . htmlspecialchars($row['action'], ENT_QUOTES, 'UTF-8') . '</td>';
    $display .= '<td>' . htmlspecialchars($row['source'], ENT_QUOTES, 'UTF-8') . '</td>';
    $display .= '<td>' . htmlspecialchars($campaign, ENT_QUOTES, 'UTF-8') . '</td>';
    $display .= '<td>' . htmlspecialchars($row['details'], ENT_QUOTES, 'UTF-8') . '</td>';
    $display .= '</tr>';
}

$display .= '</tbody></table></div>';
$display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
COM_output(COM_createHTMLDocument($display));

?>