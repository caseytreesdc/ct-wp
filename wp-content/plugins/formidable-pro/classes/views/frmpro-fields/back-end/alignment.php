<p class="frm6 frm_form_field">
	<label for="field_options_align_<?php echo absint( $field['id'] ); ?>">
		<?php esc_html_e( 'Option Layout', 'formidable-pro' ); ?>
	</label>
	<select name="field_options[align_<?php echo absint( $field['id'] ) ?>]" id="field_options_align_<?php echo absint( $field['id'] ); ?>" class="field_options_align" data-changeme="frm_field_id_<?php echo esc_attr( $field['id'] ); ?>" data-changeatt="class">
		<?php foreach ( $columns as $col => $col_label ) { ?>
			<option value="<?php echo esc_attr( $col ); ?>" <?php selected( $field['align'], $col ); ?>>
				<?php echo esc_html( $col_label ); ?>
			</option>
		<?php } ?>
	</select>
</p>
