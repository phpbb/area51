# Area51

[Area51](http://area51.phpbb.com) is the development site of phpBB.

## About

This repository contains the source code for the area51 website. It is
based on Symfony2.

## Installation

First of all, install the dependencies.

    $ php composer.phar install

Copy `app/config/parameters.dist.yml` to `app/config/parameters.yml`
and adjust the configuration.

Point your webroot to `/web`.

## Tests

To run tests, after installing dependencies, run the following from
the project root

    $ bin/phpunit -c app/

## License

See `LICENSE`.
