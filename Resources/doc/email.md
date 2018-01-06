Email Authentication
====================

## Prerequisites ##

If your using Symfony >= 4 be sure to install the mailer component :

```bash
php composer.phar require mailer
```

## How it works ##

On successful authentication it generates a random number and persist it in the user entity. The number is sent to the user via email. Then the user must enter the right number to gain access.

The number of digits can be configured:

```yaml
scheb_two_factor:
    email:
        digits: 6
```


## Basic Configuration ##

To enable this authentication method add this to your config.yml :

```yaml
scheb_two_factor:
    email:
        enabled: true
        sender_email: no-reply@example.com
        sender_name: John Doe  # Optional
```

Your user entity has to implement `Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface`. The authentication code must be persisted, so make sure that it is stored in a persisted field.

```php
namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

class User implements TwoFactorInterface
{
    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $authCode;

    // [...]

    public function getEmail()
    {
        return $this->email;
    }

    public function isEmailAuthEnabled()
    {
        return true; // This can also be a persisted field
    }

    public function getEmailAuthCode()
    {
        return $this->authCode;
    }

    public function setEmailAuthCode($authCode)
    {
        $this->authCode = $authCode;
    }
}
```


## Custom Mailer ##

By default the email is plain text and very simple. If you want a different style (e.g. HTML) you have to create your own mailer service. It must implement `Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface`.

```php
namespace Acme\DemoBundle\Mailer;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;

class MyAuthCodeMailer implements AuthCodeMailerInterface
{
    // [...]

    public function sendAuthCode(TwoFactorInterface $user)
    {
        $authCode = $user->getEmailAuthCode();

        // Send email
    }
}
```

Then register it as a service and update your configuration:

```yaml
scheb_two_factor:
    email:
        mailer: my_mailer_service
```

## Custom Template ##

The bundle uses `Resources/views/Authentication/form.html.twig` to render the authentication form. If you want to use a different template you can simply register it in configuration:

```yaml
scheb_two_factor:
    email:
        template: AcmeDemoBundle:Authentication:my_custom_template.html.twig
```
