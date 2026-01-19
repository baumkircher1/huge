<?php

/**
 * Class CaptchaModel
 *
 * This model class handles Google reCAPTCHA v2 verification.
 * @see https://developers.google.com/recaptcha/docs/verify
 */
class CaptchaModel
{
    /**
     * Verifies the reCAPTCHA response with Google's API
     *
     * @param string $recaptchaResponse The g-recaptcha-response from the form
     * @return bool True if verification successful, false otherwise
     */
    public static function checkCaptcha($recaptchaResponse)
    {
        if (empty($recaptchaResponse)) {
            return false;
        }

        $secretKey = Config::get('RECAPTCHA_SECRET_KEY');

        // Prepare POST data for Google verification
        $postData = http_build_query(array(
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ));

        // Use file_get_contents with stream context (no cURL needed)
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            ),
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true
            )
        );

        $context = stream_context_create($options);
        $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);

        // Check for errors
        if ($response === false) {
            return false;
        }

        // Decode the JSON response
        $result = json_decode($response, true);

        // Return success status from Google's response
        return isset($result['success']) && $result['success'] === true;
    }
}
