Events
======

The bundle dispatches the following events during the authentication process:

`scheb_two_factor.authentication.attempt` is dispatched when two-factor authentication is attempted, right before
checking the code.

`scheb_two_factor.authentication.success` is dispatched when two-factor authentication was successful for a single
provider. That doesn't mean the entire two-factor process is completed.

`scheb_two_factor.authentication.failure` is dispatched when the given two-factor authentication code was incorrect.

`scheb_two_factor.authentication.complete` is dispatched when the entire two-factor authentication process was completed
successfully, that means two-factor authentication was successful for all providers and the user is now fully
authenticated.
