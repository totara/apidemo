<?php
/**
 * Config file for sync demo.
 *
 * To get the sync working you need to set the variables in this file to valid values for the sites
 * you want to sync. Both sites will need to be accessible from the location where the sync code is.
 */

// URL of the Totara site you want to sync from.
$source_site_url = '';
// Oauth2 client id of the site you want to sync from.
$source_client_id = '';
// Oauth2 client secret of the site you want to sync from.
$source_client_secret = '';

/**
 * WARNING: Ensure the site referenced below is a test site with no important data. This code
 *          will make destructive changes on the users on this site. Double check the URL below
 *          is the *target* site.
 */
// URL of the Totara site you want to sync to (target site)
// Users on this site will be created/updated/deleted.
$target_site_url = '';
// Oauth2 client id of the site you want to sync to (target site).
$target_client_id = '';
// Oauth2 client secret of the site you want to sync to (target site).
$target_client_secret = '';
