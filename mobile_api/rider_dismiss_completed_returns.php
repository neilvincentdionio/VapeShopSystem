<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/return_refund_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);

try {
    $db = mobile_db();
    $user = mobile_require_rider($db, $email);
    $riderId = (int) $user['id'];
    $count = mobile_dismiss_rider_completed_returns($db, $riderId);

    json_response(true, $count > 0
        ? 'Cleared ' . $count . ' completed return(s) from your list.'
        : 'No completed returns to clear.', ['dismissed' => $count], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while clearing completed returns.', null, 500);
}
