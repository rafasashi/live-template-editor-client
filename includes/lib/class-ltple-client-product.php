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
	
	var $slug;
	var $id;
	var $agreements;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent = $parent;
		
		add_filter('ltple_loaded', array( $this, 'init_product' ));
		
		add_filter('query_vars', function( $query_vars ){
			
			if(!in_array('id',$query_vars)){
				
				$query_vars[] = 'id';
			}			
			
			if(!in_array('slug',$query_vars)){
				
				$query_vars[] = 'slug';
			}
			
			return $query_vars;
			
		}, 1);
		
		add_filter('template_redirect', array( $this, 'get_url_parameters' ));
	
		add_action( 'ltple_product_info', array($this,'get_product_info'),10,1 );
		
		add_filter('post_type_link', array( $this, 'get_permalink'),1,2);
	}

	public function get_url_parameters(){

		if( $id = get_query_var('id')){
			
			// redirect old url

			if( $post = get_post($id) ){
				
				$permalink = get_permalink($post);
				
				if( strpos($permalink,$this->parent->urls->current) !== 0 ){
					
					if( !empty($_GET) ){
					
						$permalink = add_query_arg($_GET, $permalink );
					}
					
					wp_redirect($permalink);
					exit;
				}
			}
		}
		
		if( $slug = get_query_var('slug') ){
			
			if( $post = get_page_by_path($slug,OBJECT,'cb-default-layer') ){
				
				// set product info
				
				$this->id = $post->ID;
				
				$GLOBALS['post'] = $post;
				
				foreach( $post as $key => $value){
					
					$this->{$key} = $value;
				}
				
				$image = get_the_post_thumbnail_url($post->ID);
				
				$this->image = !empty($image) ? $image : $this->parent->assets_url . 'images/default_item.png';
				
				$layer_plan = $this->parent->plan->get_layer_options($post->ID);

				$this->taxonomies 	= $layer_plan['taxonomies'];
				
				add_filter('document_title_parts', array($this,'get_title'), 99,1);
			
				add_action('wp_head', array($this, 'get_meta_tags'));
				
				add_filter( 'jetpack_enable_open_graph', '__return_false' );
			
				add_filter('ltple_header_title', array($this,'get_product_title'),10,1);
				
				add_filter('the_seo_framework_title_from_custom_field', array($this,'get_product_title'),10);
				
				add_filter('ltple_header_canonical_url', array($this,'get_product_url'),10);
				
				add_filter('the_seo_framework_rel_canonical_output', '__return_empty_string');
				
				add_filter('get_canonical_url', array($this,'get_product_url'),10);	
			}
		}
	}
	
	public function init_product(){
		
		$this->slug = get_option( $this->parent->_base . 'productSlug' );

		// add rewrite rules

		add_rewrite_rule(
		
			$this->slug . '/([0-9]+)/?$',
			'index.php?pagename=' . $this->slug . '&id=$matches[1]',
			'top'
		);

		add_rewrite_rule(
		
			$this->slug . '/([^/]+)/?$',
			'index.php?pagename=' . $this->slug . '&slug=$matches[1]',
			'top'
		);		
		
		if( !is_admin() ){
			
			add_shortcode('ltple-client-product', array( $this , 'get_product_shortcode' ) );
		}
	}
	
	public function get_permalink( $post_link, $post ){

		if( $post->post_type == 'cb-default-layer' ){
			
			$post_link = $this->parent->urls->home . '/' . $this->slug . '/' . $post->post_name . '/';
		}
					
		return $post_link;
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
		echo '<meta name="twitter:title" 		content="Awesome ' . $this->post_title . '!" />';
		echo '<meta name="twitter:description" 	content="' . $this->post_excerpt . '" />';
		echo '<meta name="twitter:image" 		content="' . $this->image . '" />';
		
		// facebook opengraph
		
		echo '<meta property="og:url"           content="' . get_permalink($this->ID) . '" />';
		echo '<meta property="og:type"          content="article" />';
		echo '<meta property="og:title"         content="Awesome ' . $this->post_title . '!" />';
		echo '<meta property="og:description"   content="' . $this->post_excerpt . '" />';
		echo '<meta property="og:image"         content="' . $this->image . '" />';
	}
	
	public function get_product_title($title){
		
		$title = $this->post_title;
		
		return $title;
	}
	
	public function get_product_url(){
		
		$this->parent->canonical_url = get_permalink($this->id);
	
		return $this->parent->canonical_url;
	}	
	
	public function get_product_shortcode(){
	
		echo '<div style="min-height:500px;">';
		
			if( !empty($this->ID) ){
				
				include($this->parent->views . '/product.php');
			}
			else{
				
				include($this->parent->views . '/products.php');
			}
			
		echo '</div>'; 
	}
	
	public function get_agreement_url( $post, $layer_type, $fee=null, $plan=null ){
		
		if( !isset($this->agreements[$post->ID]) ){
			
			$title = $post->post_title;
			
			if( is_null($fee) ){
			
				$fee = get_post_meta($post->ID,'layerPrice',true);
			}

			$options = array();
			
			if( $addon_range = $this->parent->layer->get_type_addon_range($layer_type) ){
				
				$options[] = $addon_range->slug;
			}

			$plan_data = array(
				
				'name' 		=> $title,
				'options' 	=> $options,
				'price' 	=> 0,
				'fee' 		=> $fee,
				'currency'	=> '$',
				'items'		=> array($post->ID),
			);
			
			if( !empty($plan) ){
				
				dump($plan);
			}

			$this->agreements[$post->ID] = $this->parent->plan->get_agreement_url($plan_data);
		}
		
		return $this->agreements[$post->ID];
	}	
	
	public function get_checkout_url($post){
		
		$checkout_url = $this->parent->urls->checkout;
		
		$checkout_url = add_query_arg(array(
		
			'output' 	=> 'widget',
			'template' 	=> $post->ID,
		
		),$checkout_url);
		
		return $checkout_url;
	}
	
	public function get_checkout_button($post,$layer_type,$price=null){
		
		if( is_null($price) ){
			
			$price = get_post_meta($post->ID,'layerPrice',true);
		}
		
		$button = '';					
	
		if($this->parent->user->loggedin){
			
			if($this->parent->plan->user_has_layer( $post ) === true){
				
				//get editor_url

				$editor_url = $this->parent->urls->edit . '?uri='.$post->ID;
								
				$button.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Start editting this template">Start</a>';
			}
			else{
				
				//get checkout button
				
				$checkout_url = $this->get_checkout_url($post,$layer_type,$price);
				
				$modal_id='modal_'.md5($checkout_url);
				
				$button.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
			
					$button.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL;
		
				$button.='</button>'.PHP_EOL;
				
				$button.='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
					
					$button.='<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
						
						$button.='<div class="modal-content">'.PHP_EOL;
						
							$button.='<div class="modal-header">'.PHP_EOL;
								
								$button.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
								
								$button.= '<h4 class="modal-title" id="myModalLabel">';
								
									$button.= 'Unlock ' . $post->post_title;
								
								$button.= '</h4>'.PHP_EOL;
							
							$button.='</div>'.PHP_EOL;
							
							$button.='<div class="modal-body" style="padding:0px;">'.PHP_EOL;

								$button.= '<div class="loadingIframe" style="position:absolute;height: 50px;width: 100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $this->parent->assets_url . '/loader.gif\');"></div>';

								$button.= '<iframe data-src="'.$checkout_url.'" style="width: 100%;position:relative;bottom: 0;border:0;height:calc( 100vh - 60px);overflow: hidden;"></iframe>';
							
							$button.='</div>'.PHP_EOL;
							
						$button.='</div>'.PHP_EOL;
						
					$button.='</div>'.PHP_EOL;
					
				$button.='</div>'.PHP_EOL;									
			}
		}
		else{
			
			$button.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
			
				$button.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL;
		
			$button.='</button>'.PHP_EOL;								
		}

		return $button;
	}
	
	public function get_product_tabs($product){
		
		$tabs = array();
		
		if( !empty($product->post_content) ){
			
			$tabs[] = array(
				
				'slug'		=> 'description',
				'name'		=> 'Description',
				'content'	=> $this->get_product_description($product),
			);
		}
		
		if( $installation = $this->parent->layer->get_installation_info($product)){
			
			$tabs[] = array(
				
				'slug'		=> 'installation',
				'name'		=> 'Installation',
				'content'	=> $installation,
			);				
		}

		return apply_filters('ltple_product_tabs',$tabs,$product);
	}
	
	public function get_product_description($product){
		
		$description = '';
		
		if( !empty($product->post_content) ){
			
			$description .= '<div class="col-xs-12 col-sm-8 col-lg-9">';
			
				$description .= apply_filters('the_content',$product->post_content);
			
			$description .= '</div>';
		}

		return $description;
	}
	
	public function get_product_info($product){
		
		echo'<div class="col-xs-12">';
			
			if( $tabs = $this->get_product_tabs($product) ){
				
				echo'<ul class="nav nav-tabs" role="tablist" style="background:transparent;margin:-1px;padding:0px !important;overflow:visible !important;height:50px;font-size:15px;font-weight:bold;">';
					
					$class=' class="active"';
					
					foreach( $tabs as $tab ){
						
						echo'<li role="presentation"'.$class.'><a href="#'.$tab['slug'].'" aria-controls="'.$tab['slug'].'" role="tab" data-toggle="tab" aria-expanded="true">'.$tab['name'].'</a></li>';
					
						$class = '';
					}
					
				echo'</ul>';

				echo'<div class="panel panel-default">';								
				
					echo'<div class="panel-body tab-content" style="min-height:380px;">';
						
						$class = ' class="tab-pane active"';
						
						foreach( $tabs as $tab ){
							
							echo '<div role="tabpanel"'.$class.' id="'.$tab['slug'].'">';
							
								echo $tab['content'];
								
							echo '</div>';
							
							$class = ' class="tab-pane"';
						}
						
					echo'</div>';
					
				echo'</div>';
			}
			
		echo'</div>';		
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
