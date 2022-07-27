<?php

namespace Mediatoolkit\ActiveCampaign\Accounts;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class Accounts
 *
 * @package Mediatoolkit\ActiveCampaign\Accounts
 * @see https://developers.activecampaign.com/reference#accounts
 */
class Accounts extends Resource
{
    /**
     * Create an account
     *
     * @see https://developers.activecampaign.com/reference#create-an-account
     *
     * @param array $account
     * @return string
     */
    public function create($account)
    {
        $req = $this->client->getClient()->post('/api/3/accounts', [
            'json' => compact('account'),
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Update an account
     *
     * @see https://developers.activecampaign.com/reference#update-an-account-new
     *
     * @param int $id
     * @param array $account
     * @return string
     */
    public function update($id, $account)
    {
        $req = $this->client->getClient()->put('/api/3/accounts/' . $id, [
            'json' => compact('account'),
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete an account
     *
     * @see https://developers.activecampaign.com/reference#delete-an-account
     *
     * @param int $id
     * @return string
     */
    public function delete($id)
    {
        $req = $this->client->getClient()->delete('/api/3/accounts/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * Get an account
     *
     * @see https://developers.activecampaign.com/reference#retrieve-an-account
     *
     * @param int $id
     * @return string
     */
    public function get($id)
    {
        $req = $this->client->getClient()->get('/api/3/accounts/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * List all accounts
     *
     * @see https://developers.activecampaign.com/reference#list-all-accounts
     *
     * @param array $query
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function listAll($query = [], $limit = 20, $offset = 0)
    {
        $query = array_merge($query, compact('limit', 'offset'));

        $req = $this->client
            ->getClient()
            ->get('/api/3/accounts', compact('query'));

        return $req->getBody()->getContents();
    }

    /**
     * Ottiene i custom field di un account
     *
     * @param int $account_id
     * @return array
     */
    public function getAccountCustomFields($account_id)
    {
        $req = $this->client
            ->getClient()
            ->get("/api/3/accounts/$account_id/accountCustomFieldData");

        $json_response = $req->getBody()->getContents();

        return json_decode(
            $json_response,
            true
        )['customerAccountCustomFieldData'];
    }

    /**
     * Elenca tutti gli account, iterando sulla paginazione
     *
     * @param array $query
     * @param int $accounts_per_page
     * @param bool $debug
     * @return array
     */
    public function listAllLoop(
        $query = [],
        $accounts_per_page = 100,
        $debug = false
    ) {
        // Risposta JSON dal server
        $res = $this->listAll($query, $accounts_per_page);

        // Converto la risposta in array
        $res = json_decode($res, true);

        // Calcolo le pagine, i.e. numero di richieste che devo fare in totale
        $total = (int) $res['meta']['total'];
        $pages = (int) ceil($total / $accounts_per_page);

        $accounts = $res['accounts'] ?? [];

        if ($debug) {
            echo 'Scaricata pagina 1 / ' . $pages . PHP_EOL;
        }

        // Loop sulle pagine
        for ($page = 1; $page < $pages; $page++) {
            $res = $this->listAll(
                $query,
                $accounts_per_page,
                $page * $accounts_per_page
            );
            $res = json_decode($res, true);

            // aggiungo i risultati
            $accounts = array_merge($accounts, $res['accounts']);

            if ($debug) {
                echo 'Scaricata pagina ' .
                    ($page + 1) .
                    ' / ' .
                    $pages .
                    PHP_EOL;
            }
        }

        return $accounts;
    }

    /**
     * Elenca tutti gli account, iterando sulla paginazione
     * e aggiunge pure i custom fields
     *
     * @param bool $debug
     * @param int $accounts_per_page
     * @return array
     */
    public function listAllWithCustomFields(
        $debug = false,
        $accounts_per_page = 100
    ) {
        // aggiungo ai parametri della query i contactLists
        $query = [
            'include' =>
                'accountCustomFieldData.customerAccountCustomFieldMetum',
        ];

        // Risposta JSON dal server
        $res = $this->listAll($query, $accounts_per_page);

        // Converto la risposta in array
        $res = json_decode($res, true);

        // Calcolo le pagine, i.e. numero di richieste che devo fare in totale
        $total = (int) $res['meta']['total'];
        $pages = (int) ceil($total / $accounts_per_page);

        // Estraggo le informazioni su account e custom fields
        $accounts = $res['accounts'] ?? [];
        $customFields = $res['customerAccountCustomFieldData'] ?? [];
        $customFieldsMeta = $res['customerAccountCustomFieldMeta'] ?? [];

        if ($debug) {
            echo 'Scaricata pagina 1 / ' . $pages . PHP_EOL;
        }

        // Loop sulle pagine
        for ($page = 1; $page < $pages; $page++) {
            $res = $this->listAll(
                $query,
                $accounts_per_page,
                $page * $accounts_per_page
            );
            $res = json_decode($res, true);

            // aggiungo i risultati
            $accounts = array_merge($accounts, $res['accounts']);
            $customFields = array_merge(
                $customFields,
                $res['customerAccountCustomFieldData']
            );
            $customFieldsMeta = array_merge(
                $customFieldsMeta,
                $res['customerAccountCustomFieldMeta']
            );

            if ($debug) {
                echo 'Scaricata pagina ' .
                    ($page + 1) .
                    ' / ' .
                    $pages .
                    PHP_EOL;
            }
        }

        // rimuovo eventuali copie inutili nei custom fields meta
        $customFieldsMeta = array_unique($customFieldsMeta, SORT_REGULAR);

        return compact('accounts', 'customFields', 'customFieldsMeta');
    }

    /**
     * List all custom fields
     *
     * @see https://developers.activecampaign.com/reference#list-all-custom-fields
     *
     * @param array $query
     * @return string
     */
    public function listAllCustomFields($query = [])
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/accountCustomFieldMeta', compact('query'));

        return $req->getBody()->getContents();
    }

    /**
     * List all custom field values
     * Ritorna null apparentemente per risposte troppo grosse
     *
     * @see https://developers.activecampaign.com/reference#list-all-custom-field-values-2
     *
     * @param array $query
     * @return string
     */
    public function listAllCustomFieldValues($query = [])
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/accountCustomFieldData', compact('query'));

        return $req->getBody()->getContents();
    }

    /**
     * Bulk create custom field values
     * In realtà fa create or update
     *
     * @see https://developers.activecampaign.com/reference#bulk-create-a-custom-field-value-1
     *
     * @param array $customFieldValues
     * @return string
     */
    public function bulkCreateCustomFieldValues($customFieldValues)
    {
        $req = $this->client
            ->getClient()
            ->post('/api/3/accountCustomFieldData/bulkCreate', [
                'json' => [
                    'account' => $customFieldValues,
                ],
            ]);

        return $req->getBody()->getContents();
    }
}
