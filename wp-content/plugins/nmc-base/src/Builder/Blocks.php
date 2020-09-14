<?php

namespace NMC_WP\Builder;

class Blocks
{
    public $blocksBuilder;

    public $fieldsBuilder;

    public $globalFieldGroup;

    public function __construct($groupName = 'page_content', $flexName = 'blocks')
    {
        $this->fieldsBuilder = new FieldsBuilder($groupName);
        $this->blocksBuilder = $this->fieldsBuilder->addFlexibleContent($flexName);
    }

    /**
     * Setup Global Fields
     *
     * @param  callable $callback
     * @param  string $name
     * @return
     */
    public function setupGlobalFields($callback, $name = 'reusable_block_fields')
    {
        $this->globalFieldGroup = $name;

        $builder = new FieldsBuilder($name);
        $callback($builder);

        $this->globalFieldGroup = $builder->build();

        // Fix ACF not checking for an array before
        // trying to go through location data.
        $this->globalFieldGroup['location'] = [];

        $fields = $this->globalFieldGroup;
        add_action('acf/init', function () use ($fields) {
            acf_add_local_field_group($fields);
        });
    }

    public function getBuilder()
    {
        return $this->fieldsBuilder;
    }

    public function registerComponent($name, $callback)
    {
        $this->components[$name] = $callback;
    }

    public function hideContent()
    {
        $this->fieldsBuilder->forceHideOnScreen(['the_content']);
    }

    public function addBlock($name, $callback)
    {
        $block = $this->blocksBuilder->addLayout($name);

        $block->setGlobalFieldGroup($this->globalFieldGroup);
        $block->setComponents($this->components);

        $callback($block);
    }

    public function setLocation($type, $operator, $value)
    {
        return $this->fieldsBuilder->setLocation($type, $operator, $value);
    }

    public function register()
    {
        $fields = $this->fieldsBuilder;

        add_action('acf/init', function () use ($fields) {
            acf_add_local_field_group($fields->build());
        });
    }
}
