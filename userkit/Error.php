<?php

namespace UserKit;

// Whenever the API returns an error of some kind, this CoreError class is thrown.
// Be sure to catch this and handle things appropriately.
class CoreError extends \Exception {
    public $type = null;
    public $code = null;
    public $param = null;
    public $message = null;
    public $retry_wait = null;

    public function __construct($json) {
        $this->CoreError($json);
    }

    public function CoreError($json) {
        $this->fromJSON($json);
    }

    public function toJSONString() {
        return json_encode(get_object_vars($this));
    }

    public function fromJSON($json) {
        if(gettype($json) == "string") {
            $arr = json_decode($json, true);
        }
        else {
            $arr = $json;
        }
        $arr = $arr['error'];
        $this->code = $arr['code'];
        $this->type = $arr['type'];
        $this->message = $arr['message'];
        $this->param = $arr['param'];
        $this->retry_wait = $arr['retry_wait'];
    }

    public function __toString() {
        // return a human readable string of ALL the object properties
        $obj = get_object_vars($this);
        unset($obj['parentOb']);
        return json_encode($obj);
    }
}

// The UserKitError is simply a collection of Errors that some API calls may return.
// It will contain an "errors" property containing an array of CoreError objects.
// Be sure to catch this and handle things appropriately.
class UserKitError extends CoreError
{
    // Some endpoints return a list of multiple errors
    public $errors = null;

    public function __construct($json_body, $message = null)
    {
        parent::__construct($json_body);
        $this->UserKitError($json_body, $message);
    }

    public function UserKitError($json_body, $message = null)
    {
        $this->message = $message;
        $this->fromJSON($json_body);
    }

    public function fromJSON($json)
    {
        if (gettype($json) == "string") {
            $arr = json_decode($json, true);
        } else {
            $arr = $json;
        }

        // make a new array and add each individual error item
        $this->errors = [];
        if ($arr['errors'] != null) {
            for ($i = 0; $i < count($arr['errors']); $i++) {
                $this->errors[$i] = new CoreError($arr['errors'][$i]);
            }
        }
    }

    public function __toString() {
        return json_encode($this->errors);
    }
}


class AppAuthenticationError extends UserKitError {
    // Unable to authenticate the userkit app making the request
}

class APIError extends UserKitError {
	//
}

class APIConnectionError extends UserKitError {
	//
}

class InvalidRequestError extends UserKitError {
	//
}

class UserError extends UserKitError {
	//
}

class UserAuthenticationError extends UserKitError {
    // Unable to authenticate the user. E.g. bad login or session
}

class ResourceNotFoundError extends UserKitError {
    // The requested resource (ie. user, invite) doesn't exist
}
