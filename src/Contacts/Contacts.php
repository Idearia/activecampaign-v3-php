<?php

namespace Mediatoolkit\ActiveCampaign\Contacts;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class Contacts
 *
 * @package Mediatoolkit\ActiveCampaign\Contacts
 * @see https://developers.activecampaign.com/reference#contact
 */
class Contacts extends Resource
{
    // -----------------------------------------------------------
    // CODICE NOSTRO
    // -----------------------------------------------------------

    /**
     * Iscrive un contatto ad una lista
     *
     * @param int $contact_id L'id su AC del contatto da iscrivere
     * @param int $list_id L'id su AC della lista a cui iscriverlo
     * @return string La risposta JSON
     */
    public function subscribe(int $contact_id, int $list_id): string
    {
        $req = $this->client->getClient()->post('/api/3/contactLists', [
            'json' => [
                'contactList' => [
                    'contact' => $contact_id,
                    'list' => $list_id,
                    'status' => 1,
                ],
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Rimuove l'associazione di un contatto ad una lista
     *
     * @param int $contact_list_id L'id su AC dell'associazione contatto-lista
     * @return bool Se si ottiene 200 OK come status code nella risposta
     */
    public function removeSubscription(int $contact_list_id): bool
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/contactLists/' . $contact_list_id);

        return $req->getStatusCode() === 200;
    }

    /**
     * List all contacts
     *
     * Li elenca tutti, iterando sulla paginazione
     */
    public function listAllLoop(
        array $query_params = [],
        int $contacts_per_page = 100,
        $debug = false
    ): array {
        // Risposta JSON dal server
        $res = $this->listAll($query_params, $contacts_per_page);

        // Converto la risposta in array
        $res = json_decode($res, true);

        // Calcolo le pagine, i.e. numero di richieste che devo fare in totale
        $total = (int) $res['meta']['total'];
        $pages = (int) ceil($total / $contacts_per_page);

        $contacts = $res['contacts'] ?? [];

        if ($debug) {
            echo 'Scaricata pagina 1 / ' . $pages . PHP_EOL;
        }

        // Loop sulle pagine
        for ($page = 1; $page < $pages; $page++) {
            $res = $this->listAll(
                $query_params,
                $contacts_per_page,
                $page * $contacts_per_page
            );
            $res = json_decode($res, true);

            // aggiungo i risultati
            $contacts = array_merge($contacts, $res['contacts']);

            if ($debug) {
                echo 'Scaricata pagina ' .
                    ($page + 1) .
                    ' / ' .
                    $pages .
                    PHP_EOL;
            }
        }

        return $contacts;
    }

    /**
     * List all contacts
     *
     * Li elenca tutti, iterando sulla paginazione, e aggiunge pure i contactLists
     */
    public function listAllWithContactLists(
        $debug = false,
        int $contacts_per_page = 100
    ): array {
        // aggiungo ai parametri della query i contactLists
        $query_params = [
            'include' => 'contactLists',
        ];

        // Risposta JSON dal server
        $res = $this->listAll($query_params, $contacts_per_page);

        // Converto la risposta in array
        $res = json_decode($res, true);

        // Calcolo le pagine, i.e. numero di richieste che devo fare in totale
        $total = (int) $res['meta']['total'];
        $pages = (int) ceil($total / $contacts_per_page);

        // Estraggo le informazioni su contatti e contactLists
        $contacts = $res['contacts'] ?? [];
        $contactLists = $res['contactLists'] ?? [];

        if ($debug) {
            echo 'Scaricata pagina 1 / ' . $pages . PHP_EOL;
        }

        // Loop sulle pagine
        for ($page = 1; $page < $pages; $page++) {
            $res = $this->listAll(
                $query_params,
                $contacts_per_page,
                $page * $contacts_per_page
            );
            $res = json_decode($res, true);

            // aggiungo i risultati
            $contacts = array_merge($contacts, $res['contacts']);
            $contactLists = array_merge($contactLists, $res['contactLists']);

            if ($debug) {
                echo 'Scaricata pagina ' .
                    ($page + 1) .
                    ' / ' .
                    $pages .
                    PHP_EOL;
            }
        }

        return [
            'contacts' => $contacts,
            'contactLists' => $contactLists,
        ];
    }

    /**
     * Aggiorna l'account di un dato contatto. Serve però passare
     * NON l'id del contatto
     * MA l'id dell'associazione tra quel contatto e il vecchio account
     */

    public function updateContactAccount(
        ?int $association_id,
        int $contact_id,
        int $account_id
    ) {
        if ($association_id === null) {
            return $this->addContactToAccount($contact_id, $account_id);
        }

        $req = $this->client
            ->getClient()
            ->put('/api/3/accountContacts/' . $association_id, [
                'json' => [
                    'accountContact' => [
                        'account' => $account_id,
                    ],
                ],
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Crea un nuovo contatto associato ad un account
     */
    public function createWithAccount(array $contact, int $account_id)
    {
        // creo il contatto e mi segno l'id
        $res = json_decode($this->create($contact), true);
        $contact_id = $res['contact']['id'];

        // aggiungo l'associazione con l'account
        $this->addContactToAccount($contact_id, $account_id);
    }

    /**
     * Bulk Import Contacts
     *
     * Manda più richieste bulk per importare i contatti aggiungendo anche dei tag
     *
     * @see https://developers.activecampaign.com/reference#bulk-import-contacts
     */
    public function bulkImport(array $contacts)
    {
        $req = $this->client->getClient()->post('/api/3/import/bulk_import', [
            'json' => [
                'contacts' => $contacts,
            ],
        ]);

        return $req->getBody()->getContents();
    }

    // -----------------------------------------------------------
    // CODICE DI MEDIATOOLKIT
    // -----------------------------------------------------------

    /**
     * Create a contact
     *
     * @see https://developers.activecampaign.com/reference#create-contact
     *
     * @param array $contact
     * @return string
     */
    public function create(array $contact)
    {
        $req = $this->client->getClient()->post('/api/3/contacts', [
            'json' => [
                'contact' => $contact,
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Create or update contact
     *
     * @see https://developers.activecampaign.com/reference#create-contact-sync
     *
     * @param array $contact
     * @return string
     */
    public function sync(array $contact)
    {
        $req = $this->client->getClient()->post('/api/3/contact/sync', [
            'json' => [
                'contact' => $contact,
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Get contact
     *
     * @see https://developers.activecampaign.com/reference#get-contact
     *
     * @param int $id
     * @return string
     */
    public function get(int $id)
    {
        $req = $this->client->getClient()->get('/api/3/contacts/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * Update list status for a contact
     *
     * @see https://developers.activecampaign.com/reference#update-list-status-for-contact
     *
     * @param array $contact_list
     * @return string
     */
    public function updateListStatus(array $contact_list)
    {
        $req = $this->client->getClient()->post('/api/3/contactLists', [
            'json' => [
                'contactList' => $contact_list,
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Update a contact
     *
     * @see https://developers.activecampaign.com/reference#update-a-contact
     *
     * @param int $id
     * @param array $contact
     * @return string
     */
    public function update(int $id, array $contact)
    {
        $req = $this->client->getClient()->put('/api/3/contacts/' . $id, [
            'json' => [
                'contact' => $contact,
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete a contact
     *
     * @see https://developers.activecampaign.com/reference#delete-contact
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        $req = $this->client->getClient()->delete('/api/3/contacts/' . $id);

        return $req->getStatusCode() === 200;
    }

    /**
     * List all automations the contact is in
     *
     * @see https://developers.activecampaign.com/reference#list-all-contactautomations-for-contact
     *
     * @param int $id
     * @return string
     */
    public function listAutomations(int $id)
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/contacts/' . $id . '/contactAutomations');

        return $req->getBody()->getContents();
    }

    /**
     * Aggiunge un tag al contatto
     *
     * @see https://developers.activecampaign.com/reference#create-contact-tag
     *
     * @param int $contact_id L'id del contatto a cui si vuole aggiungere un tag
     * @param int $tag_id L'id del tag che si vuole aggiungere al contatto
     * @return string La risposta JSON
     */
    public function tag(int $contact_id, int $tag_id): string
    {
        $req = $this->client->getClient()->post('/api/3/contactTags', [
            'json' => [
                'contactTag' => [
                    'contact' => $contact_id,
                    'tag' => $tag_id,
                ],
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * List all tags the contact has
     *
     * @see https://developers.activecampaign.com/reference#create-contact
     *
     * @param int $id
     * @return string
     */
    public function listContactTags(int $id)
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/contacts/' . $id . '/contactTags');

        return $req->getBody()->getContents();
    }

    /**
     * Remove a tag from a contact
     *
     * @see https://developers.activecampaign.com/reference#delete-contact-tag
     *
     * @param int $contact_tag_id
     * @return string
     */
    public function untag(int $contact_tag_id)
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/contactTags/' . $contact_tag_id);

        return $req->getBody()->getContents();
    }

    /**
     * List all contacts
     *
     * @see https://developers.activecampaign.com/reference#list-all-contacts
     *
     * @param array $query_params
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function listAll(
        array $query_params = [],
        int $limit = 20,
        int $offset = 0
    ) {
        $query_params = array_merge($query_params, [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $req = $this->client->getClient()->get('/api/3/contacts', [
            'query' => $query_params,
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * List all custom fields
     *
     * @see https://developers.activecampaign.com/v3/reference#retrieve-fields-1
     * @param array $query_params
     * @return string
     */
    public function listAllCustomFields(array $query_params = [])
    {
        $req = $this->client->getClient()->get('/api/3/fields', [
            'query' => $query_params,
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Create a custom field value
     *
     * @see https://developers.activecampaign.com/v3/reference#create-fieldvalue
     *
     * @param int $contact_id
     * @param int $field_id
     * @param string $field_value
     * @return string
     */
    public function createCustomFieldValue(
        int $contact_id,
        int $field_id,
        string $field_value
    ) {
        $req = $this->client->getClient()->post('/api/3/fieldValues', [
            'json' => [
                'fieldValue' => [
                    'contact' => $contact_id,
                    'field' => $field_id,
                    'value' => $field_value,
                ],
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Retrieve a custom field value
     *
     * @see https://developers.activecampaign.com/v3/reference#retrieve-a-fieldvalues
     *
     * @param int $field_id
     * @return string
     */
    public function retrieveCustomFieldValue(int $field_id)
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/fieldValues/' . $field_id);

        return $req->getBody()->getContents();
    }

    /**
     * Update a custom field value
     *
     * @see https://developers.activecampaign.com/v3/reference#update-a-custom-field-value-for-contact
     *
     * @param int $field_value_id
     * @param int $contact_id
     * @param int $field_id
     * @param string $field_value
     * @return string
     */
    public function updateCustomFieldValue(
        int $field_value_id,
        int $contact_id,
        int $field_id,
        string $field_value
    ) {
        $req = $this->client
            ->getClient()
            ->put('/api/3/fieldValues/' . $field_value_id, [
                'json' => [
                    'fieldValue' => [
                        'contact' => $contact_id,
                        'field' => $field_id,
                        'value' => $field_value,
                    ],
                ],
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete a custom field value
     *
     * @see https://developers.activecampaign.com/v3/reference#delete-a-fieldvalue-1
     *
     * @param int $field_value_id
     * @return bool
     */
    public function deleteCustomFieldValue(int $field_value_id)
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/fieldValues/' . $field_value_id);

        return $req->getStatusCode() === 200;
    }

    /**
     * Remove contact from automation
     *
     * @see https://developers.activecampaign.com/reference#delete-a-contactautomation
     * @param int $contactAutomationId
     * @return bool
     */
    public function removeAutomation(int $contactAutomationId)
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/contactAutomation/' . $contactAutomationId);

        return $req->getStatusCode() === 200;
    }

    /**
     * Create a new account association
     *
     * @see https://developers.activecampaign.com/reference#account-contacts
     *
     * @param int $contact_id
     * @param int $account_id
     * @param string $jobTitle
     * @return string
     */
    public function addContactToAccount(
        int $contact_id,
        int $account_id,
        string $jobTitle = ''
    ) {
        $req = $this->client->getClient()->post('/api/3/accountContacts', [
            'json' => [
                'accountContact' => [
                    'contact' => $contact_id,
                    'account' => $account_id,
                    'jobTitle' => $jobTitle,
                ],
            ],
        ]);

        return $req->getBody()->getContents();
    }

    /**
     * Get Current Contact Account Association
     *
     * @see https://developers.activecampaign.com/reference#list-all-associations
     * @param array $query_params
     * @return string
     */
    public function getContactAccountAssociation(array $query_params = [])
    {
        $req = $this->client->getClient()->get('/api/3/accountContacts', [
            'query' => $query_params,
        ]);

        return $req->getBody()->getContents();
    }
}
