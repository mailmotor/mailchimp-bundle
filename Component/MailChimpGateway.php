<?php

namespace MailMotor\Bundle\MailChimpBundle\Component;

use MailMotor\Bundle\MailMotorBundle\Component\Gateway;
use Mailchimp\Mailchimp;

/**
 * MailChimp Gateway
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 */
final class MailChimpGateway implements Gateway
{
    /**
     * The external MailChimp API
     *
     * @var mixed
     */
    protected $api;

    /**
     * The default list id
     *
     * @var string
     */
    protected $listId;

    /**
     * Construct
     *
     * @param Mailchimp $api
     * @param string $listId
     */
    public function __construct(
        Mailchimp $api,
        $listId
    ) {
        $this->api = $api;
        $this->listId = $listId;
    }

    /**
     * Get list id
     *
     * @param string $listId If you want to use a custom list id
     * @return string
     */
    public function getListId($listId = null)
    {
        return ($listId == null) ? $this->listId : $listId;
    }

    /**
     * Get
     *
     * @param string $email
     * @param string $listId
     * @return array
     */
    public function get(
        $email,
        $listId = null
    ) {
        try {
            $listId = $this->getListId($listId);
            $result = $this->api->request(
                'lists/' . $listId . '/members/' . $this->getEmailHash($email),
                array(),
                'get'
            );

            return $result->all();
        } catch (\Exception $e) {
            return new \Exception('Member not found with email = "' . $email . ' in list with id = "' . $listId . '".');
        }
    }

    /**
     * Has status
     *
     * @param string $email
     * @param string $listId
     * @param string $status
     * @return boolean
     */
    public function hasStatus(
        $email,
        $listId = null,
        $status
    ) {
        $member = $this->get(
            $email,
            $listId
        );

        // we have a list member
        if ($member && (gettype($member) == 'object' && get_class($member) !== 'Exception')) {
            return ($member['status'] === $status);
        }

        // we don't have a member
        return false;
    }

    /**
     * Subscribe
     *
     * @param string $email
     * @param string $listId
     * @param array $mergeFields
     * @param string $language
     * @return boolean
     */
    public function subscribe(
        $email,
        $listId = null,
        $mergeFields = array(),
        $language = null
    ) {
        // init body parameters
        $bodyParameters = array(
            'email_address' => $email,
            'status' => 'subscribed',
        );

        // we received a language
        if ($language !== null) {
            // define language
            $bodyParameters['language'] = $language;
        }

        // we received merge fields
        if (!empty($mergeFields)) {
            // define merge fields
            $bodyParameters['merge_fields'] = $mergeFields;
        }

        return $this->api->request(
            'lists/' . $this->getListId($listId) . '/members/' . $this->getEmailHash($email),
            $bodyParameters,
            'put'
        );
    }

    /**
     * Unsubscribe
     *
     * @param string $email
     * @param string $listId
     * @param array $mergeFields
     * @return boolean
     */
    public function unsubscribe(
        $email,
        $listId = null,
        $mergeFields = array()
    ) {
        return $this->api->request(
            'lists/' . $this->getListId($listId) . '/members/' . $this->getEmailHash($email),
            array(
                'email_address' => $email,
            ),
            'delete'
        );
    }

    /**
     * Get email hash
     *
     * @param string $email
     * @return string
     */
    protected function getEmailHash($email)
    {
        return md5(strtolower($email));
    }
}
