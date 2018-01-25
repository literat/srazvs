# Commit message rules/conventions

## The reasons for these conventions

* automatic generating of the changelog
* simple navigation through git history (e.g. ignoring style changes)

## Format of the commit message
```
<type>(<scope>): <subject>

<body>

<footer>
```

### Message subject (first line)
The first line cannot be longer than 70 characters, the second line is always blank and other lines should be wrapped at 80 characters. The type and scope should always be lowercase as shown below.

### Allowed <type> values

* feat (new feature for the user, not a new feature for build script)
* fix (bug fix for the user, not a fix to a build script)
* docs (changes to the documentation)
* style (formatting, missing semi colons, etc; no production code change)
* refactor (refactoring production code, eg. renaming a variable)
* perf (A code change that improves performance)
* test (adding missing tests, refactoring tests; no production code change)
* chore (updating grunt tasks etc; no production code change, changes to the build process or auxiliary tools and libraries such as documentation generation)

### Example <scope> values

* config
* dev-server
* proxy
* etc.

The <scope> can be empty (e.g. if the change is a global or difficult to assign to a single component), in which case the parentheses are omitted.

```
chore: add dev deployment script
```

## Message body

* uses the imperative, present tense: "change" not "changed" nor "changes"
* includes motivation for the change and contrasts with previous behavior
