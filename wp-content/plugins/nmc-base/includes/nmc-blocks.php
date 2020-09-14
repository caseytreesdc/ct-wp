<?php

/**
 * NMC Blocks
 *
 * Helpful Links:
 * https://www.advancedcustomfields.com/resources/acf_register_block_type/
 * https://weblines.com.au/gutenberg-blocks-wide-alignment-full-width/
 * Based on: https://github.com/palmiak/timber-acf-wp-blocks/blob/master/timber-acf-wp-blocks.php
 */

function nmc_block_process_php($template, $dir, $slug)
{
    $block = new \NMC_WP\Builder\FieldsBuilder($slug);
    $block->setLocation('block', '==', 'acf/'.$slug);

    include($template->getPathname());

    $block->registerNow();
    add_filter('allowed_block_types', function ($allowed_block_types, $post) use ($slug) {
        $allowed_block_types[] = 'acf/'.$slug;
        return $allowed_block_types;
    }, 11, 2);
}

function nmc_block_process_twig($template, $dir, $slug)
{
    // Get header info from the found template file(s).
    $file_path    = locate_template($dir . "/template.twig");
    $file_headers = get_file_data(
        $file_path,
        [
            'title'             => 'Title',
            'description'       => 'Description',
            'category'          => 'Category',
            'icon'              => 'Icon',
            'keywords'          => 'Keywords',
            'mode'              => 'Mode',
            'align'             => 'Align',
            'post_types'        => 'PostTypes',
            'supports_align'    => 'SupportsAlign',
            'supports_mode'     => 'SupportsMode',
            'supports_multiple' => 'SupportsMultiple',
            'supports_anchor'   => 'SupportsAnchor',
            'enqueue_style'     => 'EnqueueStyle',
            'enqueue_script'    => 'EnqueueScript',
            'enqueue_assets'    => 'EnqueueAssets',
        ]
    );

    if (empty($file_headers['title']) || empty($file_headers['category'])) {
        return;
    }

    // Keywords exploding with quotes.
    $keywords = str_getcsv($file_headers['keywords'], ' ', '"');

    // Set up block data for registration.
    $data = [
        'name'            => $slug,
        'title'           => $file_headers['title'],
        'description'     => $file_headers['description'],
        'category'        => $file_headers['category'],
        'icon'            => $file_headers['icon'],
        'keywords'        => $keywords,
        'mode'            => $file_headers['mode'],
        'align'           => $file_headers['align'],
        'enqueue_style'   => $file_headers['enqueue_style'],
        'enqueue_script'  => $file_headers['enqueue_script'],
        'enqueue_assets'  => $file_headers['enqueue_assets'],
        'render_callback' => function ($block, $content = '', $is_preview = false, $post_id = 0) {
            // Set up the slug to be useful.
            $context = Timber::get_context();
            $slug    = str_replace('acf/', '', $block['name']);

            $context['acf_block']      = $block;
            $context['post_id']    = $post_id;
            $context['slug']       = $slug;
            $context['is_preview'] = $is_preview;
            $context['block']     = get_fields();
            $classes               = [
                $slug,
                isset($block['className']) ? $block['className'] : null,
                $is_preview ? 'is-preview' : null,
                'align' . $context['acf_block']['align'],
            ];

            $context['classes'] = implode(' ', $classes);

            $context = apply_filters('timber/acf-gutenberg-blocks-data/' . $slug, $context);
            $context = apply_filters('timber/acf-gutenberg-blocks-data/' . $block['id'], $context);

            //$paths = timber_acf_path_render( $slug );
            $paths = ['blocks/'.$slug.'/template.twig'];

            Timber::render($paths, $context);
        },
    ];
    // If the PostTypes header is set in the template, restrict this block to those types.
    if (! empty($file_headers['post_types'])) {
        $data['post_types'] = explode(' ', $file_headers['post_types']);
    }
    // If the SupportsAlign header is set in the template, restrict this block to those aligns.
    if (! empty($file_headers['supports_align'])) {
        $data['supports']['align'] = in_array($file_headers['supports_align'], [ 'true', 'false' ], true) ?
        filter_var($file_headers['supports_align'], FILTER_VALIDATE_BOOLEAN) :
        explode(' ', $file_headers['supports_align']);
    }
    // If the SupportsMode header is set in the template, restrict this block mode feature.
    if (! empty($file_headers['supports_mode'])) {
        $data['supports']['mode'] = 'true' === $file_headers['supports_mode'] ? true : false;
    }
    // If the SupportsMultiple header is set in the template, restrict this block multiple feature.
    if (! empty($file_headers['supports_multiple'])) {
        $data['supports']['multiple'] = 'true' === $file_headers['supports_multiple'] ? true : false;
    }
    // If the SupportsAnchor header is set in the template, restrict this block anchor feature.
    if (! empty($file_headers['supports_anchor'])) {
        $data['supports']['anchor'] = 'true' === $file_headers['supports_anchor'] ? true : false;
    }

    // Register the block with ACF.
    acf_register_block_type($data);
}

/**
 * Create blocks based on templates found in NMC Block Folders
 */
add_action(
    'acf/init',
    function () {
        $nmc_wp_template_dir = get_template_directory() . '/';
        $nmc_block_directories_raw = glob($nmc_wp_template_dir . 'blocks/*', GLOB_ONLYDIR);
        $nmc_block_directories = array_map(function ($d) use ($nmc_wp_template_dir) {
            return str_replace($nmc_wp_template_dir, '', $d);
        }, $nmc_block_directories_raw);
        // Get an array of directories containing blocks.
        $directories = apply_filters('timber/acf-gutenberg-blocks-templates', $nmc_block_directories);

        foreach ($directories as $dir) {
            // Iterate over the directories provided and look for templates.
            $template_directory = new \DirectoryIterator(\locate_template($dir));
            $slug = str_replace('blocks/', '', $dir);
            foreach ($template_directory as $template) {
                switch ($template->getExtension()) {
                    case 'php':
                        nmc_block_process_php($template, $dir, $slug);
                        break;
                    case 'twig':
                        nmc_block_process_twig($template, $dir, $slug);
                        break;
                }
            }
        }
    }
);

add_action('after_setup_theme', function () {
    add_theme_support('align-wide');
});

add_filter('block_categories', function ($categories, $post) {
    return array_merge(
        $categories,
        [[
            'slug' => 'nmc',
            'title' => __('NMC', 'nmc-blocks'),
        ]]
    );
}, 10, 2);

// we can edit this per theme, but this is a minimum list we'll try to support on each site
add_filter('allowed_block_types', function ($allowed_block_types, $post) {
    //return $allowed_block_types; // this is all
    return [
        'core/block',
        'core/code',
        'core/embed',
        'core/freeform',
        'core/heading',
        'core/html',
        'core/image',
        'core/list',
        'core/paragraph',
        'core/preformatted',
        'core/pullquote',
        'core/quote',
        'core/reusableBlock',
        'core/separator',
        'core/spacer',
        'core/subhead',
        'core/table',
        'core/video',
    ];
}, 10, 2);
