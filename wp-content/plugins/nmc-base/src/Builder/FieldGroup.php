<?php

namespace NMC_WP\Builder;

use NMC_WP\Builder\FieldsBuilder;

/**
 * Field Group
 *
 * Wrapper class for adding field groups
 * via a callback.
 */
class FieldGroup
{
    protected $name;
    protected $builder;

    public function __construct($name = null)
    {
        if ($name) {
            $this->setName($name);
            $this->setupFieldBuilder();
        }
    }

    public function setupFieldBuilder()
    {
        $this->setBuilder(new FieldsBuilder($this->getName()));
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setBuilder($builder)
    {
        $this->builder = $builder;
    }

    public function getBuilder()
    {
        return $this->builder;
    }

    public function register()
    {
        $group = $this->getBuilder();


        add_action('acf/init', function () use ($group) {
            acf_add_local_field_group($group->build());
        });
    }

    /**
     * Add
     *
     * Single function to register a field group.
     *
     * @param string $name
     * @param callable $callback
     */
    public static function add($name, $callback)
    {
        $group = new static($name);

        $callback($group->getBuilder());

        $group->register();
    }
}
