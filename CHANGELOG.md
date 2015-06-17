# Changelog

## 0.2.0 (2015-06-17)

* Feature: Forcefully terminate underlying `Process` if its `Stream` closes.
  As such, calling `DeferredShell::close()` now terminates the underlying `Process`.
  ([#2](https://github.com/clue/php-shell-react/pull/2))

* Feature: `ProcessLauncher::createDeferredShell()` now also accepts a `Process` instance.
  ([#1](https://github.com/clue/php-shell-react/pull/1))

## 0.1.0 (2014-12-06)

* Initial concept
