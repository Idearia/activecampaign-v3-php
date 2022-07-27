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
    public function create(array $account)
    {
        $req = $this->client->getClient()->post('/api/3/accounts', [
            'json' => [
                'account' => $account,
            ],
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
    public function update(int $id, array $account)
    {
        $req = $this->client->getClient()->put('/api/3/accounts/' . $id, [
            'json' => [
                'account' => $account,
            ],
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
    public function delete(int $id)
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
    public function get(int $id)
    {
        $req = $this->client->getClient()->get('/api/3/accounts/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * List all accounts
     *
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
            'offset' => $offset,
        ]);

        $req = $this->client->getClient()->get('/api/3/accounts', [
            'query' => $query_params,
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Ottiene i custom field di un account
     */
    public function getAccountCustomFields(int $account_id): array
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
     * List all accounts
     *
     * Li elenca tutti, iterando sulla paginazione
     */
    public function listAllLoop(
        array $query_params = [],
        int $accounts_per_page = 100,
        $debug = false
    ): array {
        // Risposta JSON dal server
        $res = $this->listAll($query_params, $accounts_per_page);

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
                $query_params,
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
     * List all accounts
     *
     * Li elenca tutti, iterando sulla paginazione, e aggiunge pure i custom fields
     */
    public function listAllWithCustomFields(
        $debug = false,
        int $accounts_per_page = 100
    ): array {
        // aggiungo ai parametri della query i contactLists
        $query_params = [
            'include' =>
                'accountCustomFieldData.customerAccountCustomFieldMetum',
        ];

        // Risposta JSON dal server
        $res = $this->listAll($query_params, $accounts_per_page);

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
                $query_params,
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

        return [
            'accounts' => $accounts,
            'customFields' => $customFields,
            'customFieldsMeta' => $customFieldsMeta,
        ];
    }

    /**
     * List all custom fields
     *
     * @see https://developers.activecampaign.com/reference#list-all-custom-fields
     * @param array $query_params
     * @return string
     */
    public function listAllCustomFields(array $query_params = [])
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/accountCustomFieldMeta', [
                'query' => $query_params,
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * List all custom field values
     *
     * Ritorna null apparentemente per risposte troppo grosse (e.g. Autoluce)
     *
     * @see https://developers.activecampaign.com/reference#list-all-custom-field-values-2
     */
    public function listAllCustomFieldValues(array $query_params = [])
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/accountCustomFieldData', [
                'query' => $query_params,
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Bulk create custom field values [TESTATA SU POSTMAN]
     *
     * In realtà fa create or update
     *
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
                    'account' => $customFieldValues,
                ],
            ]);

        return $req->getBody()->getContents();
    }
}
