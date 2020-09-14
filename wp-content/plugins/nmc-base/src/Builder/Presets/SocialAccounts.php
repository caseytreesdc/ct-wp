<?php

namespace NMC_WP\Builder\Presets;

/**
 * Social Accounts
 *
 * Adds a standard social media
 * accounts repeater.
 */
class SocialAccounts
{
    public function add($builder, $name = 'social_accounts', $customTypes = [])
    {
        $repeater = $builder->addRepeater($name);

        $repeater->addUrl('social_account_url', ['instructions' => 'Full url to social media account profile.']);

        $choices = ($customTypes) ? $customTypes : $this->getDefaultChoices();
        $repeater->addSelect('social_account_type', ['choices' => $choices]);
    }

    public function getDefaultChoices()
    {
        return [
            'facebook' 		=> 'Facebook',
            'twitter' 		=> 'Twitter',
            'instagram' 	=> 'Instagram'
        ];
    }
}
