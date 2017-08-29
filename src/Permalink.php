<?php

namespace KMM\Article;

class Permalink {
	//const PERMALINK_STRUCTURE = '/%ressort_hierarchical%/%title%-%pretitle%/%post_id%';
	const PERMALINK_STRUCTURE = '/%post_id%';

	public function __construct() {
		add_filter( 'option_article_structure', array( $this, 'get_permalink_structure' ), 10, 1 );
		add_filter( 'post_type_link', array( $this, 'prepare_post_type_base_url' ), 10, 2 );
		add_filter( 'krn_custom_permalink_tags_ressort_hierarchical', array( $this, 'get_permalink_tag_ressort' ), 10, 5 );
		add_filter( 'krn_custom_permalink_tags_title', array( $this, 'get_permalink_tag_title' ), 10, 5 );
		add_filter( 'krn_custom_permalink_tags_pretitle', array( $this, 'get_permalink_tag_pretitle' ), 10, 5 );
		add_filter( 'post_type_link', array( $this, 'get_final_post_type_permalink' ), 50, 2 );
		add_filter( 'get_sample_permalink_html', array( $this, 'change_permalink_modify_field' ), 10, 5);
	}

	// permalink structure for plugin 'Custom Post Type Permalinks'
	public function get_permalink_structure( $option ) {
		return self::PERMALINK_STRUCTURE;
	}

	public function prepare_post_type_base_url( $url, $post ) {
		if( $post->post_type != Core::$article_slug ) {
			return $url;
		}
		// replace '/articles/' with '/' in url
		$url = str_replace('/'.Core::$article_rewrite.'/', '/', $url);
		return $url;
	}

	// translate '%ressort_hierarchical%' to a valid slug
	public function get_permalink_tag_ressort( $tag_value, $tag_name, $post, $leavename, $sample ) {
		if( $post->post_type != Core::$article_slug ) {
			return $tag_value;
		}
		$ressort_id = get_post_meta($post->ID, 'ressorts_0_ressort_id', true); // fixme => with ACF functions!
		// translate the ressort_id to a ressort and re-apply the filter with the ressort-post
		if( is_numeric($ressort_id) ) {
			$ressort_post = get_post($ressort_id);
			$tag_value = apply_filters( 'krn_custom_permalink_tags_'.$tag_name, $tag_value, $tag, $ressort_post, $leavename, $sample );
		}
		return $tag_value;
	}

	// translate '%title%' to a valid slug
	public function get_permalink_tag_title( $tag_value, $tag_name, $post, $leavename, $sample ) {
		if( $post->post_type != Core::$article_slug ) {
			return $url;
		}
		return sanitize_title($post->post_title);
	}

	// translate '%pretitle%' to a valid slug
	public function get_permalink_tag_pretitle( $tag_value, $tag_name, $post, $leavename, $sample ) {
		if( $post->post_type != Core::$article_slug ) {
			return $url;
		}
		$pretitle = get_post_meta($post->ID, 'pretitle', true); // fixme => with ACF functions!
		return sanitize_title($pretitle);
	}

	public function get_final_post_type_permalink( $url, $post ) {
		if( $post->post_type != Core::$article_slug ) {
			return $url;
		}
		// replace concat-chars for empty tags (e.g. -%pretitle%/)
		$url = str_replace('-/', '/', $url);
		return $url;
	}

	public function change_permalink_modify_field($return, $post_id, $new_title, $new_slug, $post) {
		if( $post->post_type != Core::$article_slug ) {
			return $return;
		}
		$view_link = get_permalink($post);
		$preview_target = ' target="_blank"';
		$return = '<strong>' . __( 'Permalink:' ) . "</strong>\n";
		$display_link = urldecode( $view_link );
		$return .= '<a id="sample-permalink" href="' . esc_url( $view_link ) . '"' . $preview_target . '>' . esc_html( $display_link ) . "</a>\n";
		return $return;
	}
}
