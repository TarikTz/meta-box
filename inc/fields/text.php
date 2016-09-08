<?php
/**
 * Text field class.
 */
class RWMB_Text_Field extends RWMB_Input_Field
{
	public $size      = 30;
	public $maxlength = false;
	public $pattern   = false;

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, array(
			'size'        => $this->size,
			'maxlength'   => $this->maxlength,
			'pattern'     => $this->pattern,
			'placeholder' => $this->placeholder,
		) );

		return $attributes;
	}
}
