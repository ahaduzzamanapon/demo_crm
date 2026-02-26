<?php

if (!function_exists('sso_login')) {
    function sso_login($sso_url, $user_data, $shared_secret) {
        $user_data['hash'] = hash_hmac('sha256', http_build_query($user_data), $shared_secret);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $sso_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($user_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return json_encode(['success' => false, 'message' => curl_error($ch)]);
        }

        curl_close($ch);

        return $response;
    }
}
