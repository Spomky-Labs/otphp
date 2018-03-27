Google Authentication
====================

## How it works

The user entity has to be linked with Google Authenticator first. This is done by generating a secret code and storing
it in the user entity. Users can add that code to the Google Authenticator app on their mobile. The app will generate a
6-digit numeric code from it that changes every 30 seconds.

On successful authentication the bundle checks if there is a secret stored in the user entity. If that's the case it
will ask for the authentication code. The user must enter the code currently shown in the Google Authenticator app to
gain access.

For more information see the [Google Authenticator website](http://code.google.com/p/google-authenticator/).


## Basic Configuration

To enable this authentication method add this to your configuration:

```yaml
scheb_two_factor:
    google:
        enabled: true
```

Your user entity has to implement `Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface`. To activate Google
Authenticator for a user, generate a secret code and persist it with the user entity.

```php
namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

class User implements TwoFactorInterface
{
    /**
     * @ORM\Column(name="googleAuthenticatorSecret", type="string", nullable=true)
     */
    private $googleAuthenticatorSecret;

    // [...]
    
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->googleAuthenticatorSecret ? true : false;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->username;
    }

    public function getGoogleAuthenticatorSecret(): string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }
}
```

## Custom Authentication Form Template

The bundle uses `Resources/views/Authentication/form.html.twig` to render the authentication form. If you want to use a
different template you can simply register it in configuration:

```yaml
scheb_two_factor:
    google:
        template: security/2fa_form.html.twig
```

## Generating a Secret Code

The service `scheb_two_factor.security.google_authenticator` provides a method to generate new secret for Google
Authenticator.

```php
$secret = $container->get("scheb_two_factor.security.google_authenticator")->generateSecret();
```

With Symfony 4 you use the dependency injection to get the services:

```php
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

public function index(GoogleAuthenticatorInterface $twoFactor)
{
    // ...
    $secret = $twoFactor->generateSecret();
}
```

## QR Codes

**Warning** To generate the QR-code an external service from Google is used. That means the user's personal secure code
is transmitted to that service. This is considered a bad security practice. If you don't like this solution, you should
generate the QR-code locally, for example with [endroid/qr-code-bundle](https://github.com/endroid/qr-code-bundle).

If a user entity has a secret code stored, you can generate a nice-looking QR code from it, which can be scanned by the
Google Authenticator app.

```php
$url = $container->get("scheb_two_factor.security.google_authenticator")->getUrl($user);
echo '<img src="'.$url.'" />';
```

If you can't or don't want to use Google charts to render the QR code you can also get the contents which need to be
encoded in the QR code:

```php
$qrContent = $container->get("scheb_two_factor.security.google_authenticator")->getQRContent($user);
```

You can then encode `$qrContent` in a QR code the way you like (e.g. by using one of the many js-libraries).
 
```php
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

public function index(GoogleAuthenticatorInterface $twoFactor)
{
    // ...
    $url = $twoFactor->getUrl();
    $qrContent = $twoFactor->getQRContent()
}
```
