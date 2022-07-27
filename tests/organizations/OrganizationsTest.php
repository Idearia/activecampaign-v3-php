<?php

namespace Mediatoolkit\Tests\Organizations;

use Mediatoolkit\ActiveCampaign\Organizations\Organizations;
use Mediatoolkit\Tests\ResourceTestCase;

class OrganizationsTest extends ResourceTestCase
{
    /**
     * The name of the organization.
     *
     * @var string|null
     */
    protected static $name;

    /**
     * This method is called before the first test of this test class is run.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$name = 'Test Org';
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::$name = null;
    }

    /**
     * @return void
     */
    public function testOrganizations()
    {
        $organizations = new Organizations($this->client);

        $create = $organizations->create([
            'name' => self::$name,
        ]);

        $org = json_decode($create, true);
        $this->assertCount(1, $org);
    }
}
