<?php

namespace NMC_WP\Builder;

use StoutLogic\AcfBuilder\FlexibleContentBuilder as ACF_FlexibleContentBuilder;
use NMC_WP\Builder\FieldsBuilder as NMC_FieldsBuilder;

class FlexibleContentBuilder extends ACF_FlexibleContentBuilder
{
    public function addComponents($components)
    {
        foreach ($components as $type => $component) {
            $layout = $this->addLayout($type);
            $component($layout);
        }
    }

    /**
     * Add Layout
     *
     * Extends StoutLogic\AcfBuilder\FlexibleContentBuilder to
     * add our own FieldsBuilder.
     */
    public function addLayout($layout, $args = [])
    {
        if ($layout instanceof FieldsBuilder) {
            $layout = clone $layout;
        } else {
            $layout = new NMC_FieldsBuilder($layout, $args);
        }

        $layout = $this->initializeLayout($layout, $args);
        $this->pushLayout($layout);

        return $layout;
    }

    /*
    * Stuff added by joel
    */
    private $blockdefaults = [];

    public function addBlockDefaults($arg)
    {
        $this->blockdefaults[] = $arg;
    }
    private function applyBlockDefaults($layout)
    {
        foreach ($this->blockdefaults as $defset) {
            if (is_array($defset)) {
                $layout->addFields($defset);
            } else {
                $defset->__invoke($layout);
            }
        }
    }

    public function addBlockFolder($path)
    {
        $blocks = $this;
        foreach (glob($path . '*.php') as $filename) {
            include $filename;
        }
    }

    public function addBlock()
    {
        $args = func_get_args();
        if (is_array($args[1])) {
            call_user_func_array([$this,"addArrayBlock"], $args);
        } else {
            call_user_func_array([$this,"addFunctionBlock"], $args);
        }
    }

    private function addArrayBlock($slug, $fields, $tablabel = 'Content')
    {
        $layout = $this->addLayout($slug);
        $layout->addFields(array_merge(
            [[$tablabel,'Tab']],
            $fields
        ));
        $this->applyBlockDefaults($layout);
    }

    private function addFunctionBlock($name, $callback, $tablabel = 'Content')
    {
        $layout = $this->addLayout($name);
        $layout->addFields([[$tablabel,'Tab']]);
        $callback($layout);
        $this->applyBlockDefaults($layout);
    }
}
