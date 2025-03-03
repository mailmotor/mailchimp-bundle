<?php

namespace MailMotor\Bundle\MailChimpBundle\Gateway;

use MailchimpMarketing\ApiClient;
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
     * @var ApiClient
     */
    protected $api;

    public function __construct(
        string $apiKey,
        string $server
    ) {
        $this->api = new ApiClient();

        $this->api->setConfig([
            'apiKey' => $apiKey,
            'server' => $server,
        ]);
    }

    public function exists(string $email, string $listId): bool
    {
        try {
            /** @var array $result */
            $result = $this->get(
                $email,
                $listId
            );

            return (!empty($result));
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get a subscriber
     *
     * @param string $email
     * @param string $listId
     * @return array
     */
    private function get(string $email, string $listId): array
    {
        try {
            return $this->api->lists->getListMember($listId, $this->getHashedEmail($email));
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getInterests(string $listId): array
    {
        try {
            $interestCategories = $this->api->lists->getListInterestCategories($listId);

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
            return [];
        }
    }

    protected function getInterestsForCategoryId(string $interestCategoryId, string $listId): array
    {
        try {
            return $this->api->lists->getInterestCategory($listId, $interestCategoryId);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function hasStatus(string $email, string $listId, string $status): bool
    {
        $member = $this->get(
            $email,
            $listId
        );

        // we have found a member
        if (is_array($member) && !empty($member) && array_key_exists('status', $member)) {
            return ($member['status'] === $status);
        }

        // we don't have a member
        return false;
    }

    public function ping(string $listId): bool
    {
        try {
            $this->api->lists->getList($listId);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Subscribe
     *
     * @param string $email
     * @param string $listId
     * @param string $language
     * @param array $mergeFields
     * @param array $interests The array is like: ['9AS489SQF' => true, '4SDF8S9DF1' => false]
     * @param bool $doubleOptin Members need to validate their emailAddress before they get added to the list
     * @return boolean
     */
    public function subscribe(
        string $email,
        string $listId,
        string $language,
        array $mergeFields,
        array $interests,
        bool $doubleOptin
    ): bool {
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

        $this->api->lists->setListMember($listId, $this->getHashedEmail($email), $parameters);

        return true;
    }

    public function unsubscribe(string $email, string $listId): bool
    {
        $this->api->lists->updateListMember(
            $listId,
            $this->getHashedEmail($email),
            array(
            'status' => 'unsubscribed',
            )
        );

        return true;
    }

    protected function getHashedEmail($email): string
    {
        return md5(strtolower($email));
    }
}
