<?php

namespace UserKit;


class Invite
{
    static $INVT_MUTABLE_FIELDS = ['to_email', 'from_user', 'expires_secs', 'extras',
        'greeting', 'body', 'signature'];

    // internal
    private $parentOb = null;

    // instance vars
    public $id = null;
    public $token = null;
    public $accepted = null;
    public $accepted_date = null;
    public $accepted_user = null;
    public $app_id = null;
    public $created = null;
    public $expires_secs = null;
    public $extras = null;
    public $from_user = null;
    public $to_email = null;
    public $token_raw = null;
    public $invite_url = null;

    public $greeting = null;
    public $body = null;
    public $signature = null;

    public function __construct(UserKit $parentOb, array $kwlist) {
        $this->fromJSON($kwlist);
        $this->parentOb = $parentOb;
    }

    // parse a json string into this object
    public function fromJSON(array $json) {
        if(gettype($json) == "string") {
            $arr = json_decode($json, true);
        }
        else {
            $arr = $json;
        }

        $this->id = $arr['id'];
        $this->token = $arr['token'];
        $this->accepted = $arr['accepted'];
        $this->accepted_date = $arr['accepted_date'];
        $this->accepted_user = $arr['accepted_user'];
        $this->app_id = $arr['app_id'];
        $this->created = $arr['created'];
        $this->expires_secs = $arr['expires_sec'];
        $this->extras = $arr['extras'];
        $this->from_user = $arr['from_user'];
        $this->to_email = $arr['to_user'];
        $this->token_raw = $arr['token_raw'];
        $this->invite_url = $arr['invite_url'];

        $this->greeting = $arr['greeting'];
        $this->body = $arr['body'];
        $this->signature = $arr['signature'];
    }

    // convert this object into an associative array
    public function toArray() {
        $arr = array();

        if ($this->accepted_date) $arr['accepted_date'] = $this->accepted_date;
        if ($this->accepted_user) $arr['accepted_user'] = $this->accepted_user;
        if ($this->accepted) $arr['accepted'] = $this->accepted;
        if ($this->app_id) $arr['app_id'] = $this->app_id;
        if ($this->expires_secs) $arr['expires_secs'] = $this->expires_secs;
        if ($this->extras) $arr['extras'] = $this->extras;
        if ($this->from_user) $arr['from_user'] = $this->from_user;
        if ($this->to_email) $arr['to_email'] = $this->to_email;

        if ($this->greeting) $arr['greeting'] = $this->greeting;
        if ($this->body) $arr['body'] = $this->body;
        if ($this->signature) $arr['signature'] = $this->signature;

        return $arr;
    }

    // convert this object into a json string
    public function toJSONString() {
        $arr = $this->toArray();
        return json_encode($arr);
    }

    public function __toString() {
        // return a human readable string of ALL the object properties
        $obj = get_object_vars($this);
        unset($obj['parentOb']);
        return json_encode($obj);
    }
}