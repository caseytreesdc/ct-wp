<?php
$context = Timber::get_context();
// $context['events_raw'] = Timber::get_posts();
$context['show_tfa_forms'] = false;
Timber::render('template-tribe-events.twig', $context);