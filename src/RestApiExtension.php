<?php

namespace KMM\Article;

class RestApiExtension {

	private $i18n;

	public function __construct( $i18n ) {
		// set text domain
		$this->i18n = $i18n;
		// add filters
		$this->add_filters( );
		// add actions
		$this->add_actions( );
	}

	private function add_filters( )
	{
		add_filter( 'krn_meta_query_article_format', array( $this, 'meta_query_article_format' ), 15, 3 );
	}

	private function add_actions( )
	{
		//add_action( 'krn_rest_acf_update_custom_meta', array( $this, 'acf_update_custom_meta' ), 10, 6 );
		add_action( 'krn_rest_acf_update_custom_meta_featured_images', array( $this, 'acf_update_featured_special_images' ), 10, 6 );
		add_action( 'krn_rest_acf_update_custom_meta_special_images', array( $this, 'acf_update_featured_special_images' ), 10, 6 );
	}

	/*
	public function acf_update_custom_meta ( $value, $field_name, $object, $object_id, $object_type, $request ) {
		print_r($value);
		print_r($field_name);
		print_r($object);
		print_r($object_id);
		print_r($object_type);
		print_r($request);
		exit;
	}
	*/

	// serialize featured_images array of custom_meta
	public function acf_update_featured_special_images ( $data, $key, $object, $object_id, $object_type, $request ) {
		if ( is_array( $data ) ) {
			return serialize( $data );
		}
		return $data;
	}

	public function meta_query_article_format( $args, $query_key, $value ) {
		$args['meta_query'] = $args['meta_query'] ?? [];
		array_push($args['meta_query'], array(
			'key' => 'article_format',
			'compare' => '=',
			'value' => $value
		));
		return $args;

	}
}