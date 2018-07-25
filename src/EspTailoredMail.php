<?php
/**
 * Created by PhpStorm.
 * Date: 7/1/18
 * Time: 11:21 AM
 */
namespace Rxmg\EspTailoredMail;

use Rxmg\EspInterface\Abstracts\EspConnector;
use Illuminate\Support\Collection;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class EspTailoredMail extends EspConnector
{
    public function __construct(Collection $details)
    {
        $this->access_key = $details->get('access_key', null);
        $this->server_uri = $details->get('server_uri', null);
        $this->host_id = $details->get('host_id', null);
        $this->database_id = $details->get('database_id', null);

        $this->configure($details);
    }

    /**
     * Configure the ESP connection.
     *
     * Details should include account ID, username, password,
     * or API and secret key, whatever is needed to enable creating a client/connection.
     *
     * @param Collection $details
     * @return bool
     */
    public function configure(Collection $details): bool
    {
        // TODO: Implement configure() method.
        return true;
    }

    /**
     * Subscribe a user to an ESP list.
     *
     * Optionally, details may have custom fields configured at the ESP per list;
     * such as first_name, last_name, birth_date, gender and so on.
     *
     * Return TRUE if the contact was subscribed successfully; false otherwise.
     *
     * @param String $email The email address to subscribe
     * @param String $list_id The list ID to subscribe to
     * @param Collection $details Optional collection of additional custom fields
     * @return bool True if successful, false otherwise
     */
    public function subscribeTo(string $email, string $list_id, Collection $details = null): bool
    {
        // Init subscribe xml
        $xml = '
                <PartnerSubscriber xmlns="http://temp.org/PartnerSubscriber">
                    <email>'.$email.'</email>
                    <userid>0</userid>
                    <hostId>'.$this->host_id.'</hostId>
                    <refId>0</refId>
                    <source>Rest API</source>
                    <databaseId>'.$this->database_id.'</databaseId>
                    <unsubbed>0</unsubbed>
                    <howUnsubbed>1</howUnsubbed>
                    <authorizationKey>'.$this->access_key.'</authorizationKey>
                </PartnerSubscriber>
                ';

        // Create GuzzleHttp Client
        $client = new Client();

        // Send Request and get Response
        $response = $client->request(
            'POST',
            "http://".$this->server_uri."/TMPartnerService/Services/SubscriberService.svc/AddSubscriber",
            [
                'body' => $xml,
                'headers' => [
                    'Content-Type' => 'application/atom+xml',
                    'Authorization' => $this->access_key
                ]
            ]
        );

        // Parse API response status
        $code = $response->getStatusCode(); // 200

        // Return response
        if ($code == "200") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check whether a contact is subscribed to a list.
     *
     * Details should have the email address or contact ID,
     * and the list ID at the ESP to check.
     *
     * If called in the same request as subscribe, a 2nd API call may not be needed.
     * If called after the subscribe request, a 2nd API call will be required.
     *
     * @param String $email The email address to check
     * @param String $list_id The list ID to check on
     * @return bool
     */
    public function isSubscribed(string $email, string $list_id): bool
    {
        // Create GuzzleHttp Client
        $client = new Client();

        // Send Request and get Response
        $response = $client->request(
            'GET',
            "http://".$this->server_uri."/TMPartnerService/Services/SubscriberService.svc/partnersubscribers/".$this->database_id.'?email='.$email,
            [
                'headers' => [
                    'Content-Type' => 'application/atom+xml',
                    'Authorization' => $this->access_key
                ]
            ]
        );

        // Parse API response status
        $code = $response->getStatusCode(); // 200
        $responseXML =  new \SimpleXMLElement($response->getBody()->getContents());
        $responseEmail = $responseXML->email;

        // Return response
        if ($code == "200" && $responseEmail != "") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check to see if the contact failed subscription due to being a duplicate.
     *
     * Details should have the email address or contact ID,
     * and the list ID at the ESP to check.
     *
     * If called in the same request as subscribe, a 2nd API call may not be needed.
     * If called after the subscribe request, a 2nd API call will be required.
     *
     * @param String $email The email address to check
     * @param String $list_id The list ID to check on
     * @return bool
     */
    public function isDuplicate(string $email, string $list_id): bool
    {
        // TODO: Implement isDuplicate() method.
        return false;
    }

    /**
     * Get the list of errors that occurred during the last request.
     *
     * @return array
     */
    public function getErrors(): array
    {
        // TODO: Implement getErrors() method.
        return [
            'No ESP Configured'
        ];
    }
}
