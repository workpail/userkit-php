<?php

namespace UserKit;

// Whenever the API returns an error of some kind, this CoreError class is thrown.
// Be sure to catch this and handle things appropriately.
class CoreError extends \Exception {
    public $error_code = null;
    public $type = null;
    public $param = null;
    public $retry_wait = null;

    ////
    //  NOTE:  "code" and "message" are protected properties of the super class
    ////
    public function __construct($json)
    {
        // first we have to verify the array
        if(gettype($json) == "string") {
            $arr = json_decode($json, true);
        }
        else {
            $arr = $json;
        }

        // then call the super constructor
        // we do not have a compatible "code" parameter
        // so we just neg it out
        parent::__construct($arr['error']['message'], -1);

        // then set the properties of this class instance
        $this->error_code = $arr['error']['code']; // mapped
        $this->type = $arr['error']['type'];
        $this->param = $arr['error']['param'];
        $this->retry_wait = $arr['error']['retry_wait'];
    }

    public function toJSONString() {
        return json_encode(get_object_vars($this));
    }

    public function __toString()
    {
        $rsVal = parent::__toString();

        // since we have extended error info
        // in our custom object we need to
        // include it in the output string
        $rsVal = $rsVal . "\nmessage = " . $this->message;
        $rsVal = $rsVal . "\nerror_code = " . $this->error_code;
        $rsVal = $rsVal . "\ntype = " . $this->type;
        $rsVal = $rsVal . "\nparam = " . $this->param;
        $rsVal = $rsVal . "\nretry_wait = " . $this->retry_wait;

        return $rsVal;
    }
}

// The UserKitError is simply a collection of Errors that some API calls may return.
// It will contain an "errors" property containing an array of CoreError objects.
// Be sure to catch this and handle things appropriately.
class UserKitError extends CoreError
{
    // Some endpoints return a list of multiple errors
    public $errors = [];

    public function __construct($json)
    {
        // make the base exception
        parent::__construct($json);

        // then verify the array
        if (gettype($json) == "string") {
            $arr = json_decode($json, true);
        } else {
            $arr = $json;
        }

        // then check for an error list
        if ($arr['errors'] != null) {
            // NOTE: we do NOT create 'sub error' objects
            // because in PHP these are treated as 'exceptions'
            // which one cannot just create an properly
            // instantiated exception without THROWING it
            // so we keep the extended list of errors
            // as JSON for the consumer to decide if they
            // wish to process each error type case.
            $this->errors = $arr['errors'];
        }
    }

    public function __toString() {
        // since we have extended error info
        // in our custom object we need to
        // include it in the output string
        $rsVal = parent::__toString();
        if ($this->errors) {
            if (count($this->errors) > 1)
            {
                for ($i = 1; $i < count($this->errors); $i++) {
                    $ob = $this->errors[$i];
                    $rsVal = $rsVal . "\n\tError (" . $i . ")";
                    $rsVal = $rsVal . "\n\tmessage = " . $ob['message'];
                    $rsVal = $rsVal . "\n\terror_code = " . $ob['code'];
                    $rsVal = $rsVal . "\n\ttype = " . $ob['type'];
                    $rsVal = $rsVal . "\n\tparam = " . $ob['param'];
                    $rsVal = $rsVal . "\n\tretry_wait = " . $ob['retry_wait'];
                }
            }
         }

        return $rsVal;
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
