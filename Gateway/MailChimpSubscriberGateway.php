<?php

namespace MailMotor\Bundle\MailChimpBundle\Gateway;

use Illuminate\Support\Collection;
use Mailchimp\Mailchimp;
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

    public function __construct(Mailchimp $api)
    {
        $this->api = $api;
    }

    public function exists(string $email, string $listId): bool
    {
        try {
            // Define result
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
     * @return mixed boolean|Collection
     */
    private function get(string $email, string $listId)
    {
        try {
            /** @var Collection $result */
            $result = $this->api->request(
                'lists/' . $listId . '/members/' . $this->getHashedEmail($email),
                array(),
                'get'
            );

            // will return the one and only member array('id', ...) from Collection
            return $result->all();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getInterests(string $listId): array
    {
        try {
            /** @var Collection $result */
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
            return [];
        }
    }

    protected function getInterestsForCategoryId(string $interestCategoryId, string $listId): array
    {
        try {
            /** @var Collection $result */
            $result = $this->api->request(
                'lists/' . $listId . '/interest-categories/' . $interestCategoryId . '/interests',
                array(),
                'get'
            );

            // will return the one and only member array('id', ...) from Collection
            return $result->all();
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
        if (is_array($member)) {
            return ($member['status'] === $status);
        }

        // we don't have a member
        return false;
    }

    public function ping(string $listId): bool
    {
        try {
            return $this->api->get('/lists/' . $listId) instanceof Collection;
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

        $this->api->request(
            'lists/' . $listId . '/members/' . $this->getHashedEmail($email),
            $parameters,
            'put'
        );

        return true;
    }

    public function unsubscribe(string $email, string $listId): bool
    {
        $this->api->request(
            'lists/' . $listId . '/members/' . $this->getHashedEmail($email),
            array(
                'status' => 'unsubscribed',
            ),
            'patch'
        );

        return true;
    }

    protected function getHashedEmail($email): string
    {
        return md5(strtolower($email));
    }
}
