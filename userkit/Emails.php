<?php

namespace UserKit;


class EmailManager {

    // internal
    private $parentOb = null;

    public function __construct(UserKit $parentOb) {
        $this->parentOb = $parentOb;
    }

    public function getPendingEmail($email_key) {
        try {
            $uri = "/emails/pending";
            $res = $this->parentOb->request("GET", $uri, ["X-Email-Key: " . $email_key], null, null);

            $em = new Email($this->parentOb, $res);
            return $em;
        }
        catch (ResourceNotFoundError $ex)
        {
            // no invite was returned
        }

        return null;
    }
}