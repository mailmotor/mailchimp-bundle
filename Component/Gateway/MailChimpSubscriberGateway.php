<?php

namespace MailMotor\Bundle\MailChimpBundle\Component\Gateway;

use MailMotor\Bundle\MailMotorBundle\Component\MailMotor;
use MailMotor\Bundle\MailMotorBundle\Component\Gateway\SubscriberGateway;

/**
 * MailChimp Subscriber Gateway
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 */
class MailChimpSubscriberGateway implements SubscriberGateway
{
    /**
     * @var MailMotor
     */
    protected $mailMotor;

    /**
     * Construct
     *
     * @param MailMotor $mailMotor
     */
    public function __construct(
        MailMotor $mailMotor
    ) {
        $this->mailMotor = $mailMotor;
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
        $listId = null
    ) {
        try {
            /** @var Illuminate\Support\Collection $response */
            $response = $this->mailMotor->getApi()->request(
                'lists/' . $this->mailMotor->getListId($listId) . '/members/' . $this->getEmailHash($email),
                array(),
                'get'
            );

            // will return the one and only member array('id', ...) from Illuminate\Support\Collection
            return $response->all();
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
        $listId = null,
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
        $listId = null,
        $mergeFields = array(),
        $language = null,
        $doubleOptin = true
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

        /** @var Illuminate\Support\Collection $response */
        $response = $this->mailMotor->getApi()->request(
            'lists/' . $this->mailMotor->getListId($listId) . '/members/' . $this->getEmailHash($email),
            $parameters,
            'put'
        );

        return $response;
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
        $listId = null
    ) {
        /** @var Illuminate\Support\Collection $response */
        $response = $this->mailMotor->getApi()->request(
            'lists/' . $this->mailMotor->getListId($listId) . '/members/' . $this->getEmailHash($email),
            array(
                'status' => 'unsubscribed',
            ),
            'patch'
        );

        return $response;
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
