<?php

namespace Mediatoolkit\Tests\Contacts;

use Mediatoolkit\ActiveCampaign\Contacts\Contacts;
use Mediatoolkit\Tests\ResourceTestCase;

class ContactsTest extends ResourceTestCase
{
    /**
     * The email of the contact.
     *
     * @var string|null
     */
    private static $email;

    /**
     * The first name of the contact.
     *
     * @var string|null
     */
    private static $firstName;

    /**
     * The last name of the contact.
     *
     * @var string|null
     */
    private static $lastName;

    /**
     * This method is called before the first test of this test class is run.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$email = 'wearetesting@mailinator.com';
        self::$firstName = 'Weare';
        self::$lastName = 'Testing';
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::$email = null;
        self::$firstName = null;
        self::$lastName = null;
    }

    /**
     * @return void
     */
    public function testContact()
    {
        $contacts = new Contacts($this->client);
        $create = $contacts->create([
            'email' => self::$email,
            'firstName' => self::$firstName,
            'lastName' => self::$lastName,
        ]);

        $createdContact = json_decode($create, true);
        $this->assertEquals(1, count($createdContact));

        $getContact = $contacts->get($createdContact['contact']['id']);
        $getContact = json_decode($getContact, true);
        $this->assertEquals(self::$email, $getContact['contact']['email']);

        $listNotExisting = $contacts->listAll([
            'email' => 'nonexistinguser@mail.tests',
        ]);

        $listNotExisting = json_decode($listNotExisting, true);
        $this->assertCount(0, $listNotExisting['contacts']);

        $limitWorking = $contacts->listAll(['email' => self::$email], 23, 5);

        $limitWorking = json_decode($limitWorking, true);
        $this->assertCount(0, $limitWorking['contacts']);

        $deleteContact = $contacts->delete($createdContact['contact']['id']);
        $this->assertEquals(true, $deleteContact);
    }
}
