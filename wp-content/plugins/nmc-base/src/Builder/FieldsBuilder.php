<?php

namespace NMC_WP\Builder;

use StoutLogic\AcfBuilder\FieldsBuilder as ACF_FieldsBuilder;
use NMC_WP\Builder\FlexibleContentBuilder as NMC_FlexibleContentBuilder;

class FieldsBuilder extends ACF_FieldsBuilder
{
    private $globalFieldGroup;

    private $components;

    private $presets = [
        'SocialAccounts' => \NMC\WP_Fields\Presets\SocialAccounts::class
    ];

    /**
     * Add Global Fields
     */
    public function addGlobalFields($exclude = [], $tab = 'Block Options')
    {
        if ($tab) {
            $this->addTab($tab);
        }

        $keys = [];
        $group = $this->getGlobalFieldGroup();

        if (is_array($group)) {
            foreach ($group['fields'] as $field) {
                $keys[] = $field['key'];
            }
        }

        $this->addField('_block_fields', 'clone', ['clone' => $keys]);
    }

    /**
     * Force Hide on Screen
     *
     * Manipulates local groups array to
     * set the last element to hide whatever
     * needs hiding.
     *
     * @param  array  $hide
     * @return void
     */
    public function forceHideOnScreen($hide = [])
    {
        add_action('acf/init', function () use ($hide) {
            if ($groups = acf_local()->groups) {
                end($groups);
                $key  = key($groups);

                $first = $groups[$key];

                $first['hide_on_screen'] = $first['hide_on_screen'] ?? [];
                $first['hide_on_screen'] = array_merge($first['hide_on_screen'], $hide);

                acf_local()->groups[$key] = $first;
            }
        }, 100);
    }

    public function setGlobalFieldGroup($group)
    {
        $this->globalFieldGroup = $group;
    }

    public function getGlobalFieldGroup()
    {
        return $this->globalFieldGroup;
    }

    public function setComponents($components)
    {
        $this->components = $components;
    }

    public function getComponent($key)
    {
        return $this->components[$key] ?? null;
    }

    public function getComponents()
    {
        return $this->components;
    }

    public function addComponent($key)
    {
        $component = $this->getComponent($key);

        if (!$component) {
            return false;
        }

        $component($this);
    }

    /**
     * Add Flexible Content
     *
     * Extends \StoutLogic\AcfBuilder\FieldsBuilder to serve
     * our own NMC_FlexibleContentBuilder.
     */
    public function addFlexibleContent($name, array $args = [])
    {
        return $this->initializeField(new NMC_FlexibleContentBuilder($name, 'flexible_content', $args));
    }

    public function __call($name, $args)
    {
        if (substr($name, 0, 3) == 'add' && $args[0]) {
            $presetName = substr($name, 3);

            if (array_key_exists($presetName, $this->presets)) {
                $presetBuilder = new $this->presets[$presetName];
                return $presetBuilder->add($this);
            }
        }
    }


    /*
    * Stuff added by joel
    */

    // Override parent and funnel to the right place
    // params are pointless here, and just supress php warnings about
    // this class not matchiing the parent
    public function addField($name = null, $type = null, array $args = [])
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            call_user_func_array([$this,"addArrayField"], $args);
        } else {
            call_user_func_array('parent::addField', $args);
        }
    }

    // We'll actually take a list...let's see what happens here
    public function addFields($fields)
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            $this->addArrayFields($args[0]);
        } else {
            parent::addFields($fields);
        }
    }

    private function addArrayField($field)
    {
        $type = $field[1];
        $slug = $field[0];
        $options = isset($field[2]) ? $field[2] : [];
        if ($type == 'Repeater') {
            $fields = new FieldsBuilder('field');
            $fields->addArrayFields($field[3]);
            $this
                ->addRepeater($slug, $options)
                ->addFields($fields)
                ->endRepeater();
        } else {
            $typeMethod = 'add' . $type;
            $this->$typeMethod($slug, $options);
        }
    }

    private function addArrayFields($fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    public function register()
    {
        $fields = $this;

        add_action('acf/init', function () use ($fields) {
            acf_add_local_field_group($fields->build());
        });
    }

    public function registerNow()
    {
        $fields = $this;
        acf_add_local_field_group($fields->build());
    }
}
