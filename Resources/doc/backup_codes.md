Backup Codes
============

Backup codes are one-time authentication codes, which can be used instead of the actual codes. They're meant as
emergency codes, when the authentication device is not available and you have to pass the two-factor authentication
process.

Enable the feature in the configuration:

```yaml
scheb_two_factor:
    backup_codes:
        enabled: false  # If the backup code feature should be enabled
```

Backup codes have to be provided from the user object. The user entity has to implement
`Scheb\TwoFactorBundle\Model\BackupCodeInterface`. Here's an example:

```php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;

class User implements BackupCodeInterface
{
    /**
     * @ORM\Column(type="json_array")
     */
    private $backupCodes;

    // [...]

    /**
     * Check if it is a valid backup code.
     *
     * @param string $code
     *
     * @return bool
     */
    public function isBackupCode(string $code): bool
    {
        return in_array($code, $this->backupCodes);
    }

    /**
     * Invalidate a backup code
     *
     * @param string $code
     */
    public function invalidateBackupCode(string $code): void
    {
        $key = array_search($code, $this->backupCodes);
        if ($key !== false){
            unset($this->backupCodes[$key]);
        }
    }
}
```

The example assumes that there are already codes generated for that user. In addition to this, you should implement the
backup code (re-)generation as you prefer.

## Custom backup code manager

If you don't like the way this is implemented, you can also have your own backup code manager. Create a service
implementing `Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeManagerInterface` and register it in the
configuration:

```yaml
scheb_two_factor:
    backup_codes:
        manager: acme.custom_backup_code_manager  # Use a custom backup code manager
```
