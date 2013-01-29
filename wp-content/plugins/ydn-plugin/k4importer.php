<?php
    /*
    Plugin Name: K4Importer
    Description: Importer for K4.
    Version: 1.0
    Author: Akshay Nathan
    Author URI: http://URI_Of_The_Plugin_Author
    License: GPL2
    */

	function add_importer_endpoint() {
		add_rewrite_endpoint( 'importer', EP_ROOT );
	}
	add_action( 'init', 'add_importer_endpoint' );

	function importer_template_redirect() {
        global $wp_query;
        global $wpdb;
		if ( ! isset( $wp_query->query_vars['importer'] ) )
                	return;
		echo("Importer! An endpoint to import k4 into the wordpress dbs.\n");
		if( isset($_GET['NITFurl']) ) {
            $url = urlencode($_GET['NITFurl']);
echo($url);
            $xml = file_get_contents($url);
echo($xml);           
 $xml_obj = new SimpleXMLElement($xml);
            
            $author = $xml_object->body->{'body.head'}->byline->person;
            $title = $xml_object->body->{'body.head'}->byline->byttl;
            $excerpt = $xml_object->body->{'body.head'}->abstract;
            $story =  $xml_object->body->{'body.content'};
            
            $first_name = explode(" ", $author);
            $last_name = $first_name[1];
            $first_name = $first_name[0];
            $query = $wpdb->prepare("SELECT user_id  FROM $wpdb->usermeta WHERE ( meta_key='first_name' AND meta_value='%s' ) and ( meta_key='last_name' AND meta_value='%s' )", $first_name ,$last_name);
            $authorID= $wpdb->get_var( $query );
		
	echo("$first_name $last_name $authorID $title $excerpt $story");

            $post = array(
              'post_author'    => $authorID,
              'post_content'   => $story,
              'post_excerpt'   => $excerpt,
              'post_name'      => $title,
              'post_status'    => 'draft',
            );  
            wp_insert_post( $post );

        }
		exit;
	}
	add_action( 'template_redirect', 'importer_template_redirect' );

	function makeplugins_endpoints_activate() {
        	add_importer_endpoint();
        	flush_rewrite_rules();
	}
	register_activation_hook( __FILE__, 'makeplugins_endpoints_activate' );
 
	function makeplugins_endpoints_deactivate() {
		flush_rewrite_rules();
	}
	register_deactivation_hook( __FILE__, 'makeplugins_endpoints_deactivate' );
?>
