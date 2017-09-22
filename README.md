# [SrazyVS application](http://vodni.skauting.cz/srazyvs)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/literat/srazvs/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/literat/srazvs/?branch=master) [![Coverage Status](https://coveralls.io/repos/github/literat/srazvs/badge.svg?branch=master)](https://coveralls.io/github/literat/srazvs?branch=master)  [![Code Coverage](https://scrutinizer-ci.com/g/literat/srazvs/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/literat/srazvs/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/literat/srazvs/badges/build.png?b=master)](https://scrutinizer-ci.com/g/literat/srazvs/build-status/master) [![Build Status](https://travis-ci.org/literat/srazvs.svg?branch=master)](https://travis-ci.org/literat/srazvs)

## Requirements

Water scouts meetings application requires PHP 7.0.0 or higher and MySQL database.

## Installation

The best way to install Srazy VS application is to download the latest package from GitHub. The downloaded package includes the following directories (just like [Nette Framework](https://nette.org/)):

- `app`: contains Controllers, Models, Templates and all application architecture
- `www`: static files like styles, javascripts and images
- `inc`: contains included files, configs and definitions
- `vendor`: contains necessery libraries for application running
- `tests`: contains unit and other application tests

## Getting started

Just ask me: tomaslitera@hotmail.com

## Coding standard

Class Definitions:		PascalCase

Class Methods:			$this->camelCase()

Class Variables:		$this->camelCase

Functions Definitions:	simple_function()

Variables Definitions:	$simple_variable

## Commit message rules/conventions

### The reasons for these conventions

* automatic generating of the changelog
* simple navigation through git history (e.g. ignoring style changes)

### Format of the commit message
```
<type>(<scope>): <subject>

<body>

<footer>
```

#### Message subject (first line)
The first line cannot be longer than 70 characters, the second line is always blank and other lines should be wrapped at 80 characters. The type and scope should always be lowercase as shown below.

#### Allowed <type> values

* feat (new feature for the user, not a new feature for build script)
* fix (bug fix for the user, not a fix to a build script)
* docs (changes to the documentation)
* style (formatting, missing semi colons, etc; no production code change)
* refactor (refactoring production code, eg. renaming a variable)
* perf (A code change that improves performance)
* test (adding missing tests, refactoring tests; no production code change)
* chore (updating grunt tasks etc; no production code change, changes to the build process or auxiliary tools and libraries such as documentation generation)

#### Example <scope> values

* config
* dev-server
* proxy
* etc.

The <scope> can be empty (e.g. if the change is a global or difficult to assign to a single component), in which case the parentheses are omitted.

```
chore: add dev deployment script
```

### Message body

* uses the imperative, present tense: "change" not "changed" nor "changes"
* includes motivation for the change and contrasts with previous behavior

## Bug Tracker

Here on GitHub:

- https://github.com/literat/srazvs/issues

## Git rules

For each feature/task/defect create a specific branch

- [task-type]-[refs]-[name/description]

- defect-44-forgetting-visitors-program

- feature-31-print-program-details

...

After your work is done, tested and ready, let me know and I merge them into the Master.

...
