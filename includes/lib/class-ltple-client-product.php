<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Product {
	
	/**
	 * The single instance of LTPLE_Client_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;	

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent = $parent;
		
		add_action( 'init', array( $this, 'init_product' ));
	}
	
	public function init_product(){
		
		if( !is_admin() ){
			
			add_shortcode('ltple-client-product', array( $this , 'get_product_shortcode' ) );
			
			if( !empty($_GET['id']) && is_numeric($_GET['id']) ){
				
				$q = get_post($_GET['id']);
				
				//echo'<pre>';var_dump($q);exit;		
				
				if( !empty($q->post_type) && $q->post_type == 'cb-default-layer' ){
					
					// set product info
					
					foreach( $q as $key => $value){
						
						$this->{$key} = $value;
					}
					
					$this->image = get_the_post_thumbnail_url($q->ID);
					
					$layer_plan = $this->parent->plan->get_layer_plan_info($q->ID);
					
					$this->info 		= $layer_plan['info'];
					$this->taxonomies 	= $layer_plan['taxonomies'];
					
					add_filter('document_title_parts', array($this,'get_title'), 99,1);
				
					add_action('wp_head', array($this, 'get_meta_tags'));
					
					add_filter( 'jetpack_enable_open_graph', '__return_false' );
				}
			}
		}
	}
	
	public function get_title( $title ){
		
		$title['title'] = $this->post_title . ' template';
		//$title['page']; 
		//$title['tagline'];
        //$title['site'];
		
		return $title;
	}
	
	public function get_meta_tags(){
		
		// twitter cards

		echo '<meta name="twitter:card" 		content="summary" />';
		//echo '<meta name="twitter:site" 		content="@" />';
		echo '<meta name="twitter:title" 		content="Awesome ' . $this->post_title . ' template!" />';
		echo '<meta name="twitter:description" 	content="' . $this->post_excerpt . '" />';
		echo '<meta name="twitter:image" 		content="' . $this->image . '" />';
		
		// facebook opengraph
		
		echo '<meta property="og:url"           content="' . $this->parent->urls->product . '?id=' . $this->ID . '" />';
		echo '<meta property="og:type"          content="article" />';
		echo '<meta property="og:title"         content="Awesome ' . $this->post_title . ' template!" />';
		echo '<meta property="og:description"   content="' . $this->post_excerpt . '" />';
		echo '<meta property="og:image"         content="' . $this->image . '" />';
	}
	
	public function get_product_shortcode(){
		
		echo '<div style="min-height:500px;">';
		
			if( !empty($this->ID) ){
				
				include($this->parent->views . $this->parent->_dev .'/product.php');
			}
			else{
				
				include($this->parent->views . $this->parent->_dev .'/products.php');
			}
			
		echo '</div>'; 
	}
	
	/**
	 * Main LTPLE_Client_Product Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Product is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Product instance
	 */
	public static function instance ( $parent ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $parent );
		}
		
		return self::$_instance;
		
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}