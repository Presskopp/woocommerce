<?php
/**
 * WooCommerce Product Form
 *
 * @package Woocommerce ProductForm
 */

namespace Automattic\WooCommerce\Internal\Admin\ProductForm;

use WP_Error;

/**
 * Contains logic for the WooCommerce Product Form.
 */
class Form {
	/**
	 * Class instance.
	 *
	 * @var Form instance
	 */
	protected static $instance = null;

	/**
	 * Store form fields.
	 *
	 * @var array
	 */
	protected static $form_fields = array();

	/**
	 * Store form cards.
	 *
	 * @var array
	 */
	protected static $form_cards = array();

	/**
	 * Store form sections.
	 *
	 * @var array
	 */
	protected static $form_sections = array();

	/**
	 * Get class instance.
	 */
	final public static function instance() {
		if ( ! static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Init.
	 */
	public function init() {    }

	/**
	 * Adds a field to the product form.
	 *
	 * @param string $id Field id.
	 * @param string $plugin_id Plugin id.
	 * @param array  $args Array containing the necessary arguments.
	 *     $args = array(
	 *       'type'            => (string) Field type. Required.
	 *       'section'         => (string) Field location. Required.
	 *       'order'           => (int) Field order.
	 *       'properties'      => (array) Field properties.
	 *       'name'            => (string) Field name.
	 *     ).
	 * @return Field|WP_Error New field or WP_Error.
	 */
	public static function add_field( $id, $plugin_id, $args ) {
		$new_field = self::create_item( 'field', 'Field', $id, $plugin_id, $args );
		if ( is_wp_error( $new_field ) ) {
			return $new_field;
		}
		self::$form_fields[ $id ] = $new_field;
		return $new_field;
	}

	/**
	 * Adds a card to the product form.
	 *
	 * @param string $id Card id.
	 * @param string $plugin_id Plugin id.
	 * @param array  $args Array containing the necessary arguments.
	 * @return Card|WP_Error New card or WP_Error.
	 */
	public static function add_card( $id, $plugin_id, $args = array() ) {
		$new_card = self::create_item( 'card', 'Card', $id, $plugin_id, $args );
		if ( is_wp_error( $new_card ) ) {
			return $new_card;
		}
		self::$form_cards[ $id ] = $new_card;
		return $new_card;
	}

	/**
	 * Adds a section to the product form.
	 *
	 * @param string $id Card id.
	 * @param string $plugin_id Plugin id.
	 * @param array  $args Array containing the necessary arguments.
	 * @return Card|WP_Error New section or WP_Error.
	 */
	public static function add_section( $id, $plugin_id, $args ) {
		$new_section = self::create_item( 'section', 'Section', $id, $plugin_id, $args );
		if ( is_wp_error( $new_section ) ) {
			return $new_section;
		}
		self::$form_sections[ $id ] = $new_section;
		return $new_section;
	}

	/**
	 * Returns form config.
	 *
	 * @return array form config.
	 */
	public static function get_form_config() {
		return array(
			'fields'   => self::get_fields(),
			'cards'    => self::get_cards(),
			'sections' => self::get_sections(),
		);
	}

	/**
	 * Returns list of registered fields.
	 *
	 * @param array $sort_by key and order to sort by.
	 * @return array list of registered fields.
	 */
	public static function get_fields( $sort_by = array(
		'key'   => 'order',
		'order' => 'asc',
	) ) {
		return self::get_items( 'field', 'Field', $sort_by );
	}

	/**
	 * Returns list of registered cards.
	 *
	 * @param array $sort_by key and order to sort by.
	 * @return array list of registered cards.
	 */
	public static function get_cards( $sort_by = array(
		'key'   => 'order',
		'order' => 'asc',
	) ) {
		return self::get_items( 'card', 'Card', $sort_by );
	}

	/**
	 * Returns list of registered sections.
	 *
	 * @param array $sort_by key and order to sort by.
	 * @return array list of registered sections.
	 */
	public static function get_sections( $sort_by = array(
		'key'   => 'order',
		'order' => 'asc',
	) ) {
		return self::get_items( 'section', 'Section', $sort_by );
	}

	/**
	 * Returns list of registered items.
	 *
	 * @param string       $type Form component type.
	 * @param class-string $class_name Class of component type.
	 * @param array        $sort_by key and order to sort by.
	 * @return array       list of registered items.
	 */
	private static function get_items( $type, $class_name, $sort_by = array(
		'key'   => 'order',
		'order' => 'asc',
	) ) {
		$item_list = self::${ 'form_' . $type . 's' };
		$class     = 'Automattic\\WooCommerce\\Internal\\Admin\\ProductForm\\' . $class_name;
		$items     = array_values( $item_list );
		if ( method_exists( $class, 'sort' ) ) {
			usort(
				$items,
				function ( $a, $b ) use ( $sort_by, $class ) {
					return $class::sort( $a, $b, $sort_by );
				}
			);
		}
		return $items;
	}

	/**
	 * Creates a new item.
	 *
	 * @param string       $type Form component type.
	 * @param class-string $class_name Class of component type.
	 * @param string       $id Item id.
	 * @param string       $plugin_id Plugin id.
	 * @param array        $args additional arguments for item.
	 * @return Field|Card|Section|WP_Error New product form item or WP_Error.
	 */
	private static function create_item( $type, $class_name, $id, $plugin_id, $args ) {
		$item_list = self::${ 'form_' . $type . 's' };
		$class     = 'Automattic\\WooCommerce\\Internal\\Admin\\ProductForm\\' . $class_name;
		if ( isset( $item_list[ $id ] ) ) {
			return new WP_Error(
				'wc_product_form_' . $type . '_duplicate_field_id',
				sprintf(
				/* translators: 1: Item type 2: Duplicate registered item id. */
					esc_html__( 'You have attempted to register a duplicate form %1$s with WooCommerce Form: %2$s', 'woocommerce' ),
					$type,
					'`' . $id . '`'
				)
			);
		}

		$missing_arguments = method_exists( $class, 'get_missing_arguments' ) ? $class::get_missing_arguments( $args ) : array();
		if ( count( $missing_arguments ) > 0 ) {
			return new WP_Error(
				'wc_product_form_' . $type . '_missing_argument',
				sprintf(
				/* translators: 1: Class name 2: Missing arguments list. */
					esc_html__( 'You are missing required arguments of WooCommerce ProductForm %1$s: %2$s', 'woocommerce' ),
					$class_name,
					join( ', ', $missing_arguments )
				)
			);
		}

		$defaults = array(
			'order' => 20,
		);

		$item_arguments = wp_parse_args( $args, $defaults );

		return new $class( $id, $plugin_id, $item_arguments );
	}
}
