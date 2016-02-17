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
            /** @var ArrayCollection $result */
            $result = $this->mailMotor->getApi()->request(
                'lists/' . $this->mailMotor->getListId($listId) . '/members/' . $this->getEmailHash($email),
                array(),
                'get'
            );

            // getting the member from the ArrayCollection
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
     * @return boolean
     */
    public function subscribe(
        $email,
        $listId = null,
        $mergeFields = array(),
        $language = null
    ) {
        // init parameters
        $parameters = array(
            'email_address' => $email,
            'status' => 'subscribed',
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

        return $this->mailMotor->getApi()->request(
            'lists/' . $this->mailMotor->getListId($listId) . '/members/' . $this->getEmailHash($email),
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
        $listId = null
    ) {
        return $this->mailMotor->getApi()->request(
            'lists/' . $this->mailMotor->getListId($listId) . '/members/' . $this->getEmailHash($email),
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
