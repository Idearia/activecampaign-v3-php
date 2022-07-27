<?php

namespace Mediatoolkit\ActiveCampaign\Users;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class Users
 *
 * @package Mediatoolkit\ActiveCampaign\Users
 * @see https://developers.activecampaign.com/reference#users
 */
class Users extends Resource
{
    /**
     * Create a user
     *
     * @see https://developers.activecampaign.com/reference#create-new-user
     *
     * @param array $user
     * @return string
     */
    public function create($user)
    {
        $req = $this->client->getClient()->post('/api/3/users', [
            'json' => [
                'user' => $user,
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Retrieve all users or a user when id is not null
     *
     * @see https://developers.activecampaign.com/reference#retrieve-a-user
     *
     * @param null $id
     * @param array $query_params
     * @return string
     */
    public function retrieve($id = null, $query_params = [])
    {
        $uri = '/api/3/users';
        if (!is_null($id)) {
            $uri .= '/' . $id;
        }
        $req = $this->client->getClient()->get($uri, [
            'query' => $query_params,
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete a user
     *
     * @see https://developers.activecampaign.com/reference#delete-a-user
     *
     * @param int $id
     * @return string
     */
    public function delete($id)
    {
        $req = $this->client->getClient()->delete('/api/3/users/' . $id);

        return $req->getBody()->getContents();
    }
}
