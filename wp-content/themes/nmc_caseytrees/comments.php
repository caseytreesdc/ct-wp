<?php
if ( post_password_required() ) {
	return;
}
?>

<div id="comments">

	<?php if ( have_comments() ) : ?>

		<h3 class="comments-title"><?php printf( _nx( 'One response to &ldquo;%2$s&rdquo;', '%1$s responses to &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'twentyfifteen' ), number_format_i18n( get_comments_number() ), get_the_title() ); ?></h3>

		<ol class="comment-list">
			<?php wp_list_comments(); ?>
		</ol>

	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php _e( 'Comments are closed.', 'twentyfifteen' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>

</div><!-- .comments-area -->
