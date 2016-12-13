<?php

namespace UserKit;

use Symfony\Component\Config\Definition\Exception\Exception;

$GLOBALS["DUMMY_USER"] = json_decode('{
    "username": null,
    "auth_type": "password",
    "disabled": false,
    "id": "usr_j3LB5QPAH8B9UD",
    "verified_email": null,
    "name": "Jane Smith",
    "created": 1473559820.5583501,
    "last_failed_login": 1473894606.9857299,
    "last_login": 1474134570.0548699,
    "verified_phone": null,
    "email": "jane.smith@example.com"
}', true);

$GLOBALS["DUMMY_USER_LIST"] = json_decode('{
    "next_page": null,
    "users": [
        {
            "username": null,
            "auth_type": "password",
            "disabled": false,
            "id": "usr_TgTbetyiSvuiIw",
            "verified_email": null,
            "name": null,
            "created": 1473544359.3973701,
            "last_failed_login": null,
            "last_login": null,
            "verified_phone": null,
            "email": "jack.doe@example.com"
        },
        {
            "username": null,
            "auth_type": "password",
            "disabled": false,
            "id": "usr_j3LB5QPAH8B9UD",
            "verified_email": null,
            "name": "Jane Smith",
            "created": 1473559820.5583501,
            "last_failed_login": 1473894606.9857299,
            "last_login": 1474134570.0548699,
            "verified_phone": null,
            "email": "jane.smith@example.com"
        }
    ]
}', true);

$GLOBALS["DUMMY_SESSION"] = json_decode('{
    "token": "usr_j3LB5QPAH8B9UD:faketoken123123|sha256",
    "expires_in_secs": 86398.979590000003,
    "refresh_after_secs": 77758.979659999997
}', true);

$GLOBALS["DUMMY_SUCCESS"] = json_decode('{"success": true}', true);

$GLOBALS["DUMMY_VERIFIED_PHONE_SUCCESS"] = json_decode('{
    "verified": true,
    "verified_phone_token": "hIFg38faFhBLSx87fah9p"
}', true);

$GLOBALS["DUMMY_VERIFIED_EMAIL_SUCCESS"] = json_decode('{
    "verified": true,
    "verified_email_token": "aF9phIFghBfaLSx8h738f"
}', true);

$GLOBALS["DUMMY_VERIFIED_EMAIL_OR_PHONE_FOR_USER_SUCCESS"] = json_decode(
    '{"verified": true}', true);

$GLOBALS["DUMMY_INVITE_CREATE"] = json_decode('{
    "token_raw": "invt_efmdqzGf32u77a-fli38NZGgR4jwU8gFDsq8KzV",
    "app_id": "app_6fa64vtE",
    "from_user": null,
    "accepted": false,
    "id": "invt_efmdqzGf32u77a",
    "expires_secs": 604800,
    "to_email": "anne.doe@example.com",
    "invite_url": "https://api.userkit.io/hosted_widget?app=app_6fa64vtE&amp;invt=invt_efmdqzGf32u77a-fli38NZGgR4jwU8gFDsq8KzV",
    "created": 1474248184.9626,
    "extras": null,
    "accepted_user": null,
    "accepted_date": null
}', true);

$GLOBALS["DUMMY_INVITE"] = json_decode('{
    "expires_secs": 604800,
    "to_email": "anne.doe@example.com",
    "accepted": false,
    "accepted_date": null,
    "created": 1474248184.9626,
    "accepted_user": null,
    "from_user": null,
    "id": "invt_efmdqzGf32u77a",
    "extras": null,
    "app_id": "app_6fa64vtE"
}', true);

$GLOBALS["DUMMY_ACCEPTED_INVITE"] = json_decode('{
    "expires_secs": 604800,
    "to_email": "james.doe@example.com",
    "accepted": true,
    "accepted_date": 1474297100.8381801,
    "created": 1474296046.0643599,
    "accepted_user": "usr_wojdQ286VOvSzA",
    "from_user": null,
    "id": "invt_y4GQk4GwfMFrlD",
    "extras": null,
    "app_id": "app_6fa64vtE"
}', true);

$GLOBALS["DUMMY_INVITES_LIST"] = json_decode('{
    "next_page": null,
    "invites": [
        {
            "expires_secs": 604800,
            "to_email": "anne.doe@example.com",
            "accepted": false,
            "accepted_date": null,
            "created": 1474248184.9626,
            "accepted_user": null,
            "from_user": null,
            "id": "invt_efmdqzGf32u77a",
            "extras": null,
            "app_id": "app_6fa64vtE"
        },
        {
            "expires_secs": 604800,
            "to_email": "james.doe@example.com",
            "accepted": false,
            "accepted_date": null,
            "created": 1474296046.0643599,
            "accepted_user": null,
            "from_user": null,
            "id": "invt_y4GQk4GwfMFrlD",
            "extras": null,
            "app_id": "app_6fa64vtE"
        }
    ]
}', true);


