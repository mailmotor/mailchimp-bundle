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
     * Get interests
     *
     * @param string $listId
     * @return array
     */
    public function getInterests(
        $listId
    ) {
        try {
            /** @var Illuminate\Support\Collection $result */
            $interestCategories = $this->api->request(
                'lists/' . $listId . '/interest-categories',
                array(),
                'get'
            );

            // Init $interests
            $interests = array();

            // Loop all interest categories
            foreach ($interestCategories->all()['categories'] as $interestCategory) {
                // Define interestCategoryItems
                $interestCategoryItems = $this->getInterestsForCategoryId(
                    $interestCategory->id,
                    $listId
                );

                // Init children
                $children = array();

                // Loop interests
                foreach ($interestCategoryItems['interests'] as $interestCategoryItem) {
                    // Add child to interestCategory children
                    $children[$interestCategoryItem->id] = $interestCategoryItem->name;
                }

                // Add interestCategory to interests
                $interests[$interestCategory->id] = [
                    'title' => $interestCategory->title,
                    'children' => $children,
                ];
            }

            return $interests;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get interest category id
     *
     * @param string $interestCategoryId
     * @param string $listId
     * @return array
     */
    protected function getInterestsForCategoryId(
        $interestCategoryId,
        $listId
    ) {
        try {
            /** @var Illuminate\Support\Collection $result */
            $result = $this->api->request(
                'lists/' . $listId . '/interest-categories/' . $interestCategoryId . '/interests',
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
     * @param string $language
     * @param array $mergeFields
     * @param array $interests The array is like: ['9AS489SQF' => true, '4SDF8S9DF1' => false]
     * @param boolean $doubleOptin Members need to validate their emailAddress before they get added to the list
     * @return boolean
     */
    public function subscribe(
        $email,
        $listId,
        $language,
        $mergeFields,
        $interests,
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

        // we received interests
        if (!empty($interests)) {
            // Init interest object
            $interestsObject = new \stdClass();

            // Loop interests
            foreach ($interests as $id => $value) {
                $interestsObject->{$id} = (bool) $value;
            }

            // Add interests to parameters
            $parameters['interests'] = $interestsObject;
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
