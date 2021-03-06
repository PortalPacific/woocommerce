<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy Abstract Product
 *
 * Legacy and deprecated functions are here to keep the WC_Abstract_Product
 * clean.
 * This class will be removed in future versions.
 *
 * @version  2.7.0
 * @package  WooCommerce/Abstracts
 * @category Abstract Class
 * @author   WooThemes
 */
abstract class WC_Abstract_Legacy_Product extends WC_Data {

	/**
	 * The product's type (simple, variable etc).
	 * @deprecated 2.7.0 get_type() method should return string instead since this prop should not be changed or be public.
	 * @var string
	 */
	public $product_type = 'simple';

	/**
	 * Magic __isset method for backwards compatibility. Legacy properties which could be accessed directly in the past.
	 *
	 * @param  string $key Key name.
	 * @return bool
	 */
	public function __isset( $key ) {
		$valid = array(
			'id',
			'product_attributes',
			'visibility',
			'sale_price_dates_from',
			'sale_price_dates_to',
			'post',
			'download_type',
			'product_image_gallery',
			'variation_shipping_class',
			'shipping_class',
			'total_stock',
			'crosssell_ids',
			'parent',
		);
		if ( $this->is_type( 'variation' ) ) {
			$valid = array_merge( $valid, array(
				'variation_id',
				'variation_data',
				'variation_has_stock',
				'variation_shipping_class_id',
			) );
		}
		return in_array( $key, array_merge( $valid, array_keys( $this->data ) ) ) || metadata_exists( 'post', $this->get_id(), '_' . $key ) || metadata_exists( 'post', $this->get_parent_id(), '_' . $key );
	}

	/**
	 * Magic __get method for backwards compatibility. Maps legacy vars to new getters.
	 *
	 * @param  string $key Key name.
	 * @return mixed
	 */
	public function __get( $key ) {

		if ( 'post_type' === $key ) {
			return $this->post_type;
		}

		wc_doing_it_wrong( $key, __( 'Product properties should not be accessed directly.', 'woocommerce' ), '2.7' );

		switch ( $key ) {
			case 'id' :
				$value = $this->is_type( 'variation' ) ? $this->get_parent_id() : $this->get_id();
				break;
			case 'product_attributes' :
				$value = isset( $this->data['attributes'] ) ? $this->data['attributes'] : '';
				break;
			case 'visibility' :
				$value = $this->get_catalog_visibility();
				break;
			case 'sale_price_dates_from' :
				$value = $this->get_date_on_sale_from();
				break;
			case 'sale_price_dates_to' :
				$value = $this->get_date_on_sale_to();
				break;
			case 'post' :
				$value = get_post( $this->get_id() );
				break;
			case 'download_type' :
				return 'standard';
				break;
			case 'product_image_gallery' :
				$value = $this->get_gallery_image_ids();
				break;
			case 'variation_shipping_class' :
			case 'shipping_class' :
				$value = $this->get_shipping_class();
				break;
			case 'total_stock' :
				$value = $this->get_total_stock();
				break;
			case 'downloadable' :
			case 'virtual' :
			case 'manage_stock' :
			case 'featured' :
			case 'sold_individually' :
				$value = $this->{"get_$key"}() ? 'yes' : 'no';
				break;
			case 'crosssell_ids' :
				$value = $this->get_cross_sell_ids();
				break;
			case 'upsell_ids' :
				$value = $this->get_upsell_ids();
				break;
			case 'parent' :
				$value = wc_get_product( $this->get_parent_id() );
				break;
			case 'variation_id' :
				$value = $this->is_type( 'variation' ) ? $this->get_id() : '';
				break;
			case 'variation_data' :
				$value = $this->is_type( 'variation' ) ? wc_get_product_variation_attributes( $this->get_id() ) : '';
				break;
			case 'variation_has_stock' :
				$value = $this->is_type( 'variation' ) ? $this->managing_stock() : '';
				break;
			case 'variation_shipping_class_id' :
				$value = $this->is_type( 'variation' ) ? $this->get_shipping_class_id() : '';
				break;
			default :
				if ( in_array( $key, array_keys( $this->data ) ) ) {
					$value = $this->{"get_$key"}();
				} else {
					$value = get_post_meta( $this->id, '_' . $key, true );
				}
				break;
		}
		return $value;
	}

	/**
	 * If set, get the default attributes for a variable product.
	 *
	 * @deprecated 2.7.0
	 * @return array
	 */
	public function get_variation_default_attributes() {
		wc_deprecated_function( 'WC_Product_Variable::get_variation_default_attributes', '2.7', 'WC_Product::get_default_attributes' );
		return apply_filters( 'woocommerce_product_default_attributes', array_filter( (array) maybe_unserialize( $this->get_default_attributes() ) ), $this );
	}

