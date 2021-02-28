<?php
/**
 * Admin View: Widget Taxonomy Input Component
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/components/taxonomy.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @version 5.2.0
 *
 * @var string $label       Label for the taxonomy input.
 * @var string $id          ID of the taxonomy input.
 * @var string $name        Name attribute for the taxonomy input.
 * @var string $disabled    The list of chosen items for select2 to disable.
 * @var string $placeholder The input placeholder.
 */

?>
<div
	class="tribe-widget-form-control tribe-widget-form-control--multiselect"
>
	<label
		class="tribe-widget-form-control__label"
		for="<?php echo esc_attr( $id ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</label>
	<select
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="widefat tribe-widget-form-control__input calendar-widget-add-filter tribe-widget-select2"
		placeholder="<?php echo esc_attr( $placeholder ); ?>"
		data-disabled="<?php echo esc_attr( $disabled ); ?>"
		data-source="terms"
		data-hide-search
		data-prevent-clear
	>
		<option selected="selected" value="-1"><?php esc_html_e( 'Select a Taxonomy Term', 'tribe-events-calendar-pro' ); ?></option>
	</select>
</div>
