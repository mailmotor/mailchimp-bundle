<?php

namespace MailMotor\Bundle\MailChimpBundle\Component;

use MailMotor\Bundle\MailMotorBundle\Component\MailMotor;
use Mailchimp\Mailchimp;

/**
 * MailChimp MailMotor
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 */
class MailChimpMailMotor extends MailMotor
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
     * @param string $listId
     */
    public function __construct(
        Mailchimp $api,
        $listId
    ) {
        parent::__construct($listId);
        $this->api = $api;
    }

    /**
     * Get the external Mailchimp api
     *
     * @return mixed
     */
    public function getApi()
    {
        return $this->api;
    }
}