	/**
	 * Returns the gallery attachment ids.
	 *
	 * @deprecated 2.7.0
	 * @return array
	 */
	public function get_gallery_attachment_ids() {
		wc_deprecated_function( 'WC_Product::get_gallery_attachment_ids', '2.7', 'WC_Product::get_gallery_image_ids' );
		return $this->get_gallery_image_ids();
	}

	/**
	 * Set stock level of the product.
	 *
	 * @deprecated 2.7.0
	 */
	public function set_stock( $amount = null, $mode = 'set' ) {
		wc_deprecated_function( 'WC_Product::set_stock', '2.7', 'wc_update_product_stock' );
		return wc_update_product_stock( $this, $amount, $mode );
	}

	/**
	 * Reduce stock level of the product.
	 *
	 * @deprecated 2.7.0
	 * @param int $amount Amount to reduce by. Default: 1
	 * @return int new stock level
	 */
	public function reduce_stock( $amount = 1 ) {
		wc_deprecated_function( 'WC_Product::reduce_stock', '2.7', 'wc_update_product_stock' );
		wc_update_product_stock( $this, $amount, 'decrease' );
	}

	/**
	 * Increase stock level of the product.
	 *
	 * @deprecated 2.7.0
	 * @param int $amount Amount to increase by. Default 1.
	 * @return int new stock level
	 */
	public function increase_stock( $amount = 1 ) {
		wc_deprecated_function( 'WC_Product::increase_stock', '2.7', 'wc_update_product_stock' );
		wc_update_product_stock( $this, $amount, 'increase' );
	}

	/**
	 * Check if the stock status needs changing.
	 *
	 * @deprecated 2.7.0 Sync is done automatically on read/save, so calling this should not be needed any more.
	 */
	public function check_stock_status() {
		wc_deprecated_function( 'WC_Product::check_stock_status', '2.7' );
	}

	/**
	 * Get and return related products.
	 * @deprecated 2.7.0 Use wc_get_related_products instead.
	 */
	public function get_related( $limit = 5 ) {
		wc_deprecated_function( 'WC_Product::get_related', '2.7', 'wc_get_related_products' );
		return wc_get_related_products( $this->get_id(), $limit );
	}

	/**
	 * Retrieves related product terms.
	 * @deprecated 2.7.0 Use wc_get_product_term_ids instead.
	 */
	protected function get_related_terms( $term ) {
		wc_deprecated_function( 'WC_Product::get_related_terms', '2.7', 'wc_get_product_term_ids' );
		return array_merge( array( 0 ), wc_get_product_term_ids( $this->get_id(), $term ) );
	}

	/**
	 * Builds the related posts query.
	 * @deprecated 2.7.0 Use Product Data Store get_related_products_query instead.
	 */
	protected function build_related_query( $cats_array, $tags_array, $exclude_ids, $limit ) {
		wc_deprecated_function( 'WC_Product::build_related_query', '2.7', 'Product Data Store get_related_products_query' );
		$data_store = WC_Data_Store::load( 'product' );
		return $data_store->get_related_products_query( $cats_array, $tags_array, $exclude_ids, $limit );
	}

	/**
	 * Returns the child product.
	 * @deprecated 2.7.0 Use wc_get_product instead.
	 * @param mixed $child_id
	 * @return WC_Product|WC_Product|WC_Product_variation
	 */
	public function get_child( $child_id ) {
		wc_deprecated_function( 'WC_Product::get_child', '2.7', 'wc_get_product' );
		return wc_get_product( $child_id );
	}

	/**
	 * Functions for getting parts of a price, in html, used by get_price_html.
	 *
	 * @deprecated 2.7.0
	 * @return string
	 */
	public function get_price_html_from_text() {
		wc_deprecated_function( 'WC_Product::get_price_html_from_text', '2.7', 'wc_get_price_html_from_text' );
		return wc_get_price_html_from_text();
	}

	/**
	 * Functions for getting parts of a price, in html, used by get_price_html.
	 *
	 * @deprecated 2.7.0 Use wc_format_sale_price instead.
	 * @param  string $from String or float to wrap with 'from' text
	 * @param  mixed $to String or float to wrap with 'to' text
	 * @return string
	 */
	public function get_price_html_from_to( $from, $to ) {
		wc_deprecated_function( 'WC_Product::get_price_html_from_to', '2.7', 'wc_format_price_range' );
		return apply_filters( 'woocommerce_get_price_html_from_to', wc_format_price_range( $from, $to ), $from, $to, $this );
	}

