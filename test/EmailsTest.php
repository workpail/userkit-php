<?php

namespace UserKit;

use Symfony\Component\Config\Definition\Exception\Exception;

require_once('../userkit/UserKit.php');
require_once('../userkit/Emails.php');
require_once('../userkit/Email.php');

class EmailsTest extends \PHPUnit_Framework_TestCase
{
    private $uk;

    public function Setup()
    {
        $api_key = getenv('USERKIT_KEY');
        $this->assertNotEmpty($api_key, 'Missing environmental variable USERKIT_KEY.');

        // set the UserKit API key to your API key via a static secrets call
        $this->uk = new UserKit($api_key);
    }

    public function test_get_pending_email_bad_key()
    {
        try {
            // create an email
            $em = $this->uk->emails->getPendingEmail('completelywrong-key-here');

            $this->assertEmpty($em, 'get email using a wrong key');

            return $em;
        } catch (Exception $ex) {
            print "\n->>exception was $ex";
        }

        return null;
    }

}