<?php

namespace Mediatoolkit\ActiveCampaign\Tags;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class Tags
 *
 * @package Mediatoolkit\ActiveCampaign\Tags
 * @see https://developers.activecampaign.com/reference#tags
 */
class Tags extends Resource
{
    /**
     * List all tags
     *
     * @see https://developers.activecampaign.com/reference#retrieve-all-tags
     *
     * @param array $query_params
     * @return string
     */
    public function listAll($query_params = [])
    {
        $req = $this->client->getClient()->get('/api/3/tags', [
            'query' => $query_params,
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Create New Tag
     *
     * @see https://developers.activecampaign.com/reference#create-a-new-tag
     *
     * @param string $tagName
     * @param string $tagDescription
     * @return string
     */
    public function createTag($tagName, $tagDescription = '')
    {
        $req = $this->client->getClient()->post('/api/3/tags', [
            'json' => [
                'tag' => [
                    'tag' => $tagName,
                    'tagType' => 'contact',
                    'description' => $tagDescription,
                ],
            ],
        ]);

        return $req->getBody()->getContents();
    }
}
