<?php

namespace Mediatoolkit\Tests\Tracking;

use Mediatoolkit\ActiveCampaign\Tracking\EventTracking;
use Mediatoolkit\Tests\ResourceTestCase;

class EventTrackingTest extends ResourceTestCase
{
    /**
     * @return void
     */
    public function testTrackEvent()
    {
        $tracking = new EventTracking($this->client);
        $track = $tracking->trackEvent('test', null, null);

        $event = json_decode($track, true);
        $this->assertArrayHasKey('success', $event);
        $this->assertEquals(1, $event['success']);
    }
}
