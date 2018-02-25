Brute Force Protection
======================

Brute force protection is not part of the bundle, but you can easily implement it by listening to the
[events](events.md) provided by the bundle.

## 1) Log failed two-factor attempts

Register a listener for the `scheb_two_factor.authentication.failure` event. Log whatever you need (IP, user, etc.)
to detect brute force attacks.

## 2) Block authentication

Register a listener for the `scheb_two_factor.authentication.attempt` event. Execute your brute-force detection logic
and decide if the attempt should be blocked. Since that event is dispatched directly before the two-factor code is
checked, you can prevent that from happening by throwing a new exception of type 
`Symfony\Component\Security\Core\Exception\AuthenticationException`. That exception will be caught by the authentication
layer and the exception message is shown to the user.
