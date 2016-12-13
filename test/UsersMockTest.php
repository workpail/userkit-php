<?php

namespace UserKit;

use Symfony\Component\Config\Definition\Exception\Exception;

require_once('../userkit/UserKit.php');
require_once('../userkit/Users.php');
require_once('../userkit/User.php');

require_once('helper.php');

class UsersMockTest extends \PHPUnit_Framework_TestCase
{
    private $uk;
    private $random_username;
    private $testUser;
    private $testSession;
    private $testExtrasScore;

    public function Setup()
    {
        // THIS IS PURPOSELY THE WRONG KEY
        $this->uk = new UserKitMock('fake-key');
    }

    public function test_pwreset_new_password()
    {
        try
        {
            $this->uk->users->resetPassword('fake-pw-reset-token',
                'fake-new-pass');
        }
        catch (Exception $ex)
        {
            print "\n EXCEPTION = $ex \n";
        }
    }

    public function test_set_user_auth_type()
    {
# Test new auth type
        try
        {
            $this->uk->users->setUserAuthType($GLOBALS['DUMMY_USER']['id'], 'password');
        }
        catch (Exception $ex)
        {
            print "\n EXCEPTION = $ex \n";
        }
    }

# Verification tests ----------------------------------------------

    public function test_send_phone_verification_code()
    {
        try
        {
            $this->uk->users->requestPhoneVerificationCode(
                '+15555555555', 'sms');
        }
        catch (Exception $ex)
        {
            print "\n EXCEPTION = $ex \n";
        }
    }

    public function test_send_email_verification_code()
    {
        try
        {
            $this->uk->users->requestEmailVerificationCode(
                'fake@example.com');
        }
        catch (Exception $ex)
        {
            print "\n EXCEPTION = $ex \n";
        }
    }

    public function test_verify_phone()
    {
        $success_token = $this->uk->users->verifyPhone('+15555555555', 'fake-code');
        $this->assertEquals($GLOBALS['DUMMY_VERIFIED_PHONE_SUCCESS']['verified_phone_token'], $success_token);
    }

    public function test_verify_email()
    {
        $success_token = $this->uk->users->verifyEmail('fake@example.com', 'fake-code');
        $this->assertEquals($GLOBALS['DUMMY_VERIFIED_EMAIL_SUCCESS']['verified_email_token'], $success_token);
    }

    public function test_verify_phone_for_user()
    {
        try
        {
            $this->uk->users->verifyPhoneForUser($GLOBALS['DUMMY_USER']['id'], '+15555555555', 'fake-code');
        }
        catch (Exception $ex)
        {
            print "\n EXCEPTION = $ex \n";
        }
    }

    public function test_email_phone_for_user()
    {
        try
        {
            $this->uk->users->verifyEmailForUser($GLOBALS['DUMMY_USER']['id'], 'fake@example.com', 'fake-code');
        }
        catch (Exception $ex)
        {
            print "\n EXCEPTION = $ex \n";
        }
    }

# Utility tests ---------------------------------------------------

    public function test_user__str__method()
    {
        $user = $this->uk->users->getUser($GLOBALS['DUMMY_USER']['id']);
        try
        {
            $s = serialize($user);
        }
        catch (Exception $ex)
        {
            print "\n EXCEPTION = $ex \n";
        }
    }

    public function test_session__str__method()
    {
        $session = $this->uk->users->loginUser('fake-uname', 'fake-pw');
        try
        {
            $s = serialize($session);
        }
        catch (Exception $ex)
        {
            print "\n EXCEPTION = $ex \n";
        }
    }

}

