# Rules Role Email for Drupal 8

The Rules Role Email module provides an action to the rules module that sends
emails to specified roles that an admin chooses.

* Project homepage: https://www.drupal.org/project/rules_role_email

## Executing the automated tests

This module comes with PHPUnit tests. You need a working Drupal 8 installation
and a checkout of the Rules Role Email module in the modules folder.

#### Unit tests and kernel/web tests

Make sure to use your DB connection details for the SIMPLETEST_DB and the URL to
your local Drupal installation for SIMPLETEST_BASE_URL.

    cd /path/to/drupal-8/core
    export SIMPLETEST_DB=mysql://drupal-8:password@localhost/drupal-8
    export SIMPLETEST_BASE_URL=http://drupal-8.localhost
    ../vendor/bin/phpunit ../modules/rules

Example for executing one single test file during development:

    ../vendor/bin/phpunit ..
    /modules/rules/tests/src/Integration/Action/DataSetTest.php

You can also execute the test cases from the web interface at
``/admin/config/development/testing``.
