<?php

namespace Mediatoolkit\ActiveCampaign\Organizations;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class Organizations
 *
 * @package Mediatoolkit\ActiveCampaign\Organizations
 * @see https://developers.activecampaign.com/reference#organizations
 */
class Organizations extends Resource
{
    /**
     * Create an organization
     *
     * @see https://developers.activecampaign.com/reference#create-organization
     *
     * @param array $organization
     * @return string
     */
    public function create($organization)
    {
        $req = $this->client->getClient()->post('/api/3/organizations', [
            'json' => compact('organization'),
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Get an organization
     *
     * @see https://developers.activecampaign.com/reference#get-organization
     *
     * @param int $id
     * @return string
     */
    public function get($id)
    {
        $req = $this->client->getClient()->get('/api/3/organizations/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * Update an organization
     *
     * @see https://developers.activecampaign.com/reference#update-organization
     *
     * @param int $id
     * @param array $organization
     * @return string
     */
    public function update($id, $organization)
    {
        $req = $this->client->getClient()->put('/api/3/organizations/' . $id, [
            'json' => compact('organization'),
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete an organization
     *
     * @see https://developers.activecampaign.com/reference#delete-organization
     *
     * @param int $id
     * @return string
     */
    public function delete($id)
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/organizations/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * Delete multiple organizations
     *
     * @see https://developers.activecampaign.com/reference#delete-multiple-organizations
     *
     * @param array $ids
     * @return string
     */
    public function bulkDelete($ids)
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/organizations/bulk_delete', [
                'query' => compact('ids'),
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * List all organizations
     *
     * @param array $query
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function listAll($query = [], $limit = 20, $offset = 0)
    {
        $query = array_merge($query, compact('limit', 'offset'));

        $req = $this->client
            ->getClient()
            ->get('/api/3/organizations', compact('query'));

        return $req->getBody()->getContents();
    }
}
