<?php
/**
/*
 * Template Name: Convio Page
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

Timber::render( array( 'template-convio.twig' ), $context );