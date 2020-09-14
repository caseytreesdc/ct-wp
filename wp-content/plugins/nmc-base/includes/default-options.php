<?php

/**
 * Default Options
 */

if (function_exists('acf_add_options_page')) {
    acf_add_options_page();
}

if (function_exists('acf_add_options_sub_page')) {
    acf_add_options_sub_page('Analytics & GTM');
    acf_add_options_sub_page('CDN');
}

/**
 * Google Analytics
 */
$google_fields = new StoutLogic\AcfBuilder\FieldsBuilder('google_settings');
$google_fields->addSelect('analytics')
    ->addChoice('off', 'Off')
    ->addChoice('ga', 'Google Analytics')
    ->addChoice('gtm', 'Google Tag Manager');
$google_fields->addText('google_analytics_ID', ['instructions' => 'UA-XXXXXXXX-1'])->conditional('analytics', '==', 'ga');
$google_fields->addText('google_tag_manager_ID', ['instructions' => 'GTM-XXXXXXX'])->conditional('analytics', '==', 'gtm');
$google_fields->addText('google_verification_tag_code', ['instructions' => 'ID from the content parameter for the Google Search Console verifications.']);
$google_fields->addTrueFalse('disable_tracking_for_logged_in', ['instructions' => 'If set to "on", this will disable Google Analytics for logged in administrators and editors.', 'ui' => true]);
$google_fields->setLocation('options_page', '==', 'acf-options-analytics-gtm');

/**
 * CDN
 */
$cdn_fields = new StoutLogic\AcfBuilder\FieldsBuilder('cdn_settings');
$cdn_fields->addText(
    'cdn_asset_version',
    [
        'instructions' => 'Enter an asset version. Changing this value will invalidate existing CDN assets.',
        'label' => 'Assets Version'
    ]
);
$cdn_fields->addTrueFalse(
    'cdn_disable',
    [
        'instructions' => 'Set to "yes" to disable the CDN and, instead, return original unresized image URLs. This is useful during devleopment when images may not exist on the remote server.',
        'ui' => true,
        'label' => 'Disable CDN'
    ]
);
$cdn_fields->setLocation('options_page', '==', 'acf-options-cdn');

add_action('acf/init', function () use ($google_fields, $cdn_fields) {
    acf_add_local_field_group($google_fields->build());
    acf_add_local_field_group($cdn_fields->build());
});
