<?php
/**
 * Block: Additional Fields - Text
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/blocks/additional-fields/text.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://m.tri.be/1ajx
 *
 * @version5.1.2
 *
 */
$label = $this->attr( 'label' );
$value = $this->attr( 'value' );

if ( empty( $value ) ) {
	return;
}
?>
<div class="tribe-block tribe-block__additional-field tribe-block__additional-field__text">
	<h3><?php echo esc_html( $label ); ?></h3>
	<?php echo esc_html( $value ); ?>
</div>
