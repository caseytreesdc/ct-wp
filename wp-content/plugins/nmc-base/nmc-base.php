<?php

/*
Plugin Name: NMC Base
Plugin URI:
Description: Core functionality for New Media Campaigns sites.
Version: 5.3.4
Author: New Media Campaigns
Author URI: https://www.newmediacampaigns.com/
*/

require 'plugin_update_check.php';
$KernlUpdater = new PluginUpdateChecker_2_0(
    'https://kernl.us/api/v1/updates/5e4df702a30a3647fe309542/',
    __FILE__,
    'nmc-base',
    1
);

require_once('vendor/autoload.php');
require_once('includes/core-functions.php');
require_once('includes/default-options.php');
require_once('includes/nmc-blocks.php');
require_once('includes/settings.php');
