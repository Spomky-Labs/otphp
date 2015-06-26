Backup Codes
============

Backup codes are one-time authentication codes, which can be used instead of the actual codes. They're meant as emergency codes,
when the authentication device is not available and you have to pass the two-factor authentication process.

Backup codes have to be made available via the user object. To enable the feature, the user entity has to implement
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
     * Check if it is a valid backup code
     *
     * @param string $code
     * @return boolean
     */
    public function isBackupCode($code)
    {
        return in_array($code, $this->backupCodes);
    }

    /**
     * Invalidate a backup code
     *
     * @param string $code
     */
    public function invalidateBackupCode($code)
    {
        $key = array_search($code, $this->backupCodes);
        if($key !== false){
            unset($this->backupCodes[$key]);
        }
    }
}
```

The example assumes that there are already codes generated for that user. In addition to this, you should implement the backup
code (re-)generation like you prefer it.
