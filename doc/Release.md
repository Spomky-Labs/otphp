# The Release Process

We manage releases through a feature-based model.

- A new patch version comes out when we made backwards-compatible bug fixes.
- A new minor version comes out when we added functionality in a backwards-compatible manner.
- A new major version comes out when we make incompatible API changes.

The meanings of "patch", "minor" and "major" come from the [Semantic Versioning](http://semver.org/) strategy.

## Backwards Compatibility

We allow developers to upgrade with confidence from one minor version to the next one.

Whenever keeping backward compatibility is not possible, the feature,
the enhancement or the bug fix will be scheduled for the next major version.

## Semantic Versioning Exception

Experimental Features and code marked with the @internal tags are excluded from this Backward Compatibility policy.

Also note that backward compatibility breaks are tolerated if they are required to fix a security issue.
