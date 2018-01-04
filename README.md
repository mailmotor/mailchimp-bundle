# MailChimpBundle

[![Latest Stable Version](http://img.shields.io/packagist/v/mailmotor/mailchimp-bundle.svg)](https://packagist.org/packages/mailmotor/mailchimp-bundle)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/mailmotor/mailchimp-bundle/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/mailmotor/mailchimp-bundle.svg?branch=new-version)](https://travis-ci.org/mailmotor/mailchimp-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mailmotor/mailchimp-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mailmotor/mailchimp-bundle/?branch=master)

> Subscribing/Unsubscribing to your own mailinglist has never been this easy! Thanks to this Symfony2 bundle.

## Examples

### Configure (MailChimp)

```bash
composer require mailmotor/mailchimp-bundle
```

```php
// In `app/AppKernel.php`
public function registerBundles()
{
    $bundles = array(
        // ...
        new MailMotor\Bundle\MailMotorBundle\MailMotorMailMotorBundle(),
        new MailMotor\Bundle\MailChimpBundle\MailMotorMailChimpBundle(),
    );
```

```yaml
# In `app/config/parameters.yml`
parameters:
    # ...
	mailmotor.mail_engine:  'mailchimp'
	mailmotor.api_key:      xxx # enter your mailchimp api_key here
	mailmotor.list_id:      xxx # enter the mailchimp default list_id here
```

### Subscribing

```php
$this->get('mailmotor.subscriber')->subscribe(
    $email,         // f.e.: 'jeroen@siesqo.be'
    $language,      // f.e.: 'nl'
    $mergeFields,   // f.e.: ['FNAME' => 'Jeroen', 'LNAME' => 'Desloovere']
    $interests,     // f.e.: ['9A28948d9' => true, '8998ASAA' => false]
    $doubleOptin,   // OPTIONAL, default = true
    $listId         // OPTIONAL, default listId is in your config parameters
);
```

### Unsubscribing

```php
$this->get('mailmotor.subscriber')->unsubscribe(
    $email,
    $listId // OPTIONAL, default listId is in your config parameters
);
```

### Exists

```php
$this->get('mailmotor.subscriber')->exists(
    $email,
    $listId // OPTIONAL, default listId is in your config parameters
);
```

### Is subscribed

```php
$this->get('mailmotor.subscriber')->isSubscribed(
    $email,
    $listId // OPTIONAL, default listId is in your config parameters
);
```

### Full example for subscribing

```php
use MailMotor\Bundle\MailMotorBundle\Exception\NotImplementedException;

// Don't forget to add validation to your $email
$email = 'jeroen@siesqo.be';

try {
    if ($this->get('mailmotor.subscriber')->isSubscribed($email)) {
        // Add error to your form
    }
// Fallback for when no mailmotor parameters are defined
} catch (NotImplementedException $e) {
    // Do nothing
}

if ($noErrors)
    try {
        // Subscribe the user to our default group
        $this->get('mailmotor.subscriber')->subscribe(
            $email,
            $language,
            $mergeFields
        );
    // Fallback for when no mailmotor parameters are defined
    } catch (NotImplementedException $e) {
        // Add you own code here to f.e.: send a mail to the admin
    }
}
```

### Full example for unsubscribing

```php
use MailMotor\Bundle\MailMotorBundle\Exception\NotImplementedException;

// Don't forget to add validation to your $email
$email = 'jeroen@siesqo.be';

try {
    // Email exists
    if ($this->get('mailmotor.subscriber')->exists($email)) {
        // User is already unsubscribed
        if ($this->get('mailmotor.subscriber')->isUnsubscribed($email)) {
            // Add error to your form: "User is already unsubscribed"
        }
    // Email not exists
    } else {
        // Add error to your form: "email is not in mailinglist"
    }
// Fallback for when no mailmotor parameters are defined
} catch (NotImplementedException $e) {
    // Do nothing
}

if ($noErrors) {
    try {
        // Unsubscribe the user
        $this->get('mailmotor.subscriber')->unsubscribe($email);
    // Fallback for when no mailmotor parameters are defined
    } catch (NotImplementedException $e) {
        // We can send a mail to the admin instead
    }
}
```

## Extending

### Creating a bundle for another mail engine.

F.e.: You want to use a mail engine called "Crazy".

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Crazy\Bundle\MailMotorBundle\CrazyMailMotorBundle(),
    );
```

In **app/config/parameters.yml**

```yaml
mailmotor.mail_engine:  'crazy'
mailmotor.api_key:      xxx # enter your crazy api_key here
mailmotor.list_id:      xxx # enter the crazy default list_id here
```

Then you just need to duplicate all files from another mail engine, like f.e.: "mailmotor/mailchimp-bundle" and replace all the logic for your own mail engine.

## Credits

* Jeroen Desloovere - [Github](https://github.com/jeroendesloovere), [Website](http://jeroendesloovere.be)