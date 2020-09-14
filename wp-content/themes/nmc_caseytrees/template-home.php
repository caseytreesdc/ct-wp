<?php
/**
/*
 * Template Name: Home Page
 * Description: A Page Template for the home page
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

Timber::render( array( 'template-home.twig' ), $context );