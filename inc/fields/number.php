<?php
/**
 * Number field class.
 */
class RWMB_Number_Field extends RWMB_Input_Field
{
	public $type = 'number';
	public $step = 1;
	public $min  = 0;
	public $max  = false;

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	function get_attributes( $value = null )
	{
		$attributes = parent::get_attributes( $value );
		$attributes = wp_parse_args( $attributes, array(
			'step' => $field['step'],
			'max'  => $field['max'],
			'min'  => $field['min'],
		) );
		return $attributes;
	}
}
