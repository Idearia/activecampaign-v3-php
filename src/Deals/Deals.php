<?php

namespace Mediatoolkit\ActiveCampaign\Deals;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class Deals
 *
 * @package Mediatoolkit\ActiveCampaign\Deals
 * @see https://developers.activecampaign.com/reference#deal
 */
class Deals extends Resource
{
    /**
     * Create a deal
     *
     * @see https://developers.activecampaign.com/reference#create-a-deal
     *
     * @param array $deal
     * @return string
     */
    public function create($deal)
    {
        $req = $this->client->getClient()->post('/api/3/deals', [
            'json' => compact('deal'),
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Get a deal by id
     *
     * @see https://developers.activecampaign.com/reference#retrieve-a-deal
     *
     * @param int $id
     * @return string
     */
    public function get($id)
    {
        $req = $this->client->getClient()->get('/api/3/deals/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * Update a deal
     *
     * @see https://developers.activecampaign.com/reference#update-a-deal
     *
     * @param int $id
     * @param array $deal
     * @return string
     */
    public function update($id, $deal)
    {
        $req = $this->client->getClient()->put('/api/3/deals/' . $id, [
            'json' => compact('deal'),
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete a deal by id
     *
     * @see https://developers.activecampaign.com/reference#delete-a-deal
     *
     * @param int $id
     * @return string
     */
    public function delete($id)
    {
        $req = $this->client->getClient()->delete('/api/3/deals/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * Move deals to another stage
     *
     * @see https://developers.activecampaign.com/reference#move-deals-to-another-deal-stage
     *
     * @param int $id
     * @param array $deal
     * @return string
     */
    public function moveToStage($id, $deal)
    {
        $req = $this->client
            ->getClient()
            ->put('/api/3/dealStages/' . $id . '/deals', [
                'json' => compact('deal'),
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Create a deal custom field value
     *
     * @see https://developers.activecampaign.com/v3/reference#create-dealcustomfielddata-resource
     *
     * @param int $dealId
     * @param int $custom_field_id
     * @param mixed $fieldValue
     * @return string
     */
    public function createCustomFieldValue(
        $dealId,
        $custom_field_id,
        $fieldValue
    ) {
        $req = $this->client->getClient()->post('/api/3/dealCustomFieldData', [
            'json' => [
                'dealCustomFieldDatum' => compact(
                    'dealId',
                    'custom_field_id',
                    'fieldValue'
                ),
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Retrieve a custom field value
     *
     * @see https://developers.activecampaign.com/v3/reference#retrieve-a-dealcustomfielddata
     *
     * @param int $custom_field_id
     * @return string
     */
    public function retrieveCustomFieldValue($custom_field_id)
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/dealCustomFieldData/' . $custom_field_id);

        return $req->getBody()->getContents();
    }

    /**
     * Update a custom field value
     *
     * @see https://developers.activecampaign.com/v3/reference#update-a-dealcustomfielddata-resource
     *
     * @param int $custom_field_id
     * @param mixed $fieldValue
     * @return string
     */
    public function updateCustomFieldValue($custom_field_id, $fieldValue)
    {
        $req = $this->client
            ->getClient()
            ->put('/api/3/dealCustomFieldData/' . $custom_field_id, [
                'json' => [
                    'dealCustomFieldDatum' => compact('fieldValue'),
                ],
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete a custom field value
     *
     * @see https://developers.activecampaign.com/v3/reference#retrieve-a-dealcustomfielddata-resource
     *
     * @param int $custom_field_id
     * @return string
     */
    public function deleteCustomFieldValue($custom_field_id)
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/dealCustomFieldData/' . $custom_field_id);

        return $req->getBody()->getContents();
    }

    /**
     * List all custom fields
     *
     * @see https://developers.activecampaign.com/reference#retrieve-all-dealcustomfielddata-resources
     *
     * @param array $query
     * @return string
     */
    public function listAllCustomFields(array $query = [])
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/dealCustomFieldMeta', compact('query'));

        return $req->getBody()->getContents();
    }

    /**
     * List all custom field values
     *
     * @see https://developers.activecampaign.com/reference#list-all-custom-field-values
     *
     * @param array $query
     * @return string
     */
    public function listAllCustomFieldValues($query)
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/dealCustomFieldData', compact('query'));

        return $req->getBody()->getContents();
    }

    /**
     * List all pipelines
     *
     * @see https://developers.activecampaign.com/reference#list-all-pipelines
     *
     * @param array $query
     * @return string
     */
    public function listAllPipelines($query = [])
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/dealGroups', compact('query'));

        return $req->getBody()->getContents();
    }

    /**
     * List all stages
     *
     * @see https://developers.activecampaign.com/reference#list-all-deal-stages
     *
     * @param array $query
     * @return string
     */
    public function listAllStages($query = [])
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/dealStages', compact('query'));

        return $req->getBody()->getContents();
    }
}
