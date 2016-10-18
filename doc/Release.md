The Release Process
===================

We manage releases through feature and time-based models.

- A new patch version comes out every month when we made backwards-compatible bug fixes.
- A new minor version comes out every six months when we added functionality in a backwards-compatible manner.
- A new major version comes out every year when we make incompatible API changes.

The meanings of "patch", "minor" and "major" come from the [Semantic Versioning](http://semver.org/) strategy.

This release process applies from version 3.1.x.

### Backwards Compatibility

We allow developers to upgrade with confidence from one minor version to the next one.

Whenever keeping backward compatibility is not possible, the feature, the enhancement or the bug fix will be scheduled for the next major version.
