<?php

/**
 * Base field class which defines all necessary methods.
 * Fields must inherit this class and overwrite methods with its own.
 */
abstract class RWMB_Field
{
	public $type        = '';
	public $object_type = 'post';
	public $id          = '';
	public $name        = '';
	public $multiple    = false;
	public $std         = '';
	public $desc        = '';
	public $format      = '';
	public $before      = '';
	public $after       = '';
	public $field_name  = '';
	public $placeholder = '';

	public $clone      = false;
	public $max_clone  = 0;
	public $sort_clone = false;

	public $class      = '';
	public $disabled   = false;
	public $required   = false;
	public $attributes = array();

	function __construct( $args = array() )
	{
		$args['field_name']  = isset( $args['id'] ) ? $args['id'] : '',
		$defaults            = get_object_vars( $this );
		$args                = wp_parse_args( $args, $defaults );

		//Set args
		foreach( $defaults as $key => $value ) {
			$this->$key = $args[$key];
		}

	}
	
	/**
	 * Add actions
	 */
	public static function add_actions()
	{
	}

	/**
	 * Enqueue scripts and styles
	 */
	public static function admin_enqueue_scripts()
	{
	}

	/**
	 * Show field HTML
	 * Filters are put inside this method, not inside methods such as "meta", "html", "begin_html", etc.
	 * That ensures the returned value are always been applied filters
	 * This method is not meant to be overwritten in specific fields
	 *
	 * @param bool  $saved
	 *
	 * @return string
	 */
	public function show( $saved )
	{
		$post    = get_post();
		$post_id = isset( $post->ID ) ? $post->ID : 0;

		$meta = $this->meta( $post_id, $saved );
		$meta = self::filter( 'field_meta', $meta, $field, $saved );

		$begin = $this->begin_html( $meta );
		$begin = self::filter( 'begin_html', $begin, $field, $meta );

		// Separate code for cloneable and non-cloneable fields to make easy to maintain

		// Cloneable fields
		if ( $this->clone )
		{
			$field_html = RWMB_Clone::html( $meta, $field );
		}
		// Non-cloneable fields
		else
		{
			// Call separated methods for displaying each type of field
			$field_html = $this->html( $meta );
			$field_html = self::filter( 'html', $field_html, $field, $meta );
		}

		$field_html = $this->end_html( $meta );
		$end = self::filter( 'end_html', $end, $field, $meta );

		$html = self::filter( 'wrapper_html', "$begin$field_html$end", $field, $meta );

		// Display label and input in DIV and allow user-defined classes to be appended
		$classes = "rwmb-field rwmb-{$this->type}-wrapper " . $this->class;
		if ( 'hidden' === $this->type )
			$classes .= ' hidden';
		if ( ! empty( $this->required ) )
			$classes .= ' required';

		$outer_html = sprintf(
			$this->before . '<div class="%s">%s</div>' . $this->after,
			trim( $classes ),
			$html
		);
		$outer_html = self::filter( 'outer_html', $outer_html, $field, $meta );

		echo $outer_html;
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 *
	 * @return string
	 */
	public function html( $meta )
	{
		return '';
	}

	/**
	 * Show begin HTML markup for fields
	 *
	 * @param mixed $meta
	 *
	 * @return string
	 */
	public function begin_html( $meta )
	{
		$field_label = '';
		if ( $this->name )
		{
			$field_label = sprintf(
				'<div class="rwmb-label"><label for="%s">%s</label></div>',
				$this->id,
				$this->name
			);
		}

		$data_max_clone = is_numeric( $this->max_clone ) && $this->max_clone > 1 ? ' data-max-clone=' . $this->max_clone : '';

		$input_open = sprintf(
			'<div class="rwmb-input"%s>',
			$data_max_clone
		);

		return $field_label . $input_open;
	}

	/**
	 * Show end HTML markup for fields
	 *
	 * @param mixed $meta
	 *
	 * @return string
	 */
	public function end_html( $meta )
	{
		return RWMB_Clone::add_clone_button( $field ) . $this->element_description() . '</div>';
	}

	/**
	 * Display field description.
	 *
	 * @return string
	 */
	protected function element_description()
	{
		$id = $this->id ? " id='{$this->id}-description'" : '';
		return $this->desc ? "<p{$id} class='description'>{$this->desc}</p>" : '';
	}

	/**
	 * Get meta value
	 *
	 * @param int   $post_id
	 * @param bool  $saved
	 *
	 * @return mixed
	 */
	public function meta( $post_id, $saved )
	{
		/**
		 * For special fields like 'divider', 'heading' which don't have ID, just return empty string
		 * to prevent notice error when displaying fields
		 */
		if ( empty( $this->id ) )
			return '';

		$single = $this->clone || ! $this->multiple;
		$meta   = get_metadata( $this->object_type, $post_id, $this->id, $single );

		// Use $this->std only when the meta box hasn't been saved (i.e. the first time we run)
		$meta = ! $saved ? $this->std : $meta;

		// Escape attributes
		$meta = $this->esc_meta( $meta );

		// Make sure meta value is an array for clonable and multiple fields
		if ( $this->clone || $this->multiple )
		{
			if ( empty( $meta ) || ! is_array( $meta ) )
			{
				/**
				 * Note: if field is clonable, $meta must be an array with values
				 * so that the foreach loop in self::show() runs properly
				 * @see self::show()
				 */
				$meta = $this->clone ? array( '' ) : array();
			}
		}

		return $meta;
	}

	/**
	 * Escape meta for field output
	 *
	 * @param mixed $meta
	 *
	 * @return mixed
	 */
	public function esc_meta( $meta )
	{
		return is_array( $meta ) ? array_map( __METHOD__, $meta ) : esc_attr( $meta );
	}

	/**
	 * Set value of meta before saving into database
	 *
	 * @param mixed $new
	 * @param mixed $old
	 * @param int   $post_id
	 *
	 * @return int
	 */
	public static function value( $new, $old, $post_id )
	{
		return $new;
	}

	/**
	 * Save meta value
	 *
	 * @param $new
	 * @param $old
	 * @param $post_id
	 */
	public static function save( $new, $old, $post_id )
	{
		$name = $this->id;

		// Remove post meta if it's empty
		if ( '' === $new || array() === $new )
		{
			delete_metadata( $this->object_type, $post_id, $name );
			return;
		}

		// If field is cloneable, value is saved as a single entry in the database
		if ( $this->clone )
		{
			// Remove empty values
			$new = (array) $new;
			foreach ( $new as $k => $v )
			{
				if ( '' === $v || array() === $v )
					unset( $new[$k] );
			}
			// Reset indexes
			$new = array_values( $new );
			update_metadata( $this->object_type, $post_id, $name, $new );
			return;
		}

		// If field is multiple, value is saved as multiple entries in the database (WordPress behaviour)
		if ( $this->multiple )
		{
			$new_values = array_diff( $new, $old );
			foreach ( $new_values as $new_value )
			{
				add_metadata( $this->object_type, $post_id, $name, $new_value, false );
			}
			$old_values = array_diff( $old, $new );
			foreach ( $old_values as $old_value )
			{
				delete_metadata( $this->object_type, $post_id, $name, $old_value );
			}
			return;
		}

		// Default: just update post meta
		update_metadata( $this->object_type, $post_id, $name, $new );
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function get_attributes( $value = null )
	{
		$attributes = wp_parse_args( $this->attributes, array(
			'disabled' => $this->disabled,
			'required' => $this->required,
			'class'    => "rwmb-{$this->type}",
			'id'       => $this->id,
			'name'     => $this->field_name,
		) );

		return $attributes;
	}

	/**
	 * Renders an attribute array into an html attributes string
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function render_attributes( $attributes )
	{
		$output = '';

		foreach ( $attributes as $key => $value )
		{
			if ( false === $value || '' === $value )
				continue;

			if ( is_array( $value ) )
				$value = json_encode( $value );

			$output .= sprintf( true === $value ? ' %s' : ' %s="%s"', $key, esc_attr( $value ) );
		}

		return $output;
	}

	/**
	 * Get the field value
	 * The difference between this function and 'meta' function is 'meta' function always returns the escaped value
	 * of the field saved in the database, while this function returns more meaningful value of the field, for ex.:
	 * for file/image: return array of file/image information instead of file/image IDs
	 *
	 * Each field can extend this function and add more data to the returned value.
	 * See specific field classes for details.
	 *
	 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Field value
	 */
	public function get_value( $args = array(), $post_id = null )
	{
		// Some fields does not have ID like heading, custom HTML, etc.
		if ( empty( $this->id ) )
		{
			return '';
		}

		if ( ! $post_id )
			$post_id = get_the_ID();

		// Get raw meta value in the database, no escape
		$single = $this->clone || ! $this->multiple;
		$value  = get_metadata( $this->object_type, $post_id, $this->id, $single );

		// Make sure meta value is an array for cloneable and multiple fields
		if ( $this->clone || $this->multiple )
		{
			$value = is_array( $value ) && $value ? $value : array();
		}

		return $value;
	}

	/**
	 * Output the field value
	 * Depends on field value and field types, each field can extend this method to output its value in its own way
	 * See specific field classes for details.
	 *
	 * Note: we don't echo the field value directly. We return the output HTML of field, which will be used in
	 * rwmb_the_field function later.
	 *
	 * @use self::get_value()
	 * @see rwmb_the_value()
	 *
	 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string HTML output of the field
	 */
	public function the_value( $args = array(), $post_id = null )
	{
		$value = $this->get_value( $args, $post_id );
		return $this->format_value( $value );
	}

	/**
	 * Format value for the helper functions.
	 * @param string|array $value The field meta value
	 * @return string
	 */
	public function format_value( $value )
	{
		if ( ! is_array( $value ) )
		{
			return $this->format_single_value( $value );
		}
		$output = '<ul>';
		foreach ( $value as $subvalue )
		{
			$output .= '<li>' . $this->format_value( $subvalue ) . '</li>';
		}
		$output .= '</ul>';
		return $output;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 * @param string $value The value
	 * @return string
	 */
	public function format_single_value( $value )
	{
		return $value;
	}


	/**
	 * Get field class name
	 *
	 * @param array $field Field array
	 * @return string Field class name
	 */
	public static function get_class_name( $field )
	{
		$type = $this->type;
		if ( 'file_advanced' == $this->type )
		{
			$type = 'media';
		}
		if ( 'plupload_image' == $this->type )
		{
			$type = 'image_upload';
		}
		$type  = str_replace( array( '-', '_' ), ' ', $type );
		$class = 'RWMB_' . ucwords( $type ) . '_Field';
		$class = str_replace( ' ', '_', $class );
		return class_exists( $class ) ? $class : 'RWMB_Input_Field';
	}

	/**
	 * Apply various filters based on field type, id.
	 * Filters:
	 * - rwmb_{$name}
	 * - rwmb_{$this->type}_{$name}
	 * - rwmb_{$this->id}_{$name}
	 * @return mixed
	 */
	public static function filter()
	{
		$args = func_get_args();

		// 3 first params must be: filter name, value, field. Other params will be used for filters.
		$name  = array_shift( $args );
		$value = array_shift( $args );
		$field = array_shift( $args );

		// List of filters
		$filters = array(
			'rwmb_' . $name,
			'rwmb_' . $this->type . '_' . $name,
		);
		if ( isset( $this->id ) )
		{
			$filters[] = 'rwmb_' . $this->id . '_' . $name;
		}

		// Filter params: value, field, other params. Note: value is changed after each run.
		array_unshift( $args, $field );
		foreach ( $filters as $filter )
		{
			$filter_args = $args;
			array_unshift( $filter_args, $value );
			$value = apply_filters_ref_array( $filter, $filter_args );
		}

		return $value;
	}
}
