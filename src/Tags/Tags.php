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
     * @param array $query
     * @return string
     */
    public function listAll($query = [])
    {
        $req = $this->client->getClient()->get('/api/3/tags', compact('query'));

        return $req->getBody()->getContents();
    }

    /**
     * Create New Tag
     *
     * @see https://developers.activecampaign.com/reference#create-a-new-tag
     *
     * @param string $tag
     * @param string $description
     * @param string $tagType
     * @return string
     */
    public function createTag($tag, $description = '', $tagType = 'contact')
    {
        $req = $this->client->getClient()->post('/api/3/tags', [
            'json' => [
                'tag' => compact('tag', 'description', 'tagType'),
            ],
        ]);

        return $req->getBody()->getContents();
    }
}