	/**
	 * Lists a table of attributes for the product page.
	 * @deprecated 2.7.0 Use wc_display_product_attributes instead.
	 */
	public function list_attributes() {
		wc_deprecated_function( 'WC_Product::list_attributes', '2.7', 'wc_display_product_attributes' );
		wc_display_product_attributes( $this );
	}

	/**
	 * Returns the price (including tax). Uses customer tax rates. Can work for a specific $qty for more accurate taxes.
	 *
	 * @deprecated 2.7.0 Use wc_get_price_including_tax instead.
	 * @param  int $qty
	 * @param  string $price to calculate, left blank to just use get_price()
	 * @return string
	 */
	public function get_price_including_tax( $qty = 1, $price = '' ) {
		wc_deprecated_function( 'WC_Product::get_price_including_tax', '2.7', 'wc_get_price_including_tax' );
		return wc_get_price_including_tax( $this, array( 'qty' => $qty, 'price' => $price ) );
	}

	/**
	 * Returns the price including or excluding tax, based on the 'woocommerce_tax_display_shop' setting.
	 *
	 * @deprecated 2.7.0 Use wc_get_price_to_display instead.
	 * @param  string  $price to calculate, left blank to just use get_price()
	 * @param  integer $qty   passed on to get_price_including_tax() or get_price_excluding_tax()
	 * @return string
	 */
	public function get_display_price( $price = '', $qty = 1 ) {
		wc_deprecated_function( 'WC_Product::get_display_price', '2.7', 'wc_get_price_to_display' );
		return wc_get_price_to_display( $this, array( 'qty' => $qty, 'price' => $price ) );
	}

	/**
	 * Returns the price (excluding tax) - ignores tax_class filters since the price may *include* tax and thus needs subtracting.
	 * Uses store base tax rates. Can work for a specific $qty for more accurate taxes.
	 *
	 * @deprecated 2.7.0 Use wc_get_price_excluding_tax instead.
	 * @param  int $qty
	 * @param  string $price to calculate, left blank to just use get_price()
	 * @return string
	 */
	public function get_price_excluding_tax( $qty = 1, $price = '' ) {
		wc_deprecated_function( 'WC_Product::get_price_excluding_tax', '2.7', 'wc_get_price_excluding_tax' );
		return wc_get_price_excluding_tax( $this, array( 'qty' => $qty, 'price' => $price ) );
	}

	/**
	 * Adjust a products price dynamically.
	 *
	 * @deprecated 2.7.0
	 * @param mixed $price
	 */
	public function adjust_price( $price ) {
		wc_deprecated_function( 'WC_Product::adjust_price', '2.7', 'WC_Product::set_price / WC_Product::get_price' );
		$this->data['price'] = $this->data['price'] + $price;
	}

	/**
	 * Returns the product categories.
	 *
	 * @deprecated 2.7.0
	 * @param string $sep (default: ', ').
	 * @param string $before (default: '').
	 * @param string $after (default: '').
	 * @return string
	 */
	public function get_categories( $sep = ', ', $before = '', $after = '' ) {
		wc_deprecated_function( 'WC_Product::get_categories', '2.7', 'wc_get_product_category_list' );
		return wc_get_product_category_list( $this->get_id(), $sep, $before, $after );
	}

	/**
	 * Returns the product tags.
	 *
	 * @deprecated 2.7.0
	 * @param string $sep (default: ', ').
	 * @param string $before (default: '').
	 * @param string $after (default: '').
	 * @return array
	 */
	public function get_tags( $sep = ', ', $before = '', $after = '' ) {
		wc_deprecated_function( 'WC_Product::get_tags', '2.7', 'wc_get_product_tag_list' );
		return wc_get_product_tag_list( $this->get_id(), $sep, $before, $after );
	}

	/**
	 * Get the product's post data.
	 *
	 * @deprecated 2.7.0
	 * @return WP_Post
	 */
	public function get_post_data() {
		wc_deprecated_function( 'WC_Product::get_post_data', '2.7', 'get_post' );

		// In order to keep backwards compatibility it's required to use the parent data for variations.
		if ( $this->is_type( 'variation' ) ) {
			$post_data = get_post( $this->get_parent_id() );
		} else {
			$post_data = get_post( $this->get_id() );
		}

		return $post_data;
	}

