<?php
/**
 * Button field class.
 */
class RWMB_Button_Field extends RWMB_Field
{
	function __construct( $args = array() )
	{
		$args['std'] = $args['std'] ? $args['std'] : __( 'Click me', 'meta-box' );
		parent::__construct( $args );
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @return string
	 */
	function html( $meta )
	{
		$attributes = self::get_attributes();
		return sprintf( '<a href="#" %s>%s</a>', self::render_attributes( $attributes ), $field['std'] );
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param mixed $value
	 * @return array
	 */
	function get_attributes( $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes['class'] .= ' button hide-if-no-js';

		return $attributes;
	}
}
