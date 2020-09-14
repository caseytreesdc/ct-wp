<?php

/**
 * NMC Base Wordpress Settings
 */

add_action('admin_menu', 'nmc_base_add_admin_menu');
add_action('admin_init', 'nmc_base_settings_init');

function nmc_base_add_admin_menu()
{
    add_options_page('NMC Base', 'NMC Base', 'manage_options', 'nmc_base', 'nmc_base_options_page');
}

function nmc_base_settings_init()
{
    register_setting('nmcBase', 'nmc_base_settings');

    add_settings_section(
        'nmc_base_cache_section',
        __('Caching', 'nmc-base'),
        function () {
            echo __('', 'nmc-base');
        },
        'nmcBase'
    );

    add_settings_field(
        'nmc_base_settings_caching_strategy',
        __('Caching Strategy', 'nmc-base'),
        function () {
            $options = get_option('nmc_base_settings');
            $value  = $options['caching_strategy'] ?? null;

            $strategies = [
                'default' => 'Refresh Everything (Default)',
                'performance' => 'Refresh Important Pages (Better Performance)'
            ];
        
            $html = '<p><select name="nmc_base_settings[caching_strategy]">';
            foreach ($strategies as $key => $name) {
                $html .= '<option value="'.$key.'"';

                if ($key == $value) {
                    $html .= ' selected';
                }

                $html .= '>'.$name.'</option>';
            }
            $html .= '</select></p>';

            $html .= '<p>When this site\'s content changes, what should happen? The system can either refresh every page, which would ensure that the change is instantly visible everywhere, or it can just refresh the page that was saved along with important pages for better performance. If the better performance option is selected, it is always possible to refresh everything using the \'Clear Cache\' button in the admin menu bar.</p>';

            echo $html;
        },
        'nmcBase',
        'nmc_base_cache_section'
    );

    add_settings_field(
        'nmc_base_settings_caching_important_pages',
        __('Important Pages', 'nmc-base'),
        function () {
            $options = get_option('nmc_base_settings');
            $value  = $options['caching_important_pages'] ?? null;

            $html = '<textarea style="min-width: 400px; min-height: 80px;" name="nmc_base_settings[caching_important_pages]">'.$value.'</textarea>';

            $html .= '<p>When a post is saved, that post\'s url will be cleared along with any other urls in its path. (For example, saving /news/some-article will clear /news/some-article, /news/, and /). If there are other important pages that should be cleared on every save, list their paths here. The entered paths should not include your domain or https and should instead begin with a path. So use /my-important-url instead of https://domain.com/my-important-url</p>';

            echo $html;
        },
        'nmcBase',
        'nmc_base_cache_section'
    );

    add_settings_section(
        'nmc_base_rest_api_section',
        __('REST API', 'nmc-base'),
        function () {
            echo __('In order to use the NMC Social APIs and other REST API-base services, you need an API key, which you can generate at <a href="https://restservices.epicenter1.com/" target="_blank">https://restservices.epicenter1.com/</a>', 'nmc-base');
        },
        'nmcBase'
    );

    add_settings_field(
        'nmc_base_settings_api_key',
        __('API Key', 'nmc-base'),
        function () {
            $options = get_option('nmc_base_settings');

            // Check for older key from settings.
            $key = $options['api_key'] ?? null;
            if (!$key) {
                $social_options = get_option('nmc_social_settings');
                $key = $social_options['api_key'] ?? null;
            }
        
            $html = '<input type="text" style="min-width: 400px" name="nmc_base_settings[api_key]" value="'.$key.'">';
            echo $html;
        },
        'nmcBase',
        'nmc_base_rest_api_section'
    );
}

function nmc_base_options_page()
{
    ?>
	<form action='options.php' method='post'>
		<h2>NMC Base Settings</h2>

		<?php
        settings_fields('nmcBase');
    do_settings_sections('nmcBase');
    submit_button(); ?>

	</form>
	<?php
}