	/**
	 * Get the parent of the post.
	 *
	 * @deprecated 2.7.0
	 * @return int
	 */
	public function get_parent() {
		wc_deprecated_function( 'WC_Product::get_parent', '2.7', 'WC_Product::get_parent_id' );
		return apply_filters( 'woocommerce_product_parent', absint( $this->get_post_data()->post_parent ), $this );
	}

	/**
	 * Returns the upsell product ids.
	 *
	 * @deprecated 2.7.0
	 * @return array
	 */
	public function get_upsells() {
		wc_deprecated_function( 'WC_Product::get_upsells', '2.7', 'WC_Product::get_upsell_ids' );
		return apply_filters( 'woocommerce_product_upsell_ids', $this->get_upsell_ids(), $this );
	}

	/**
	 * Returns the cross sell product ids.
	 *
	 * @deprecated 2.7.0
	 * @return array
	 */
	public function get_cross_sells() {
		wc_deprecated_function( 'WC_Product::get_cross_sells', '2.7', 'WC_Product::get_cross_sell_ids' );
		return apply_filters( 'woocommerce_product_crosssell_ids', $this->get_cross_sell_ids(), $this );
	}

	/**
	 * Check if variable product has default attributes set.
	 *
	 * @deprecated 2.7.0
	 * @return bool
	 */
	public function has_default_attributes() {
		wc_deprecated_function( 'WC_Product_Variable::has_default_attributes', '2.7', 'Check WC_Product::get_default_attributes directly' );
		if ( ! $this->get_default_attributes() ) {
			return true;
		}
		return false;
	}

	/**
	 * Get variation ID.
	 *
	 * @deprecated 2.7.0
	 * @return int
	 */
	public function get_variation_id() {
		wc_deprecated_function( 'WC_Product::get_variation_id', '2.7', 'WC_Product::get_id() will always be the variation ID if this is a variation.' );
		return $this->get_id();
	}

	/**
	 * Get product variation description.
	 *
	 * @deprecated 2.7.0
	 * @return string
	 */
	public function get_variation_description() {
		wc_deprecated_function( 'WC_Product::get_variation_description', '2.7', 'WC_Product::get_description()' );
		return $this->get_description();
	}

	/**
	 * Check if all variation's attributes are set.
	 *
	 * @deprecated 2.7.0
	 * @return boolean
	 */
	public function has_all_attributes_set() {
		wc_deprecated_function( 'WC_Product::has_all_attributes_set', '2.7', 'Use array filter on get_variation_attributes for a quick solution.' );
		$set = true;

		// undefined attributes have null strings as array values
		foreach ( $this->get_variation_attributes() as $att ) {
			if ( ! $att ) {
				$set = false;
				break;
			}
		}
		return $set;
	}

	/**
	 * Returns whether or not the variations parent is visible.
	 *
	 * @deprecated 2.7.0
	 * @return bool
	 */
	public function parent_is_visible() {
		wc_deprecated_function( 'WC_Product::parent_is_visible', '2.7' );
		return $this->is_visible();
	}

	/**
	 * Get total stock - This is the stock of parent and children combined.
	 *
	 * @deprecated 2.7.0
	 * @return int
	 */
	public function get_total_stock() {
		wc_deprecated_function( 'WC_Product::get_total_stock', '2.7', 'Use get_stock_quantity on each child. Beware of performance issues in doing so.' );
		if ( sizeof( $this->get_children() ) > 0 ) {
			$total_stock = max( 0, $this->get_stock_quantity() );

			foreach ( $this->get_children() as $child_id ) {
				if ( 'yes' === get_post_meta( $child_id, '_manage_stock', true ) ) {
					$stock = get_post_meta( $child_id, '_stock', true );
					$total_stock += max( 0, wc_stock_amount( $stock ) );
				}
			}
		} else {
			$total_stock = $this->get_stock_quantity();
		}
		return wc_stock_amount( $total_stock );
	}

	/**
	 * Get formatted variation data with WC < 2.4 back compat and proper formatting of text-based attribute names.
	 *
	 * @deprecated 2.7.0
	 * @return string
	 */
	public function get_formatted_variation_attributes( $flat = false ) {
		wc_deprecated_function( 'WC_Product::get_formatted_variation_attributes', '2.7', 'wc_get_formatted_variation' );
		return wc_get_formatted_variation( $this, $flat );
	}

	/**
	 * Sync variable product prices with the children lowest/highest prices.
	 *
	 * @deprecated 2.7.0 not used in core.
	 */
	public function variable_product_sync( $product_id = '' ) {
		wc_deprecated_function( 'WC_Product::variable_product_sync', '2.7' );
		if ( empty( $product_id ) ) {
			$product_id = $this->get_id();
		}

		// Sync prices with children
		self::sync( $product_id );

		// Re-load prices
		$this->read_product_data();
	}

