<?php

namespace UserKit;

use Symfony\Component\Config\Definition\Exception\Exception;

require_once('../userkit/UserKit.php');
require_once('../userkit/Users.php');
require_once('../userkit/User.php');

require_once('helper.php');

// common constant defines
define("example_email_domain", "@example.com");
define("example_user_password", "12345678");
define("example_auth_type", "password");

class UsersTest extends \PHPUnit_Framework_TestCase
{
    private $uk;
    private $random_username;
    private $testUser;
    private $testSession;
    private $testExtrasScore;

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
        // NOTE: we are not implementing the rand_email and rand_str
        // because they do NOT correlate to knowing a USER name and the
        // ability to see what email goes with what USER name and vice-versa
        // email = rand_email()

        // create a new random name
        $this->random_username = 'ex' . round(microtime(true) * 1000);

        $this->testExtrasScore = ['score' => 100];

        // create a new user
        $u = $this->uk->users->createUser(['username' => $this->random_username,
                'email' => $this->random_username . example_email_domain,
                'password' => example_user_password,
                'auth_type' => example_auth_type,
                'name' => $this->random_username,
                'extras' => $this->testExtrasScore]
        );

        sleep(1.0);

        return $u;
    }

    public function test_create_user()
    {
        $u = $this->createTestUser();
        $this->assertNotEmpty($u);

        $this->assertEquals($u->extras['score'], $this->testExtrasScore['score'], 'scores are not equal');
    }

    public function test_get_user()
    {
        $u = $this->uk->users->getUser($this->testUser->id);
        $this->assertNotEmpty($u);
    }

    public function test_get_user_does_not_exist()
    {
        $u = $this->uk->users->getUser('wrong-id');
        $this->assertEmpty($u);
    }


    public function test_list_users()
    {
        $ulist = $this->uk->users->getUsers();
        $this->assertNotEmpty($ulist);
    }

    public function test_list_users_two()
    {
        $ulist = $this->uk->users->getUsers(2);
        $this->assertNotEmpty($ulist);
    }

    public function test_update_user()
    {
        // update the publci class value
        $this->testExtrasScore = ['score' => 200];

        // update the instance value
        $this->testUser->extras = $this->testExtrasScore;

        // push to server
        $u = $this->uk->users->updateUser($this->testUser->id, $this->testUser->toArray());

        $this->assertNotEmpty($u);
        $this->assertEquals($u->extras['score'], $this->testExtrasScore['score'], 'scores are not equal');
    }

    public function test_save_user()
    {
        try {
            $altName = 'altName' . round(microtime(true) * 1000);

            $this->testUser->name = $altName;

            $this->testUser->save();

            # since 'save()' updates the object with the server version
            # the name should now be the equal
            $this->assertEquals($altName, $this->testUser->name, 'names need to match');
        } catch (Exception $ex) {
            print "\n EXCEPTION = $ex \n";
        }
    }

    public function test_disable_user()
    {
        $u = $this->createTestUser();
        $this->assertNotEmpty($u);

        $u = $this->uk->users->disableUser($u->id, true);

        $this->assertTrue($u->disabled, 'logic failed - disabled should be true');

        $u = $this->uk->users->disableUser($u->id, false);

        $this->assertFalse($u->disabled, 'logic failed - disabled should be false');

        $this->testUser->disable(true);
    }

    public function test_login_and_logout_user()
    {
        $session = $this->uk->users->loginUser($this->random_username, example_user_password);
        $this->assertNotEmpty($session);
        
        try
        {
            $this->uk->users->logoutUser($session->token);
        }
        catch (Exception $ex)
        {
            // do nothing
        }
    }

    public function test_get_user_by_session()
    {
        $u = $this->uk->users->getUserBySession($this->testSession->token);
        $this->assertNotEmpty($u);
    }

    public function test_get_user_by_session_bad_token()
    {
        $this->setExpectedException(UserAuthenticationError::class);

        $u = $this->uk->users->getUserBySession('very-bad-token');
        $this->assertEmpty($u);
    }

    public function test_get_current_user()
    {
        $u = $this->uk->users->getCurrentUser($this->testSession->token);
        $this->assertNotEmpty($u);
    }

    public function test_get_current_user_bad_token()
    {
        $u = $this->uk->users->getCurrentUser('very-bad-session');
        $this->assertEmpty($u);
    }

    public function test_request_password_reset()
    {
        $this->uk->users->requestPasswordReset($this->random_username);
    }

    public function test_refresh_session()
    {
        // login the created user
        $session1 = $this->uk->users->loginUser($this->random_username, example_user_password);
        $this->assertNotEmpty($session1);

        $session2 = $this->uk->users->refreshSession($session1->token);
        $this->assertNotEmpty($session2);
    }
}
