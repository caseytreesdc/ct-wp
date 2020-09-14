<?php
/**
/*
 * Template Name: Modules Page
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

Timber::render( array( 'template-modules.twig' ), $context );