	/**
	 * Sync the variable product's attributes with the variations.
	 */
	public static function sync_attributes( $product, $children = false ) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		/**
		 * Pre 2.4 handling where 'slugs' were saved instead of the full text attribute.
		 * Attempt to get full version of the text attribute from the parent and UPDATE meta.
		 */
		if ( version_compare( get_post_meta( $product->get_id(), '_product_version', true ), '2.4.0', '<' ) ) {
			$parent_attributes = array_filter( (array) get_post_meta( $product->get_id(), '_product_attributes', true ) );

			if ( ! $children ) {
				$children = $product->get_children( 'edit' );
			}

			foreach ( $children as $child_id ) {
				$all_meta = get_post_meta( $child_id );

				foreach ( $all_meta as $name => $value ) {
					if ( 0 !== strpos( $name, 'attribute_' ) ) {
						continue;
					}
					if ( sanitize_title( $value[0] ) === $value[0] ) {
						foreach ( $parent_attributes as $attribute ) {
							if ( 'attribute_' . sanitize_title( $attribute['name'] ) !== $name ) {
								continue;
							}
							$text_attributes = wc_get_text_attributes( $attribute['value'] );
							foreach ( $text_attributes as $text_attribute ) {
								if ( sanitize_title( $text_attribute ) === $value[0] ) {
									update_post_meta( $child_id, $name, $text_attribute );
									break;
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Match a variation to a given set of attributes using a WP_Query.
	 * @deprecated 2.7.0 in favour of Product data store's find_matching_product_variation.
	 */
	public function get_matching_variation( $match_attributes = array() ) {
		wc_deprecated_function( 'WC_Product::get_matching_variation', '2.7', 'Product data store find_matching_product_variation' );
		$data_store = WC_Data_Store::load( 'product' );
		return $data_store->find_matching_product_variation( $this, $match_attributes );
	}

	/**
	 * Returns whether or not we are showing dimensions on the product page.
	 * @deprecated 2.7.0 Unused.
	 * @return bool
	 */
	public function enable_dimensions_display() {
		wc_deprecated_function( 'WC_Product::enable_dimensions_display', '2.7' );
		return apply_filters( 'wc_product_enable_dimensions_display', true ) && ( $this->has_dimensions() || $this->has_weight() || $this->child_has_weight() || $this->child_has_dimensions() );
	}

	/**
	 * Returns the product rating in html format.
	 *
	 * @deprecated 2.7.0
	 * @param string $rating (default: '')
	 * @return string
	 */
	public function get_rating_html( $rating = null ) {
		wc_deprecated_function( 'WC_Product::get_rating_html', '2.7', 'wc_get_rating_html' );
		return wc_get_rating_html( $rating );
	}

	/**
	 * Sync product rating. Can be called statically.
	 *
	 * @deprecated 2.7.0
	 * @param  int $post_id
	 */
	public static function sync_average_rating( $post_id ) {
		wc_deprecated_function( 'WC_Product::sync_average_rating', '2.7', 'WC_Comments::get_average_rating_for_product or leave to CRUD.' );
		$average = WC_Comments::get_average_rating_for_product( wc_get_product( $post_id ) );
		update_post_meta( $post_id, '_wc_average_rating', $average );
	}

	/**
	 * Sync product rating count. Can be called statically.
	 *
	 * @deprecated 2.7.0
	 * @param  int $post_id
	 */
	public static function sync_rating_count( $post_id ) {
		wc_deprecated_function( 'WC_Product::sync_rating_count', '2.7', 'WC_Comments::get_rating_counts_for_product or leave to CRUD.' );
		$counts     = WC_Comments::get_rating_counts_for_product( wc_get_product( $post_id ) );
		update_post_meta( $post_id, '_wc_rating_count', $counts );
	}

	/**
	 * Same as get_downloads in CRUD.
	 *
	 * @deprecated 2.7.0
	 * @return array
	 */
	public function get_files() {
		wc_deprecated_function( 'WC_Product::get_files', '2.7', 'WC_Product::get_downloads' );
		return $this->get_downloads();
	}

	/**
	 * @deprected 2.7.0 Sync is taken care of during save - no need to call this directly.
	 */
	public function grouped_product_sync() {
		wc_deprecated_function( 'WC_Product::grouped_product_sync', '2.7' );
	}
}
