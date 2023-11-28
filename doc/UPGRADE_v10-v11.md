# Upgrade from `v10.x` to `v11.x`

Congratulation, you have nothing to do!
This version requires PHP8.1+, but no changes on your side are expected.

However, please note the change in behavior of the `window` feature between versions 10 and 11.

With version 10
---------------

The `window` of timestamps goes from `timestamp - window * period` to `timestamp + window * period`.
For example, if the window is `5`, the period `30` and the timestamp `1476822000`, the OTP tested are within `1476821850` (`1476822000 - 5 * 30`) and `1476822150` (`1476822000 + 5 * 30`).
In other words, this validated the **5 OTP before and after** the current timestamp.

With version 11
---------------
The window of TOTP acts as time drift.
If the window is `10`, the period `30` and the timestamp `147682209`, the OTP tested are within `1476821999` (`147682209 - 10`), `147682209` and `1476822219` (`147682209 + 10`).
This includes the previous OTP, but not the next one.
The `window` shall be lower than the `period`. In the previous example, the `window` shall be between `0` and `30`.
