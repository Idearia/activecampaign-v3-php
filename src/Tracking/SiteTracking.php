<?php

namespace Mediatoolkit\ActiveCampaign\Tracking;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class SiteTracking
 *
 * @package Mediatoolkit\ActiveCampaign\Tracking
 * @see https://developers.activecampaign.com/reference#site-tracking
 */
class SiteTracking extends Resource
{
    /**
     * Get site tracking status (enabled or disabled)
     *
     * @see https://developers.activecampaign.com/reference#retrieve-site-tracking-status
     *
     * @param array $query
     * @return string
     */
    public function retrieveStatus($query = [])
    {
        $req = $this->client
            ->getClient()
            ->get('api/3/siteTracking', compact('query'));

        return $req->getBody()->getContents();
    }
}
