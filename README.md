# MailChimpBundle

This Symfony2 bundle loads in [MailMotor](https://github.com/mailmotor/mailmotor) as a service. So you can subscribe/unsubscribe members to any mailinglist managing API. F.e.: [MailChimp](https://github.com/mailmotor/mailmotor-mailchimp), CampaignMonitor, ...

## Installation

Open your **terminal** and type:
```
composer require mailmotor/mailchimp-bundle
```

In **app/AppKernel.php** add

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new MailMotor\Bundle\MailChimpBundle\MailMotorMailChimpBundle(),
    );
```
