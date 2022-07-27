<?php

namespace Mediatoolkit\ActiveCampaign\Tracking;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class EventTracking
 *
 * @package Mediatoolkit\ActiveCampaign\Tracking
 * @see https://developers.activecampaign.com/reference#event-tracking
 */
class EventTracking extends Resource
{
    /**
     * Retrieve status
     *
     * @see https://developers.activecampaign.com/reference#retrieve-event-tracking-status
     * @return string
     */
    public function retrieveStatus()
    {
        $req = $this->client->getClient()->get('api/3/eventTracking');

        return $req->getBody()->getContents();
    }

    /**
     * Create a new event
     *
     * @see https://developers.activecampaign.com/v3/reference#create-a-new-event-name-only
     *
     * @param string $name
     * @return string
     */
    public function createEvent($name)
    {
        $req = $this->client->getClient()->post('/api/3/eventTrackingEvents', [
            'json' => [
                'eventTrackingEvent' => compact('name'),
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete event
     *
     * @see https://developers.activecampaign.com/v3/reference#remove-event-name-only
     *
     * @param string $name
     * @return bool
     */
    public function deleteEvent($name)
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/eventTrackingEvent/' . $name);

        return 200 === $req->getStatusCode();
    }

    /**
     * List all events
     *
     * @see https://developers.activecampaign.com/v3/reference#list-all-event-types
     *
     * @param array $query
     * @return string
     */
    public function listAllEvents($query = [])
    {
        $req = $this->client
            ->getClient()
            ->get('api/3/eventTrackingEvents', compact('query'));

        return $req->getBody()->getContents();
    }

    /**
     * Enable/Disable event tracking
     *
     * @see https://developers.activecampaign.com/v3/reference#enable-disable-event-tracking
     *
     * @param bool $enabled
     * @return string
     */
    public function toggleEventTracking($enabled)
    {
        $req = $this->client->getClient()->put('/api/3/eventTracking/', [
            'json' => [
                'eventTracking' => compact('enabled'),
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * @param string $event
     * @param null $event_data
     * @param null $email
     * @return string
     */
    public function trackEvent($event, $event_data = null, $email = null)
    {
        $client = $this->client->getEventTrackingClient();

        if ($client === null) {
            return '';
        }

        $form_params = compact('event');

        if (!is_null($event_data)) {
            $form_params['eventdata'] = $event_data;
        }

        if (!is_null($email)) {
            $form_params['visit'] = json_encode(compact('email'));
        }

        $form_params = array_merge(
            $form_params,
            $client->getConfig('form_params')
        );

        $req = $client->post('', compact('form_params'));

        return $req->getBody()->getContents();
    }
}
