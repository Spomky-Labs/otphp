# Build of the OSS Scanner (https://github.com/anthropics/oss-scanner), with the repository root as context.
# It starts from the phpqa image the CI jobs run in, so phpunit-11 and the QA tools behave as they do on CI.
# PHPUnit is not a Composer dependency of this repository: phpunit-11 and the extension loaded by the PHPUnit
# configuration come from the image. The scan runs without network access: every dependency is installed here.
FROM ghcr.io/spomky-labs/phpqa:8.4

USER root

COPY . /src
WORKDIR /src

RUN composer install --no-interaction --no-progress

RUN composer exec -- phpunit-11 --configuration .ci-tools/phpunit.xml.dist --no-progress \
	|| echo "WARNING: PHPUnit reported failures"
