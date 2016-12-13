<?php

namespace UserKit;

class Invites {
    private $parentOb = null;

    public function __construct(UserKit $parentOb) {
        $this->parentOb = $parentOb;
    }

    public static function filteredArray(array $arr)
    {
        $post_array = array();
        foreach($arr as $key => $val)
        {
            foreach (Invite::$INVT_MUTABLE_FIELDS as $item)
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

    public function createInvite(array $arr) {
        $post_data = Invites::filteredArray($arr);
        $res = $this->parentOb->request("POST", "/invites", null, null, $post_data);

        $iv = new Invite($this->parentOb, $res);
        return $iv;
    }

    public function sendInvite(array $arr) {
        $post_data = Invites::filteredArray($arr);
        $res = $this->parentOb->request("POST", "/invites/send", null, null, $post_data);

        $iv = new Invite($this->parentOb, $res);
        return $iv;
    }

    public function getInvite($invite_id) {
        try {
            $uri = "/invites/" . $invite_id;
            $res = $this->parentOb->request("GET", $uri, null, null, null);

            $iv = new Invite($this->parentOb, $res);
            return $iv;
        }
        catch (ResourceNotFoundError $ex)
        {
            // no invite was returned
        }

        return null;
    }

    public function getByToken($token) {
        try {
            $uri = "/invites/by_token";

            $res = $this->parentOb->request("GET", $uri, ["X-Invite-Token: " . $token], null, null);

            $iv = new Invite($this->parentOb, $res);
            return $iv;
        }
        catch (ResourceNotFoundError $ex)
        {
            // no invite was returned
        }

        return null;
    }

    public function getOnce($token) {
        try {
            $uri = "/invites/get_once";

            $res = $this->parentOb->request("GET", $uri, ["X-Invite-Token: " . $token], null, null);

            $iv = new Invite($this->parentOb, $res);

            return $iv;
        }
        catch (ResourceNotFoundError $ex)
        {
            // no invite was returned
        	// NOTE: only catch this one specific error
        	// and let the others bubble up...
            return null;
        }
    }

    public function getInvites($limit=0, $next_page=null) {
        $params = array();
        if ($limit > 0) {
            $params['limit'] = $limit;
        }

        if ($next_page) {
            $params['next_page'] = $next_page;
        }

        $res = $this->parentOb->request("GET", "/invites", null, $params, null);

        $invite_list = new InviteList();

        $rsInvites = $res['invites'];
        if ($rsInvites) {
            for ($i = 0; $i < count($rsInvites); $i++) {
                $invite_list->append(new Invite($this->parentOb, $rsInvites[$i]));
            }
        }

        $invite_list->next_page = $res['next_page'];

        return $invite_list;
    }

    public function acceptInvite($user_id, $token) {
        $res = $this->parentOb->request("POST", "/invites/accept", null, null, ['user_id' => $user_id, 'token' => $token]);

        $iv = new Invite($this->parentOb, $res);
        return $iv;
    }
}
