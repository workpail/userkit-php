<?php

namespace UserKit;

class Email {
    public $id = null;
    public $to = null;
    public $subject = null;
    public $body = null;

    // internal
    private $parentOb = null;

    public function __construct(UserKit $parentOb, $kwlist) {
        $this->fromJSON($kwlist);
        $this->parentOb = $parentOb;
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
        $this->to = $arr['to'];
        $this->subject = $arr['subject'];
        $this->body = $arr['body'];
    }

    // convert this object into an associative array
    public function toArray() {
        $arr = array();

        if ($this->id) $arr['id'] = $this->id;
        if ($this->to) $arr['to'] = $this->to;
        if ($this->subject) $arr['subject'] = $this->subject;
        if ($this->body) $arr['body'] = $this->body;

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
