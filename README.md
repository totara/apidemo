This repository contains the source code for a demo of Totara 17's new external
API functionality as a companion to a developer screencast of the functionality.

The GIT commit history is tagged with the steps shown in the screencast so you can
follow along.

To test the code you would need:

* Two Totara sites, both accessible over the network from where this code is running.
* A computer running:
  * A recent version of GIT
  * PHP 8.0+
  * Composer installed (https://getcomposer.org/).

Then follow these steps:

1. Clone the repository:
 
```shell
git clone https://github.com/totara/apidemo.git
cd apidemo
```

2. Install dependencies via composer:

```shell
composer install
```

3. Edit the config.php file and enter the URLs and OAuth2 credentials of the two sites. See the screencast for details on how to obtain client id and secret.

4. Update the SERVICE_ACCOUNT_USERNAME constant in src/Sync.php to match the service account username on the target site.

5. Run the command:

```shell
php run.php
```
**WARNING:** User data on the *target site* will be created, updated, and deleted by this code.

The code in this repository is for demonstration purposes only - it is not
production ready or supported by Totara Learning.

This repository is licensed for use under GPLv3 or later.
