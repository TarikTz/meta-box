<?php

/**
 * Fieldset text class.
 */
class RWMB_Fieldset_Text_Field extends RWMB_Text_Field
{
	function __construct( $args = array() )
	{
		$args['multiple'] = false;
		parent::__construct( $args );
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 *
	 * @return string
	 */
	function html( $meta )
	{
		$html = array();
		$tpl  = '<label>%s %s</label>';

		foreach ( $this->options as $key => $label )
		{
			$value                       = isset( $meta[$key] ) ? $meta[$key] : '';
			$this->attributes['name'] = $this->field_name . "[{$key}]";
			$html[]                      = sprintf( $tpl, $label, parent::html( $value ) );
		}

		$out = '<fieldset><legend>' . $this->desc . '</legend>' . implode( ' ', $html ) . '</fieldset>';

		return $out;
	}

	/**
	 * Do not show field description.
	 * @param array $field
	 * @return string
	 */
	public static function element_description( $field )
	{
		return '';
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param mixed $value
	 * @return array
	 */
	public function get_attributes( $value = null )
	{
		$attributes         = parent::get_attributes( $value );
		$attributes['id']   = false;
		$attributes['type'] = 'text';
		return $attributes;
	}

	/**
	 * Format value for the helper functions.
	 * @param string|array $value The field meta value
	 * @return string
	 */
	public function format_value( $value )
	{
		$output = '<table><thead><tr>';
		foreach ( $this->options as $label )
		{
			$output .= "<th>$label</th>";
		}
		$output .= '<tr>';

		if ( ! $this->clone )
		{
			$output .= self::format_single_value( $field, $value );
		}
		else
		{
			foreach ( $value as $subvalue )
			{
				$output .= self::format_single_value( $field, $subvalue );
			}
		}
		$output .= '</tbody></table>';
		return $output;
	}

	/**
	 * Format a single value for the helper functions.
	 * @param array $field Field parameter
	 * @param array $value The value
	 * @return string
	 */
	public function format_single_value( $value )
	{
		$output = '<tr>';
		foreach ( $value as $subvalue )
		{
			$output .= "<td>$subvalue</td>";
		}
		$output .= '</tr>';
		return $output;
	}
}
