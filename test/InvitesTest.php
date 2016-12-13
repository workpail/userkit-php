<?php

namespace UserKit;

use Symfony\Component\Config\Definition\Exception\Exception;

require_once('../userkit/UserKit.php');
require_once('../userkit/Users.php');
require_once('../userkit/User.php');

require_once('../userkit/Invites.php');
require_once('../userkit/Invite.php');

// common constant defines
define("example_email_domain", "@example.com");
define("example_user_password", "12345678");
define("example_auth_type", "password");

define("test_to_email", "jane.smith@example.com");

class InvitesTest extends \PHPUnit_Framework_TestCase
{
    private $uk;
    private $random_username;
    private $testUser;
    private $testSession;

    public function Setup()
    {
        $api_key = getenv('USERKIT_KEY');
        $this->assertNotEmpty($api_key, 'Missing environmental variable USERKIT_KEY.');

        $this->uk = new UserKit($api_key);

        // make our standard test user
        $this->testUser = $this->createTestUser();

        // check the create results
        $this->assertNotEmpty($this->testUser, 'cannot create user');
        $this->assertEquals($this->testUser->username, $this->random_username, 'names are not equal');

        // get the user's session
        $this->testSession = $this->uk->users->loginUser($this->random_username, example_user_password);
        $this->assertNotEmpty($this->testSession, 'invalid session');
    }

    private function createTestUser()
    {
        // create a new random name
        $this->random_username = 'ex' . round(microtime(true) * 1000);

        // create a new user
        $u = $this->uk->users->createUser(['username' => $this->random_username,
                'email' => $this->random_username . example_email_domain,
                'password' => example_user_password,
                'auth_type' => example_auth_type,
                'name' => $this->random_username]
        );

        sleep(1);

        return $u;
    }

    public function test_create_invite()
    {
        try {
            $extras = ['team' => 'run&jump'];

            // create an invite
            $iv = $this->uk->invites->createInvite([
                'to_email' => test_to_email
                , 'expires_secs' => 86400
                , 'extras' => $extras
            ]);

            $this->assertNotEmpty($iv, 'could not create invite');
            $this->assertEquals($iv->extras['team'], $extras['team'], 'teams are not equal');

            return $iv;
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }

        return null;
    }

    public function DISABLED_test_send_invite()
    {
        # TODO: re-enable after turning on send-invite endpoint again

        try {
            // make an invite
            $iv = $this->uk->invites->sendInvite([
                'to_email' => test_to_email
                , 'expires_secs' => 86400
            ]);

            $this->assertNotEmpty($iv, 'could not send invite');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

    public function test_get_invite()
    {
        try {
            $extras = ['score' => 200];

            // create a minimal invite
            $iv_created = $this->uk->invites->createInvite([
                'to_email' => test_to_email
                , 'extras' => $extras
            ]);

            $this->assertNotEmpty($iv_created, 'could not create invite');
            $this->assertEquals($iv_created->extras['team'], $extras['team'], 'teams are not equal');

            // now fetch it to make sure the fetch method
            $iv_fetched = $this->uk->invites->getInvite($iv_created->id);
            $this->assertNotEmpty($iv_fetched, 'could not fetch invite');

            $this->assertEquals($iv_fetched->id, $iv_created->id, 'ids are not equal');
            $this->assertEquals($iv_fetched->to_email, $iv_created->to_email, 'emails are not equal');
            $this->assertEquals($iv_fetched->extras['score'], $iv_created->extras['score'], 'scores are not equal');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

    public function test_get_invite_does_not_exist()
    {
        try {
            // try to get a bad id invite
            $iv_fetched = $this->uk->invites->getInvite('wrong-id');
            $this->assertEmpty($iv_fetched, 'invite fetch was not empty');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

    public function test_get_invite_by_token()
    {
        try {
            // create a minimal invite
            $iv_created = $this->uk->invites->createInvite([
                'to_email' => test_to_email
            ]);

            $this->assertNotEmpty($iv_created, 'could not create invite');

            $token = $iv_created->token_raw;

            $iv_fetched = $this->uk->invites->getByToken($token);
            $this->assertNotEmpty($iv_fetched, 'could not get invite by token');

            $this->assertEquals($iv_fetched->id, $iv_created->id, 'ids are not equal');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

    public function test_get_invite_bad_token()
    {
        try {
            // create a minimal invite
            $iv_created = $this->uk->invites->createInvite([
                'to_email' => test_to_email
            ]);

            $this->assertNotEmpty($iv_created, 'could not create invite');

            $bad_token = $iv_created->token_raw . 'bad';

            $iv_fetched = $this->uk->invites->getByToken($bad_token);
            $this->assertEmpty($iv_fetched, 'got bad token invite');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

    public function test_get_invite_once_by_token()
    {
        try {
            // create a minimal invite
            $iv_created = $this->uk->invites->createInvite([
                'to_email' => test_to_email
            ]);
            $this->assertNotEmpty($iv_created, 'could not create invite');

            $token = $iv_created->token_raw;

            $iv_fetched = $this->uk->invites->getOnce($token);
            $this->assertNotEmpty($iv_fetched, 'could not get invite by token');

            $this->assertEquals($iv_fetched->id, $iv_created->id, 'ids are not equal');

            // Should fail second time for same invite
            $iv_fetched = $this->uk->invites->getOnce($token);
            $this->assertEmpty($iv_fetched, 'got invite more than once');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

    public function test_get_invite_once_bad_token()
    {
        try {
            // create a minimal invite
            $iv_created = $this->uk->invites->createInvite([
                'to_email' => test_to_email
            ]);
            $this->assertNotEmpty($iv_created, 'could not create invite');

            $bad_token = $iv_created->token_raw . 'bad';

            $iv_fetched = $this->uk->invites->getOnce($bad_token);
            $this->assertEmpty($iv_fetched, 'got an invite from a bad token');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

    public function test_list_invites()
    {
        $rs_list = null;
        try {
            $rs_list = $this->uk->invites->getInvites();
            $this->assertNotEmpty($rs_list, 'getting invites failed');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

    public function test_accept_invite()
    {
        try {
            $iv_created = $this->test_create_invite();
            $this->assertNotEmpty($iv_created, 'create invite failed');

            # now fetch it to make sure the fetch method
            $iv_fetched = $this->uk->invites->acceptInvite($this->testUser->id, $iv_created->token_raw);
            $this->assertNotEmpty($iv_fetched, 'could not fetch invite');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

    // Util methods
    public function test_invite__str__method()
    {
        try {
            $iv_created = $this->test_create_invite();
            $this->assertNotEmpty($iv_created, 'create invite failed');

            $s = serialize($iv_created);
            $this->assertNotEmpty($s, 'could not fetch invite');
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }
    }

}