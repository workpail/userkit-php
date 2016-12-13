<?php

namespace UserKit;

use Symfony\Component\Config\Definition\Exception\Exception;

require_once('Email.php');
require_once('Emails.php');
require_once('Error.php');
require_once('Invite.php');
require_once('InviteList.php');
require_once('Invites.php');
require_once('Session.php');
require_once('User.php');
require_once('UserList.php');
require_once('Users.php');


class UserKit {
    private $apiKey = null;
    private $baseApiUrl = 'https://api.userkit.io/v1';
    private $authizationB64 = null;
    public $users = null;
    public $invites = null;
    public $emails = null;

    public function __construct($apiKey, $apiBaseUrl=null) {
        $this->apiKey = $apiKey;

        if ($apiBaseUrl == null)
        {
            $apiBaseUrl = 'https://api.userkit.io/v1';
        }
        else
        {
            $apiBaseUrl = $apiBaseUrl . '/v1';
        }

        $this->baseApiUrl = $apiBaseUrl;

        $this->users = new UserManager($this);
        $this->invites = new Invites($this);
        $this->emails = new EmailManager($this);
    }

    public function version() {
        return 1.0;
    }

    public function __toString() {
        // return a human readable string of ALL the object properties
        $obj = get_object_vars($this);
        unset($obj['users']);
        unset($obj['invites']);

        return json_encode($obj);
    }

    // A general wrapper method to the cURL calls to make a request to the backend
    // API server. POSTs are JSON bodies.
    // This method returns an JSON associative array as the single response
    public function request($method, $uri, $headers, $uri_params, $post_data)
    {
        $url = $this->baseApiUrl . $uri;
        if ($uri_params)
        {
            $url .= "?" . http_build_query($uri_params);
        }

        $curl = curl_init($url);

        if ($this->authizationB64 == null)
        {
            // perform initial encoding of the api key (user:secret)
            $this->authizationB64 = base64_encode("api:" . $this->apiKey);
        }

        if (!$headers)
        {
            // ensure we have an array to attach our stuff to...
            $headers = array();
        }
        $headers[] = 'Content-type: application/json';
        $headers[] = 'Authorization: Basic ' . $this->authizationB64;
        $headers[] = 'X-Escape: false'; // Don't escape returned JSON

        if ($post_data)
        {
            $postValue = (gettype($post_data) == 'string') ? $post_data : json_encode($post_data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postValue);

            $headers[] = 'Content-Length: ' . strlen($postValue);
        }
        else
        {
            // NOTE: there must always be a content length
            $headers[] = 'Content-Length: 0';
        }

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // enforce cert validation
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        // NOTE: CURLOPT_SSL_VERIFYSTATUS was only added in cURL 7.41.0. Available since PHP 7.0.7.
        // curl_setopt($curl, CURLOPT_SSL_VERIFYSTATUS, true);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_USERPWD, $this->apiKey);

        // FOR DEBUG ONLY
//         curl_setopt($curl, CURLOPT_VERBOSE, true);
//         curl_setopt($curl, CURLOPT_CERTINFO, true);

        $response = curl_exec($curl);

        if (!$response)
        {
            throw new UserKitError('{"error":{"message": "Failed to connect to ' . $url . '"}}');
        }

        // set some failed return values
        $status_code = -1;
        $response_body = null;

        try
        {
            $tmp = explode("\r\n\r\n", $response);
            $h = explode("\r\n", $tmp[0]);
            $status_code = intval(explode(" ", $h[0])[1]);
            $response_body = $tmp[1];
        }
        catch (Exception $ex)
        {
            print "\nIN MY ERROR" . $ex;
        }

        curl_close($curl);

        if ($status_code == 200) {
            return json_decode($response_body, true);
        }
        else if ($status_code == 401) {
            throw new AppAuthenticationError(json_decode($response_body, true));
        }
        else if ($status_code == 400) {
            $arr = json_decode($response_body, true);
            if ($arr != null) {
                if ($arr['error'] != null) {
                    if ($arr['error']['type'] == 'user_authentication_error') {
                        throw new UserAuthenticationError($arr);
                    } else if ($arr['error']['type'] == 'resource_not_found_error') {
                        throw new ResourceNotFoundError($arr);
                    }
                }
            }
            // throw whatever we have
            throw new InvalidRequestError($arr);
        }
        else if ($status_code == 415) {
            throw new InvalidRequestError(json_decode($response_body, true));
        }
        else {
            $msg = 'There was an error in our servers, status code: ' . $status_code . '. If this persists, please let us know at support@userkit.io';
            throw new APIError(null, $msg);
        }

        // NOTE: must leave for the IDE to resolve correctly
        return null;
    }
}


?>
