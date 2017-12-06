# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/opencfp/opencfp).

## Pull Requests

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to run `make cs`, which will apply all code-style fixes necessary.

- **Add tests!** - Your patch probably won't be accepted if it doesn't have tests. Any new tests must be placed in the `tests/OpenCFP` directory and properly namespaced at `OpenCFP\Test`. There will be an ongoing effort to move remaining tests from `tests/unit` into that namespace.

- **Tests must be clear in meaning** - We value clarity in meaning / purposes behind tests. If there is excessive setup required for a test, it should be hidden behind an intention-revealing (and possibly re-usable) method.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Create feature branches** - Feature branches are critically important if you're going to be sending us more than one contribution. Don't send a PR from `master`!

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests. Large pull requests are difficult to review and manage.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. [Appropriate formatting of commit messages](http://chris.beams.io/posts/git-commit/) is also appreciated!

- **Don't close issues via commit message** - We would rather handle these actions ourselves, especially for longer-running issues that may have many PRs submitting against them.

## Testing Coding Conventions

- **All test methods must have the `@test` annotation** -- We value descriptiveness in test method names
- **All data providers must return array-of-args-arrays** -- The use of generators may let us not remember the number of arguments but too many arguments is a sign of potential code- and test-smells
- **All data provider methods must be prefixed `provider`**
- **All test method names should be `snake_case_test_methods`** 


## Running Tests

Run

```
$ make unit
```

to run unit tests.

Run

```
$ make integration
```

to run integration tests.

Run

```
$ make test
```

to run all the tests.

Run 
```
$ make infection
```

to run [infection tests](https://infection.github.io/guide/)

## Fixing Code Style issues

Run

```
$ make cs
```

to detect and automatically fix code style issues.

## Extra lazy?

Run

```
$ make
```

to run both all the tests and to automatically fix code style issues. 

## Credit

This `CONTRIBUTING.md` format was graciously lifted from The PHP League's [example](https://github.com/thephpleague/skeleton/blob/master/CONTRIBUTING.md). Thanks!

**Happy coding**!
