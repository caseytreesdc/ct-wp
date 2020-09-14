#!/usr/bin/env php
<?php

/**
 * Asset Compilation
 *
 * Custom assets.json processor
 * for block compilation.
 */

function nmc_get_current_theme_no_db()
{
    $themeFolders = glob(__DIR__.'/../../../themes/*', GLOB_ONLYDIR);
    $themeFolders = array_map(function ($path) {
        return basename($path);
    }, $themeFolders);
    $themeFolders = array_filter($themeFolders, function ($folderName) {
        if (strpos($folderName, 'nmc_') !== false) {
            return true;
        }
    });

    return current($themeFolders);
}

$jsonPath = $_SERVER['argv'][1] ?? null;

if (!$jsonPath) {
    echo json_encode($assets, JSON_PRETTY_PRINT);
    exit;
}

if (!file_exists($jsonPath)) {
    echo json_encode([], JSON_PRETTY_PRINT);
    exit;
}

$assetJsonContent = file_get_contents($jsonPath);
$assetJsonContentArray = @json_decode($assetJsonContent, true);

if (!is_array($assetJsonContentArray)) {
    echo json_encode([], JSON_PRETTY_PRINT);
    exit;
}

// Do they specify a blocks var?
if ($assetJsonContentArray['blocks']) {
    unset($assetJsonContentArray['blocks']);

    $allCssKey = '{css_dest_path}/blocks/all.min.css';
    $assetJsonContentArray['less'][$allCssKey] = [
        'files' => [
            '{asset_src_path}/less/gutenberg-editor.less'
        ],
        'paths' => []
    ];

    $blocksAssetsArray = [];
    $themePath = __DIR__.'/../../../themes/'.nmc_get_current_theme_no_db();
    $blocksPath = $themePath.'/blocks';
    $blocks = glob($blocksPath.'/*', GLOB_ONLYDIR);
    foreach ($blocks as $blockPath) {
        $blockPathBase = realpath($blockPath.'/../../../../../../');
        $blockPath = realpath($blockPath);
        $blockRelPath = ltrim(str_replace($blockPathBase, '', $blockPath), '/');
        $blockSlug = basename($blockPath);

        $mainStyleFile = $blockPath.'/style.less';

        // We don't want to do anything
        // unless we have that style file
        if (!file_exists($mainStyleFile)) {
            continue;
        }

        $assetJsonContentArray['less']['{css_dest_path}/blocks/'.$blockSlug.'.min.css'] = [
            'files' => [$blockRelPath.'/style.less'],
            'paths' => [$blockRelPath, '{asset_src_path}/less']
        ];

        $assetJsonContentArray['less'][$allCssKey]['files'][] = $blockRelPath.'/style.less';
        $assetJsonContentArray['less'][$allCssKey]['paths'][] = $blockRelPath;
    }

    if ($assetJsonContentArray['less'][$allCssKey]['files']) {
        $assetJsonContentArray['less'][$allCssKey]['paths'][] = '{asset_src_path}/less';
    }
}

echo json_encode($assetJsonContentArray, JSON_PRETTY_PRINT);
