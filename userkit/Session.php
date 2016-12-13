<?php

namespace UserKit;


// Session holds a user's session info.
// login_user() and refresh_token() return a Session object.

class Session {
    public $refresh_after_secs = null;
    public $expires_in_secs = null;
    public $token = null;

    public function __construct($json=null) {
        if($json) {
            $this->fromJSON($json);
        }
    }

    public function __toString() {
        // return a human readable string of ALL the object properties
        $obj = get_object_vars($this);
        unset($obj['parentOb']);
        return json_encode($obj);
    }
    
    public function fromJSON($json) {
        if(gettype($json) == "string") {
            $arr = json_decode($json, true);
        }
        else {
            $arr = $json;
        }
        $this->refresh_after_secs = $arr['refresh_after_secs'];
        $this->expires_in_secs = $arr['expires_in_secs'];
        $this->token = $arr['token'];
    }
}
