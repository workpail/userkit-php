<?php

namespace UserKit;

// A User class, contains all the properties/fields of a single User
// The client/auth properties are private and aren't part of a User at all
// (they're used for internal processing).
class User {
    static $USR_MUTABLE_FIELDS = ['username', 'email', 'name', 'password', 'auth_type', 'extras'];

    // Internal use
    private $parentOb;

    // Properties of a User
    public $id = null;
    public $username = null;
    public $email = null;
    public $name = null;
    public $password = null;
    public $auth_type = null;
    public $extras = null;

    public $verified_phone = null;
    public $created = null;
    public $last_login = null;
    public $last_failed_login = null;
    public $disabled = null;

    public function __construct(UserKit $parentOb, $kwlist) {
        $this->fromJSON($kwlist);
        $this->parentOb = $parentOb;
    }

    // Save any changes to the server, also loads the data back into this User
    // from the server overwriting any user properties that may exist.
    public function save() {
        $res = $this->parentOb->request("POST", "/users/" . $this->id, null, null, $this->toArray());

        $this->fromJSON($res);
    }

    // Disable this User. Note the server usually just flags the User as disabled.
    // The User json coming back from the server is reloaded into this User object
    // which will overwrite any user properties that may exist.
    public function disable($disabled_mode) {
        $arr = array();
        $arr['disabled'] = $disabled_mode;

        $res = $this->parentOb->request("POST", "/users/" . $this->id . '/disable', null, null, $arr);

        $this->fromJSON($res);
    }

    // parse a json string into this object
    public function fromJSON($json) {
        if(gettype($json) == "string") {
            $arr = json_decode($json, true);
        }
        else {
            $arr = $json;
        }

        $this->id = $arr['id'];
        $this->name = $arr['name'];
        $this->email = $arr['email'];
        $this->username = $arr['username'];
        $this->password = $arr['password'];
        $this->verified_phone = $arr['verified_phone'];
        $this->auth_type = $arr['auth_type'];
        $this->created = $arr['created'];
        $this->last_login = $arr['last_login'];
        $this->last_failed_login = $arr['last_failed_login'];
        $this->disabled = $arr['disabled'];
        $this->extras = $arr['extras'];
    }

    // convert this object into an associative array
    public function toArray() {
        $arr = array();

        // as per David change in Python - User.save() can now save properties set to None
        $arr['name'] = $this->name;
        $arr['email'] = $this->email;
        $arr['username'] = $this->username;
        if ($this->password) $arr['password'] = $this->password;
        if ($this->auth_type) $arr['auth_type'] = $this->auth_type;
        if ($this->extras) $arr['extras'] = $this->extras;

        return $arr;
    }

    // convert this object into a json string
    public function toJSONString() {
        $obj = $this->toArray();
        return json_encode($obj);
    }

    public function __toString() {
        // return a human readable string of ALL the object properties
        $obj = get_object_vars($this);
        unset($obj['parentOb']);
        return json_encode($obj);
    }
}
