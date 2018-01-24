# [SrazVS](http://vodni.skauting.cz/srazvs)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/literat/srazvs/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/literat/srazvs/?branch=master) [![Coverage Status](https://coveralls.io/repos/github/literat/srazvs/badge.svg?branch=master)](https://coveralls.io/github/literat/srazvs?branch=master)  [![Code Coverage](https://scrutinizer-ci.com/g/literat/srazvs/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/literat/srazvs/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/literat/srazvs/badges/build.png?b=master)](https://scrutinizer-ci.com/g/literat/srazvs/build-status/master) [![Build Status](https://travis-ci.org/literat/srazvs.svg?branch=master)](https://travis-ci.org/literat/srazvs)

**czech water/sea scouts meetings application**

## Requirements

### production

- `PHP 7` or higher
- `MySql 5` or higher

### development

- production and
- `node.js 6.10` or higher
- `docker` 

For detailed configuration read `composer.json` and `package.json`.

## Installation

Download the latest [release](https://github.com/literat/srazvs/releases).

Run:
- `composer install`
- `npm install` or `yarn install`

## Getting started

### Structure

- `app`: contains Controllers, Models, Templates and all application architecture
- `www`: static files like styles, javascripts and images
- `inc`: contains included files, configs and definitions
- `vendor`: contains necessery libraries for application running
- `tests`: contains unit and other application tests

### Scripts

#### Composer
- `composer sa` - runs static analysis
- `composer test:unit` - runs unit tests
- `composer test:acceptance` - runs acceptance tests
- `composer tester` - runs Nette Tester
- `composer codeception` - runs Codeception
- `composer clean` - purges cache directories
- `composer deploy` - ships to the production
- `composer deploy:test` - runs dry shipping
- `composer release` - builds release

#### Node

- `npm run build` - runs build using `webpack`
- `npm run dev` - runs development build
- `npm run prod` - runs production build
- `npm watch` - starts watching files
- `npm run changelog` - updates changelog
- `npm run version` - creates new release
- `npm run purge` - purges `node_modules`
- `npm run lint` - runs linter


## Contributing

Please read the [documentation](docs/CONTRIBUTING.md).

## Questions?

* [tomas@litera.me](mailto:tomas@litera.me)
