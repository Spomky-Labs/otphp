<!--
Slabs security advisory template (brand guidelines, page 49).

1. Fill the fields of the GitHub form (Security > Advisories > New draft) from the first block.
2. Paste the rest, from "Impact" to "Credits", into the Description field.
3. Then, in this order: request the CVE, publish the patched release with the release note below, post the social message.

Tone: calm and factual. What is wrong, who is affected, how serious it is, what to do.
Severity in words, never shown by a color. No capitals for emphasis, no exclamation marks.
Credit the reporter only with their agreement.
-->

Title: [package]: [short description of the issue]

Ecosystem: Composer
Package: [vendor/package]
Affected versions: [for example >= 4.0.0, < 4.9.2]
Patched versions: [for example 4.9.2]
Severity: [Low, Moderate, High or Critical], CVSS [score] ([vector])
Weakness: [CWE identifier and name]
CVE: [CVE identifier], once assigned

---

### Impact

[What is wrong, under which conditions, and what an attacker could do.]

Applications are affected if they use [package] [affected versions] and [condition].

### Patches

Fixed in [patched versions]:

```
composer update [vendor/package]
```

### Workarounds

[What users can do before upgrading, or "None".]

### References

- [Link to the fix: commit or pull request]
- [Link to the release notes]

### Credits

Reported by [Reporter], with their agreement.

---

## Release note of the patched version

### Security

- [Short description of the issue] ([GHSA identifier], [CVE identifier]). [Severity] severity. Upgrade from [affected versions]. Thanks to [Reporter] for the report.

---

## Social post

[package] [patched version] fixes a [severity] severity issue: [short description]. Versions [x] to [y] are affected; upgrade to [z]. Details: [advisory link] #php #security
