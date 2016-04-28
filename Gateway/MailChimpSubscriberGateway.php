<?php

namespace MailMotor\Bundle\MailChimpBundle\Gateway;

use Mailchimp\Mailchimp;
use MailMotor\Bundle\MailMotorBundle\MailMotor;
use MailMotor\Bundle\MailMotorBundle\Gateway\SubscriberGateway;

/**
 * MailChimp Subscriber Gateway
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 */
class MailChimpSubscriberGateway implements SubscriberGateway
{
    /**
     * The external MailChimp API
     *
     * @var Mailchimp
     */
    protected $api;

    /**
     * Construct
     *
     * @param Mailchimp $api
     */
    public function __construct(
        Mailchimp $api
    ) {
        $this->api = $api;
    }

    /**
     * Get a subscriber
     *
     * @param string $email
     * @param string $listId
     * @return array
     */
    public function get(
        $email,
        $listId
    ) {
        try {
            /** @var Illuminate\Support\Collection $result */
            $result = $this->api->request(
                'lists/' . $listId . '/members/' . $this->getEmailHash($email),
                array(),
                'get'
            );

            // will return the one and only member array('id', ...) from Illuminate\Support\Collection
            return $result->all();
        } catch (\Exception $e) {
            return false;
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
        $listId,
        $status
    ) {
        $member = $this->get(
            $email,
            $listId
        );

        // we have found a member
        if (is_array($member)) {
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
     * @param boolean $doubleOptin Members need to validate their emailAddress before they get added to the list
     * @return boolean
     */
    public function subscribe(
        $email,
        $listId,
        $mergeFields,
        $language,
        $doubleOptin
    ) {
        // default status
        $status = 'subscribed';

        // redefine to pending
        if ($doubleOptin) {
            $status = 'pending';
        }

        // init parameters
        $parameters = array(
            'email_address' => $email,
            'status' => $status,
        );

        // we received a language
        if ($language !== null) {
            // add language to parameters
            $parameters['language'] = $language;
        }

        // we received merge fields
        if (!empty($mergeFields)) {
            // add merge fields to parameters
            $parameters['merge_fields'] = $mergeFields;
        }

        return $this->api->request(
            'lists/' . $listId . '/members/' . $this->getEmailHash($email),
            $parameters,
            'put'
        );
    }

    /**
     * Unsubscribe
     *
     * @param string $email
     * @param string $listId
     * @return boolean
     */
    public function unsubscribe(
        $email,
        $listId
    ) {
        return $this->api->request(
            'lists/' . $listId . '/members/' . $this->getEmailHash($email),
            array(
                'status' => 'unsubscribed',
            ),
            'patch'
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
