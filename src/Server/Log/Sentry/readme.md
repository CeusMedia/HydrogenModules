# Sentry Monitoring Integration

Module <code>Server:Log:Sentry</code>:
- uses the Sentry SDK for PHP
- used hook onEnvInitModules to start Sentry listening
- catches then (also) all exceptions handled with hook onEnvLogException

## Install Composer Package

```
composer require sentry/sdk
```

## Configuration

- active: main switch of module
- dsn: Sentry project DSN
- environment: project environment, if available
- release: project release, if available



