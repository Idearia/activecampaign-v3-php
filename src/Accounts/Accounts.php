<?php

namespace Mediatoolkit\ActiveCampaign\Accounts;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class Accounts
 * @package Mediatoolkit\ActiveCampaign\Accounts
 * @see https://developers.activecampaign.com/reference#accounts
 */
class Accounts extends Resource {

    /**
     * Create an account
     * @see https://developers.activecampaign.com/reference#create-an-account
     *
     * @param array $account
     * @return string
     */
    public function create(array $account)
    {
        $req = $this->client
            ->getClient()
            ->post('/api/3/accounts', [
                'json' => [
                    'account' => $account
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Update an account [ANCORA DA TESTARE]
     * @see https://developers.activecampaign.com/reference#update-an-account-new
     *
     * @param int $id
     * @param array $account
     * @return string
     */
    public function update(int $id, array $account)
    {
        $req = $this->client
            ->getClient()
            ->put('/api/3/accounts/' . $id, [
                'json' => [
                    'account' => $account
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Get an account
     * @see https://developers.activecampaign.com/reference#retrieve-an-account
     *
     * @param int $id
     * @return string
     */
    public function get(int $id)
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/accounts/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * List all accounts
     * @see https://developers.activecampaign.com/reference#list-all-accounts
     *
     * @param array $query_params
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function listAll(array $query_params = [], $limit = 20, $offset = 0)
    {
        $query_params = array_merge($query_params, [
            'limit' => $limit,
            'offset' => $offset
        ]);

        $req = $this->client
            ->getClient()
            ->get('/api/3/accounts', [
                'query' => $query_params
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * List all custom fields
     * @see https://developers.activecampaign.com/reference#list-all-custom-fields
     * @param array $query_params
     * @return string
     */
    public function listAllCustomFields(array $query_params = [])
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/accountCustomFieldMeta', [
                'query' => $query_params
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * List all custom field values [ANCORA DA TESTARE]
     * @see https://developers.activecampaign.com/reference#list-all-custom-field-values-2
     * @param array $query_params
     * @return string
     */
    public function listAllCustomFieldValues(array $query_params)
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/accountCustomFieldData', [
                'query' => $query_params
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Bulk create custom field values [ANCORA DA TESTARE]
     * @see https://developers.activecampaign.com/reference#bulk-create-a-custom-field-value-1
     *
     * @param array $customFieldValues
     * @return string
     */
    public function bulkCreateCustomFieldValues(array $customFieldValues)
    {
        $req = $this->client
            ->getClient()
            ->post('/api/3/accountCustomFieldData/bulkCreate', [
                'json' => [
                    'account' => $customFieldValues
                ]
            ]);

        return $req->getBody()->getContents();
    }

}
