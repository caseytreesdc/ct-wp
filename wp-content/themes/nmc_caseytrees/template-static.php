<?php
/**
/*
 * Template Name: Static Page
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

Timber::render( array( 'template-static.twig' ), $context );