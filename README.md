# Personal utilities for PHPUnit

## Disclaimer

USE AT YOUR OWN RISK. See [LICENSE](LICENSE).

This is a hackish tool to simplify my life a bit.
It does not represent best practices of software development.

## Summary

This project currently consists of only single utility to create
cascade PHPUnit filter collecting a minumum number of dependent
tests needed to be run, to run target test

## Install

This is a composer project. Run:

```bash
composer install
```

It's advised to have it globally available, somewhere in `$PATH`,
e.g. `~/bin/`

```bash
ln -s <install_path>/bin/console ~/bin/phpunit-utils

```


## Tools

### Create Filter

This tool allows to run single specified PHPUnit test which has many
cascade dependencies by collecting them and displaying a proper
`phpunit --filter=` value.

See tool help:
```bash
phpunit-utils create-filter --help
```

Usage in custom project root dir:

```bash
phpunit --filter=$(phpunit-utils create-filter 'My\Class::testMethodWithManyDependencies')
```

# Copyright

(c) 2017, Andrew Longosz.
