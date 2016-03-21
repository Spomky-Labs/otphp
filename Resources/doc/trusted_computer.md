Trusted Computers
=================

You can give users the possibility to flag machines as "trusted computers", which means the two-factor process will be skipped after being executed successfully once.

You have to enable this feature in your `config.yml`:

```yaml
scheb_two_factor:
    trusted_computer:
        enabled: true   # If the trusted computer feature should be enabled
        cookie_name: trusted_computer   # Name of the trusted computer cookie
        cookie_lifetime: 5184000    # Lifetime of the trusted computer cookie
        cookie_secure: false        # Set the 'Secure' (HTTPS Only) flag on the trusted_computer cookie
```

Also, your user entity has to implement `Scheb\TwoFactorBundle\Model\TrustedComputerInterface`. Here's an example:

```php
namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;

class User implements TrustedComputerInterface
{
    /**
     * @ORM\Column(type="json_array")
     */
    private $trusted;

    // [...]

    public function addTrustedComputer($token, \DateTime $validUntil)
    {
        $this->trusted[$token] = $validUntil->format("r");
    }

    public function isTrustedComputer($token)
    {
        if (isset($this->trusted[$token])) {
            $now = new \DateTime();
            $validUntil = new \DateTime($this->trusted[$token]);
            return $now < $validUntil;
        }
        return false;
    }
}
```
