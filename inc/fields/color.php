<?php

/**
 * Color field class.
 */
class RWMB_Color_Field extends RWMB_Text_Field
{
	public $type       = 'text';
	public $size       = 7;
	public $maxlength  = 7;
	public $pattern    = '^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$';
	public $js_options = array(
		'defaultColor' => false,
		'hide'         => true,
		'palettes'     => true,
	);

	/**
	 * Enqueue scripts and styles
	 */
	static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'rwmb-color', RWMB_CSS_URL . 'color.css', array( 'wp-color-picker' ), RWMB_VER );
		wp_enqueue_script( 'rwmb-color', RWMB_JS_URL . 'color.js', array( 'wp-color-picker' ), RWMB_VER, true );
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
		$attributes = wp_parse_args( $attributes, array(
			'data-options' => wp_json_encode( $this->js_options ),
		) );
		$attributes['type'] = 'text';

		return $attributes;
	}

	/**
	 * Format a single value for the helper functions.
	 * @param string $value The value
	 * @return string
	 */
	function format_single_value( $value )
	{
		return sprintf( "<span style='display:inline-block;width:20px;height:20px;border-radius:50%%;background:%s;'></span>", $value );
	}
}
