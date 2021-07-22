<?php

namespace Mediatoolkit\ActiveCampaign\Contacts;

use Mediatoolkit\ActiveCampaign\Resource;

/**
 * Class Contacts
 * @package Mediatoolkit\ActiveCampaign\Contacts
 * @see https://developers.activecampaign.com/reference#contact
 */
class Contacts extends Resource
{
    // -----------------------------------------------------------
    // CODICE NOSTRO
    // -----------------------------------------------------------

    /**
     * List all contacts
     * 
     * Li elenca tutti, iterando sulla paginazione
     */
    public function listAllLoop(array $query_params = [], int $contacts_per_page = 100, $debug = false): array
    {
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
                echo 'Scaricata pagina ' . ($page + 1) . ' / ' . $pages . PHP_EOL;
            }
        }

        return $contacts;
    }

    /**
     * Aggiorna l'account di un dato contatto. Serve però passare
     * NON l'id del contatto
     * MA l'id dell'associazione tra quel contatto e il vecchio account
     */

    public function updateContactAccount(?int $associationID, int $contactID, int $accountID)
    {
        if ($associationID === null) {
            $this->addContactToAccount($contactID, $accountID);
        }

        $req = $this->client
            ->getClient()
            ->put('/api/3/accountContacts/' . $associationID, [
                'json' => [
                    'accountContact' => [
                        'account' => $accountID,
                    ]
                ]
            ]);

        return $req->getBody()->getContents();
    }
    
    // -----------------------------------------------------------
    // CODICE DI MEDIATOOLKIT
    // -----------------------------------------------------------

    /**
     * Create a contact
     * @see https://developers.activecampaign.com/reference#create-contact
     *
     * @param array $contact
     * @return string
     */
    public function create(array $contact)
    {
        $req = $this->client
            ->getClient()
            ->post('/api/3/contacts', [
                'json' => [
                    'contact' => $contact
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Create or update contact
     * @see https://developers.activecampaign.com/reference#create-contact-sync
     *
     * @param array $contact
     * @return string
     */
    public function sync(array $contact)
    {
        $req = $this->client
            ->getClient()
            ->post('/api/3/contact/sync', [
                'json' => [
                    'contact' => $contact
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Get contact
     * @see https://developers.activecampaign.com/reference#get-contact
     *
     * @param int $id
     * @return string
     */
    public function get(int $id)
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/contacts/' . $id);

        return $req->getBody()->getContents();
    }

    /**
     * Update list status for a contact
     * @see https://developers.activecampaign.com/reference#update-list-status-for-contact
     *
     * @param array $contact_list
     * @return string
     */
    public function updateListStatus(array $contact_list)
    {
        $req = $this->client
            ->getClient()
            ->post('/api/3/contactLists', [
                'json' => [
                    'contactList' => $contact_list
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Update a contact
     * @see https://developers.activecampaign.com/reference#update-a-contact
     *
     * @param int $id
     * @param array $contact
     * @return string
     */
    public function update(int $id, array $contact)
    {
        $req = $this->client
            ->getClient()
            ->put('/api/3/contacts/' . $id, [
                'json' => [
                    'contact' => $contact
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete a contact
     * @see https://developers.activecampaign.com/reference#delete-contact
     *
     * @param int $id
     * @return string
     */
    public function delete(int $id)
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/contacts/' . $id);

        return 200 === $req->getStatusCode();
    }

    /**
     * List all automations the contact is in
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
     * Add a tag to contact
     * @see https://developers.activecampaign.com/reference#create-contact-tag
     *
     * @param int $id
     * @param int $tag_id
     * @return string
     */
    public function tag(int $id, int $tag_id)
    {
        $req = $this->client
            ->getClient()
            ->post('/api/3/contactTags', [
                'json' => [
                    'contactTag' => [
                        'contact' => $id,
                        'tag' => $tag_id
                    ]
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * List all tags the contact has
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
     * @see https://developers.activecampaign.com/reference#list-all-contacts
     *
     * @param array $query_params
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function listAll(array $query_params = [], int $limit = 20, int $offset = 0)
    {
        $query_params = array_merge($query_params, [
            'limit' => $limit,
            'offset' => $offset
        ]);

        $req = $this->client
            ->getClient()
            ->get('/api/3/contacts', [
                'query' => $query_params
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * List all custom fields
     * @see https://developers.activecampaign.com/v3/reference#retrieve-fields-1
     * @param array $query_params
     * @return string
     */
    public function listAllCustomFields(array $query_params = [])
    {
        $req = $this->client
            ->getClient()
            ->get('/api/3/fields', [
                'query' => $query_params
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Create a custom field value
     * @see https://developers.activecampaign.com/v3/reference#create-fieldvalue
     *
     * @param int $contact_id
     * @param int $field_id
     * @param string $field_value
     * @return string
     */
    public function createCustomFieldValue(int $contact_id, int $field_id, string $field_value)
    {
        $req = $this->client
            ->getClient()
            ->post('/api/3/fieldValues', [
                'json' => [
                    'fieldValue' => [
                        'contact' => $contact_id,
                        'field' => $field_id,
                        'value' => $field_value
                    ]
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Retrieve a custom field value
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
     * @see https://developers.activecampaign.com/v3/reference#update-a-custom-field-value-for-contact
     *
     * @param int $field_value_id
     * @param int $contact_id
     * @param int $field_id
     * @param string $field_value
     * @return string
     */
    public function updateCustomFieldValue(int $field_value_id, int $contact_id, int $field_id, string $field_value)
    {
        $req = $this->client
            ->getClient()
            ->put('/api/3/fieldValues/' . $field_value_id, [
                'json' => [
                    'fieldValue' => [
                        'contact' => $contact_id,
                        'field' => $field_id,
                        'value' => $field_value
                    ]
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Delete a custom field value
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

        return 200 === $req->getStatusCode();
    }

    /**
     * Remove contact from automation
     * @see https://developers.activecampaign.com/reference#delete-a-contactautomation
     * @param int $contactAutomationId
     * @return bool
     */
    public function removeAutomation(int $contactAutomationId)
    {
        $req = $this->client
            ->getClient()
            ->delete('/api/3/contactAutomation/' . $contactAutomationId);

        return 200 === $req->getStatusCode();
    }

    /**
     * Create a new account association
     * @see https://developers.activecampaign.com/reference#account-contacts
     *
     * @param int $contactID
     * @param int $accountID
     * @param string $jobTitle
     * @return string
     */
    public function addContactToAccount(int $contactID, int $accountID, string $jobTitle = '')
    {
        $req = $this->client
            ->getClient()
            ->post('/api/3/accountContacts', [
                'json' => [
                    'accountContact' => [
                        'contact' => $contactID,
                        'account' => $accountID,
                        'jobTitle' => $jobTitle
                    ]
                ]
            ]);

        return $req->getBody()->getContents();
    }

    /**
     * Get Current Contact Account Association
     * @see https://developers.activecampaign.com/reference#list-all-associations
     * @param array $query_params
     * @return string
     */
    public function getContactAccountAssociation(array $query_params = []) {
        $req = $this->client
            ->getClient()
            ->get('/api/3/accountContacts', [
                'query' => $query_params
            ]);

        return $req->getBody()->getContents();
    }
}
