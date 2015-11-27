<?php

namespace MailMotor\Bundle\MailChimpBundle\Component;

use MailMotor\Bundle\MailChimpBundle\Component\Service;
use Mailchimp\Mailchimp;

/**
 * MailChimp Service
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 */
final class MailChimpService implements Service
{
	/**
	 * @var mixed
	 */
	protected $api;

	/**
	 * Construct
	 *
	 * @param mixed $api
	 */
	public function __construct(
		Mailchimp $api
	) {
		$this->api = $api;
	}

	/**
	 * Is subscribed
	 *
	 * @param string $email
	 * @param string $listId
	 * @return boolean
	 */
	public function isSubscribed($email, $listId = null)
	{
	}

	/**
	 * Subscribe
	 *
	 * @param string $email
	 * @param string $listId
	 * @return boolean
	 */
	public function subscribe($email, $listId = null)
	{
		return $this->request(
			'lists/' . $listId . '/members/',
			array(
				'email' => $email,
				'status' => 'subscribed'
			),
			'post'
		);
	}

	/**
	 * Unsubscribe
	 *
	 * @param string $email
	 * @param string $listId
	 * @return boolean
	 */
	public function unsubscribe($email, $listId = null)
	{
	}
}