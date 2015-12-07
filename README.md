# MailChimpBundle

This Symfony2 bundle loads in [MailMotor](https://github.com/mailmotor/mailmotor) as a service. So you can subscribe/unsubscribe members to any mailinglist managing API. F.e.: [MailChimp](https://github.com/mailmotor/mailmotor-mailchimp), CampaignMonitor, ...

## Installation

Open your **terminal** and type:
```
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

In **app/config/config.yml**
```yaml
services:
    #...
    mailmotor:
        class: MailMotor\Bundle\MailMotorBundle\Component\MailMotor
        arguments:
            # enter the mail-gateway you want to use, this currently only supports @mailchimp.gateway. It's easy to create your own though.
            - @mailchimp.gateway
```

In **app/config/parameters.yml**

```yaml
    mailchimp.api_key:      xxx # enter your mailchimp api_key here
    mailchimp.list_id:      xxx # enter the default list_id here
```

