# MailChimpBundle

This Symfony2 bundle loads in [MailMotor](https://github.com/mailmotor/mailmotor-bundle) as a service. So you can subscribe/unsubscribe members to any mailinglist managing API. F.e.: [MailChimp](https://github.com/mailmotor/mailmotor-mailchimp), CampaignMonitor, ...

## Installation example for MailChimp

*Open your `terminal` and type*
```bash
composer require mailmotor/mailchimp-bundle
```

*In `app/AppKernel.php`*
```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new MailMotor\Bundle\MailMotorBundle\MailMotorMailMotorBundle(),
        new MailMotor\Bundle\MailChimpBundle\MailMotorMailChimpBundle(),
    );
```

*In app/config/parameters.yml*
```yaml
    mailmotor.mail_engine:  'mailchimp'
    mailmotor.api_key:      xxx # enter your mailchimp api_key here
    mailmotor.list_id:      xxx # enter the mailchimp default list_id here
```

> And you're ready to go.

## Examples

### Subscribing/Unsubscribing

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

>If you didn't fill in the required fields (mailmotor.mail_engine, mailmotor.api_key and mailmotor.list_id) a `NotImplementedException` is being thrown. So you can try/catch that error and integrate your custom integration. For more integration details, checkout the integration in the Fork CMS MailMotor module - [Subscribe example](https://github.com/mailmotor/fork-cms-module-mailmotor/blob/master/src/Frontend/Modules/MailMotor/Actions/Subscribe.php#L108-L152), [Unsubscribe example](https://github.com/mailmotor/fork-cms-module-mailmotor/blob/master/src/Frontend/Modules/MailMotor/Actions/Unsubscribe.php#L112-L158)

[More available mail engines can be found here](https://github.com/mailmotor/mailmotor-bundle)