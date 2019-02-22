<?php
/*
Plugin Name: Full Size Image Links
Plugin URI: https://meowapps.com
Description: Link to full-size media files from galleries. Blatently ripped from Gallery Custom Links by Jordy Meow
Version: 1.0.0
Author: Zach Isbach, Jordy Meow
Author URI: https://meowapps.com
Text Domain: full-size-image-links
Domain Path: /languages

Licensed under the MIT license:
http://www.opensource.org/licenses/mit-license.php
*/

require_once 'vendor/autoload.php';

use DiDom\Document;
use DiDom\Element;

new Full_Size_Image_Links();

class Full_Size_Image_Links {	
	public function __construct() {
		add_filter( 'the_content', array( $this, 'linkify' ), 20 );
	}
	
	function get_pathinfo_from_image_src( $image_src ) {
		$uploads = wp_upload_dir();
		$uploads_url = trailingslashit( $uploads['baseurl'] );
		if ( strpos( $image_src, $uploads_url ) === 0 )
			return ltrim( substr( $image_src, strlen( $uploads_url ) ), '/');
		else if ( strpos( $image_src, wp_make_link_relative( $uploads_url ) ) === 0 )
			return ltrim( substr( $image_src, strlen( wp_make_link_relative( $uploads_url ) ) ), '/');
		$img_info = parse_url( $image_src );
		return ltrim( $img_info['path'], '/' );
	}
		
	function resolve_image_id( $url ) {
		global $wpdb;
		$pattern = '/[_-]\d+x\d+(?=\.[a-z]{3,4}$)/';
		$url = preg_replace( $pattern, '', $url );
		$url = $this->get_pathinfo_from_image_src( $url );
		$query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid LIKE '%s'", '%' . $url . '%' );
		$attachment = $wpdb->get_col( $query );
		return empty( $attachment ) ? null : $attachment[0];
	}
		
	function linkify( $buffer ) {
		if (!isset( $buffer ) || trim( $buffer ) === '' )
			return $buffer;
		$html = new Document();
		$html->preserveWhiteSpace();
		$html->loadHtml( $buffer, 0 );
		$hasChanges = false;
		$classes = apply_filters( 'gallery_custom_links_classes', array( '.gallery', '.wp-block-gallery' ) );
		foreach ( $classes as $class ) {
			foreach ( $html->find( $class . ' img' ) as $element )
				$hasChanges = $this->linkify_element( $element ) || $hasChanges;
		}
		return $hasChanges ? $html->html() : $buffer;
	}
		
	function linkify_element( $element ) {
		$classes = $element->attr('class');
		$mediaId = null;

		// Check if the wp-image-xxx class exists
		if ( preg_match( '/wp-image-([0-9]{1,10})/i', $classes, $matches ) )
			$mediaId = $matches[1];
		// Otherwise, resolve the ID from the URL
		else {
			$url = $element->attr('src');
			$mediaId = $this->resolve_image_id( $url );
		}

		if ( $mediaId ) {
			$url = wp_get_attachment_url($mediaId);
			$parent = $element->parent();

			// Modify an existing link tag
			$potentialLinkNode = $parent;
			$maxDepth = 10;
			do {
				if ( $potentialLinkNode->tag === 'a' ) {
					$potentialLinkNode->attr( 'href', $url );
					$class = $potentialLinkNode->attr( 'class' );
					$potentialLinkNode->attr( 'class', $class );
					return true;
				}
				if ( method_exists( $potentialLinkNode, 'parent' ) )
					$potentialLinkNode = $potentialLinkNode->parent();
				else
					break;
			}
			while ( $potentialLinkNode && $maxDepth-- >= 0 );

			// Insert a new link tag
			if ( $parent->tag === 'figure' )
				$parent = $parent->parent();
			$a = new Element('a');
			$a->attr( 'href', $url );
			$a->appendChild( $parent->children() );
			foreach( $parent->children() as $img ) {
				$img->remove();
			}
			$parent->appendChild( $a );
			return true;
		}
		return false;
	}
}

?>
