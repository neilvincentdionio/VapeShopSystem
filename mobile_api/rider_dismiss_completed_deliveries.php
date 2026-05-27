<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/rider_list_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);

try {
    $db = mobile_db();
    $user = mobile_require_rider($db, $email);
    $riderId = (int) $user['id'];
    $count = mobile_dismiss_rider_completed_deliveries($db, $riderId);

    json_response(true, $count > 0
        ? 'Cleared ' . $count . ' completed delivery(ies) from your list.'
        : 'No completed deliveries to clear.', ['dismissed' => $count], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while clearing completed deliveries.', null, 500);
}
