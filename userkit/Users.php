<?php

namespace UserKit;

require_once("UserKit.php");


class UserManager
{
    private $parentOb = null;

    public function __construct(UserKit $parentOb) {
        $this->parentOb = $parentOb;
    }

    public static function filteredArray(array $arr)
    {
        $post_array = array();
        foreach($arr as $key => $val)
        {
            foreach (User::$USR_MUTABLE_FIELDS as $item)
            {
                if ($key == $item)
                {
                    $post_array[$key] = $val;
                    break;
                }
            }
        }

        return $post_array;
    }

    public function getCurrentUser($token) {
        try {
            return $this->getUserBySession($token);
        }
        catch (UserAuthenticationError $ex)
        {
        	// we only catch this error
        	// and all other errors are failures
        }

        return null;
    }

    // Create a new User, takes in a newly instantiated and filled out User object
    // which is then processed and sent to the server to be created.
    // If successful the User object that was passed in will get loaded with the new
    // user info from the server (overwriting any user properties that exist).
    public function createUser(array $arr) {
        $post_data = UserManager::filteredArray($arr);

        $res = $this->parentOb->request("POST", "/users", null, null, $post_data);
        return new User($this->parentOb, $res);
    }

    public function updateUser($userID, array $arr) {
        $post_data = UserManager::filteredArray($arr);

        $res = $this->parentOb->request("POST", "/users/" . $userID, null, null, $post_data);

        return new User($this->parentOb, $res);
    }

    // Login a User given the username and password
    // On success the Session is returned
    public function loginUser($username, $password, $code=null) {
        $res = $this->parentOb->request("POST", "/users/login", null, null, $code ? ["username" => $username, "password" => $password, "code" => $code] : ["username" => $username, "password" => $password]);

        return new Session($res);
    }

    // Logout this currently logged in User
    public function logoutUser($token) {
        $res = $this->parentOb->request("POST", "/users/logout", ["X-User-Token: " . $token], null, null);

        if($res['success'] != true) {
            throw new UserKitError('{"error":{"message": "Failed to logout, success != true"}}');
        }
    }

    // Get a User based on session token
    public function getUserBySession($token) {
        $res = $this->parentOb->request("GET", "/users/by_token", ["X-User-Token: " . $token], null, null);

        return new User($this->parentOb, $res);
    }

    // Get an arbitrary User based on the user_id
    public function getUser($user_id) {
    	try
    	{
			$res = $this->parentOb->request("GET", "/users/" . $user_id, null, null, null);
        }
        catch (ResourceNotFoundError $ex)
        {
        	// we only catch this error
        	// and all other errors are failures
        	return null;
        }

        return new User($this->parentOb, $res);
    }

    public function requestPasswordReset($username_or_email) {
        $res = $this->parentOb->request("POST", "/users/request_password_reset", null, null, ["username_or_email" => $username_or_email]);

        if($res['success'] != true) {
            throw new UserKitError('{"error": {"message": "Failed to request password reset (success != true)"}}');
        }
    }

    public function resetPassword($pw_reset_token, $new_password) {
        $this->parentOb->request("POST", "/users/password_reset_new_password", null, null, ["token" => $pw_reset_token, "password" => $new_password]);
    }

    // Get a list of Users for this app
    // Note if limit is not passed in, it isn't passed to the server. However the server may have
    // a hardcoded upper limit, so always assume there may be a next_page even without a limit set.
    // Note the next_page is a token gotten from the UserList that's returned from a previous call
    // to getUsers. You pass in the next_page token to get the next page in the full list.
    public function getUsers($limit=0, $next_page=null) {
        $params = array();
        if ($limit > 0) {
            $params['limit'] = $limit;
        }

        if ($next_page) {
            $params['next_page'] = $next_page;
        }

        $res = $this->parentOb->request("GET", "/users", null, $params, null);

        $user_list = new UserList();

        $rsUsers = $res['users'];
        if ($rsUsers) {
            for ($i = 0; $i < count($rsUsers); $i++) {
                $user_list->append(new User($this->parentOb, $rsUsers[$i]));
            }
        }

        $user_list->next_page = $res['next_page'];

        return $user_list;
    }

    public function setUserAuthType($user_id, $auth_type, $phone_number=null, $phone_token=null)
    {
        $post_data = ["auth_type" => $auth_type];
        if ($phone_number && $phone_token)
        {
            $post_data['phone'] = $phone_number;
            $post_data['phone_token'] = $phone_token;
        }

        $res = $this->parentOb->request("POST", "/users/$user_id/auth_type", null, null, $post_data);

        if($res['success'] != true)
        {
            throw new UserKitError('{"error":{"message": "Failed setUserAuthType, success != true"}}');
        }
    }

    public function requestPhoneVerificationCode($phone_number, $send_method="sms")
    {
       $res = $this->parentOb->request("POST", "/users/request_phone_verification_code", null, null, ["phone" => $phone_number, "send_method" => $send_method]);

        if($res['success'] != true)
        {
            throw new UserKitError('{"error":{"message": "Failed requestPhoneVerificationCode, success != true"}}');
        }
      }

    public function verifyPhone($phone_number, $verification_code)
    {
        $res = $this->parentOb->request("POST", "/users/verify_phone", null, null, ["phone" => $phone_number, "code" => $verification_code]);

        if($res['verified'] == true)
        {
            return $res['verified_phone_token'];
        }

        return null;
    }

    public function verifyPhoneForUser($user_id, $phone_number, $verification_code)
    {
        $res = $this->parentOb->request("POST", "/users/$user_id/verify_phone_for_user", null, null, ["phone" => $phone_number, "code" => $verification_code]);

        return $res['verified'];
    }

    public function requestEmailVerificationCode($email_address)
    {
        $this->parentOb->request("POST", "/users/request_email_verification_code", null, null, ["email" => $email_address]);
    }

    public function verifyEmail($email_address, $verification_code)
    {
        $res = $this->parentOb->request("POST", "/users/verify_email", null, null, ["email" => $email_address, "code" => $verification_code]);

        if($res['verified'])
        {
            return $res['verified_email_token'];
        }

        return null;
    }

    public function verifyEmailForUser($user_id, $email_address, $verification_code)
    {
        $res = $this->parentOb->request("POST", "/users/$user_id/verify_email_for_user", null, null, ["email" => $email_address, "code" => $verification_code]);

        return $res['verified'];
    }

    public function disableUser($user_id, $disabled=true) {
        $post_data = array();
        $post_data['disabled'] = $disabled;

        $res = $this->parentOb->request("POST", "/users/$user_id/disable", null, null, $post_data);

        return new User($this->parentOb, $res);
    }

    // Refreshes an Session for this already logged in User
    public function refreshSession($sessionToken) {

        $res = $this->parentOb->request("GET", "/users/auth_token", ["X-User-Token: " . $sessionToken], null, null);

        return new Session($res);
    }
}
