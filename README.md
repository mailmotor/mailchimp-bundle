# MailChimpBundle

This Symfony2 bundle loads in [MailMotor](https://github.com/mailmotor/mailmotor-bundle) as a service. So you can subscribe/unsubscribe members to any mailinglist managing API. F.e.: [MailChimp](https://github.com/mailmotor/mailmotor-mailchimp), CampaignMonitor, ...

## Installation

Open your **terminal** and type:
```bash
composer require mailmotor/mailchimp-bundle
```

In **app/AppKernel.php**

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new MailMotor\Bundle\MailMotorBundle\MailMotorMailMotorBundle(),
        new MailMotor\Bundle\MailChimpBundle\MailMotorMailChimpBundle(),
    );
```

In **app/config/parameters.yml**

```yaml
    mailmotor.mail_engine:  'mailchimp'
    mailmotor.api_key:      xxx # enter your mailchimp api_key here
    mailmotor.list_id:      xxx # enter the mailchimp default list_id here
```

## Examples

*Possible methods*
```php
# Check if email "is subscribed"?
$this->get('mailmotor.subscriber')->isSubscribed($email);

# Subscribe email (when email was unsubscribed, subscribe it again without complaining)
$this->get('mailmotor.subscriber')->subscribe(
    $email,
    $listId,
    $mergeFields,
    $language
);

# Unsubscribe email
$this->get('mailmotor.subscriber')->unsubscribe(
    $email,
    $listId
);
```

*Example variables*
```php
// Define email (required)
$email = 'info@jeroendesloovere.be';

// Define listId (optional), if null, your mailmotor.list_id will be used
$listId = null;

// Define merge fields (optional)
$mergeFields = array(
    'FNAME' => 'Jeroen',
    'LNAME' => 'Desloovere',
);

// Define language (optional)
$language = 'en';
```