class UserKitMock extends UserKit
{
    private function hasPrefix($hay, $needle)
    {
        return (substr($hay, 0, strlen($needle)) === $needle);
    }

    private function hasSuffix($hay, $needle)
    {
        return (substr($hay, -strlen($needle) ) === $needle);
    }

    public function request($method, $uri, $headers, $uri_params, $post_data)
    {
        // GET
        if ($method == 'GET') {
            if ($uri == '/users') {
                // List users
                return $GLOBALS['DUMMY_USER_LIST'];
            } else if ($uri == '/users/auth_token') {
                // Refresh session token
                return $GLOBALS['DUMMY_SESSION'];
            } else if ($this->hasPrefix($uri, '/users/')) {
                // Get a user
                return $GLOBALS['DUMMY_USER'];
            }
        }
        else if ($method == 'POST') {
            if ($uri == '/users') {
                return $GLOBALS['DUMMY_USER'];
            } else if ($uri == '/users/request_phone_verification_code') {
                // Request phone verification, returns success flag
                return $GLOBALS['DUMMY_SUCCESS'];
            } else if ($uri == '/users/request_email_verification_code') {
                // Request email verification, returns success flag
                return $GLOBALS['DUMMY_SUCCESS'];
            } else if ($uri == '/users/request_password_reset') {
                return $GLOBALS['DUMMY_SUCCESS'];
            } else if ($uri == '/users/password_reset_new_password') {
                return $GLOBALS['DUMMY_SUCCESS'];
            } else if ($uri == '/users/verify_phone') {
                // Verify phone, returns verification-success token
                return $GLOBALS['DUMMY_VERIFIED_PHONE_SUCCESS'];
            } else if ($uri == '/users/verify_email') {
                // Verify email, returns verification-success token
                return $GLOBALS['DUMMY_VERIFIED_EMAIL_SUCCESS'];
            } else if ($uri == '/users/login') {
                // Login returns a session token
                return $GLOBALS['DUMMY_SESSION'];
            } else if ($uri == '/users/logout') {
                // Logout returns a success flag
                return $GLOBALS['DUMMY_SUCCESS'];
            } else if ($this->hasPrefix($uri, '/users')) {
                // User endpoints containing the DUMMY_USER's id
                if (strpos($uri, $GLOBALS['DUMMY_USER']['id']) !== false)
                {
                    if ($this->hasSuffix($uri, '/disable')) {
                        // This is a set-disabled state request, returns user
                        $u = json_decode(json_encode($GLOBALS['DUMMY_USER']), true); // make a deep copy
                        $u['disabled'] = $post_data['disabled'];
                        return $u;
                    } else if ($this->hasSuffix($uri, '/auth_type')) {
                        // This is a set-auth-type request, returns success flag
                        return $GLOBALS['DUMMY_SUCCESS'];
                    } else if ($this->hasSuffix($uri, '/verify_phone_for_user')) {
                        // Verify phone for user, returns verified success flag
                        return $GLOBALS['DUMMY_VERIFIED_EMAIL_OR_PHONE_FOR_USER_SUCCESS'];
                    } else if ($this->hasSuffix($uri, '/verify_email_for_user')) {
                        // Verify email for user, returns verified success flag
                        return $GLOBALS['DUMMY_VERIFIED_EMAIL_OR_PHONE_FOR_USER_SUCCESS'];
                    } else if ($this->hasSuffix($uri, $GLOBALS['DUMMY_USER']['id'])) {
                        // This is an update
                        $u = json_decode(json_encode($GLOBALS['DUMMY_USER']), true); // make a deep copy
                        $u->update($post_data);
                        return $u;
                    }
                }
            }

            // Invites -----------------------------------------------------
            if ($this->hasPrefix($uri, '/invites')) {
                if ($uri == '/invites') {
                    if ($method == 'POST') {
                        return $GLOBALS['DUMMY_INVITE_CREATE'];
                    } else if ($method == 'GET') {
                        return $GLOBALS['DUMMY_INVITES_LIST'];
                    }
                } else if (($uri == '/invites/send') and ($method == 'POST')) {
                    return $GLOBALS['DUMMY_INVITE'];
                } else if (($uri == '/invites/accept') and ($method == 'POST')) {
                    return $GLOBALS['DUMMY_ACCEPTED_INVITE'];
                } else if ($this->hasSuffix($uri, $GLOBALS['DUMMY_INVITE']['id'])) {
                    return $GLOBALS['DUMMY_INVITE'];
                }
            }

            throw new UserKitError('{"error":{"message": "No matching URI."}}');
        }

        return null;
    }
}
