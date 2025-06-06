<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Product extends LTPLE_Client_Object {
	
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
	
		add_action('ltple_product_info', array($this,'get_product_info'),0,1 );
		
		add_filter('post_type_link', array( $this, 'get_permalink'),1,2);
	}

	public function get_url_parameters(){

		if( $id = get_query_var('id')){
			
			// redirect old url

			if( $post = get_post($id) ){
				
				$permalink = get_permalink($post);
				
				if( strpos($permalink,$this->parent->urls->current) !== 0 ){
					
					if( !empty($_GET) ){
						
						if( !empty($_GET['ref']) ){
							
							// prevent infinite loop
							
							$permalink = sanitize_url(urldecode($_GET['ref']));
						}
						else{
						
							$permalink = add_query_arg($_GET, $permalink );
						}
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
				
				$alt_url = $this->parent->layer->get_preview_image_url($post->ID,'post-thumbnail',$this->parent->assets_url . 'images/default_item.png');
				
				$this->image = $this->parent->layer->get_thumbnail_url($post->ID,'post-thumbnail',$alt_url);

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
	
	public function get_product_ranges($product){
		
		$ranges = array();
		
		if( $layer_plan = $this->parent->plan->get_layer_options($product->ID) ){

			foreach( $layer_plan['taxonomies']['layer-range']['terms'] as $term ){
				
				if($term['has_term']){
					
					$ranges[] = $term['slug'];
				}
			}
		}
		
		return $ranges;
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
	
	public function get_agreement_url( $post, $layer_type, $fee=null, $plan=null ){
		
		if( !isset($this->agreements[$post->ID]) ){
			
			$title = $post->post_title;
			
			if( is_null($fee) ){
			
				$fee = get_post_meta($post->ID,'layerPrice',true);
			}

			$options = array();
			
			if( $addon = $this->parent->layer->get_type_addon_range($layer_type) ){
				
				$options[] = $addon->slug;
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
	
	public function get_checkout_button($post){
		
		$price = get_post_meta($post->ID,'layerPrice',true);

		$button = '';					

		if($this->parent->plan->user_has_layer( $post ) === true){
			
			//get editor_url

			$quick_start_url = apply_filters('ltple_quick_start_url',$this->parent->urls->edit . '?uri='.$post->ID,$post);
							
			$button.='<a class="btn btn-sm btn-success" href="'. $quick_start_url .'" target="_parent" title="Start editing this template">Start</a>';
		}
		else{
			
			//get checkout button
			
			$checkout_url = $this->get_checkout_url($post);
            
            if( $this->parent->inWidget === true ){
                
                $button.='<a type="button" class="btn btn-sm btn-success" href="'.$checkout_url.'" targer="_self">'.PHP_EOL;
            
                    $button.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL;
        
                $button.='</a>'.PHP_EOL;
            }
            else{
                
                $checkout_modal = $this->get_modal($checkout_url);
                
                $button.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#'.$checkout_modal['id'].'">'.PHP_EOL;
            
                    $button.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL;
        
                $button.='</button>'.PHP_EOL;
                
                $button.=$checkout_modal['content'].PHP_EOL;
            }
		}

		return $button;
	}
	
	public function get_product_tabs($product){
		
		$tabs = array();
		
		if( !empty($product->post_content) ){
			
			$tabs[] = array(
				
				'slug'		=> 'description',
				'name'		=> '<i class="far fa-window-maximize"></i> Description',
				'content'	=> $this->get_product_description($product),
			);
		}
		
		if( $installation = $this->parent->layer->get_installation_info($product)){
			
			$tabs[] = array(
				
				'slug'		=> 'installation',
				'name'		=> '<i class="fa fa-cloud-download-alt" aria-hidden="true"></i> Installation',
				'content'	=> $installation,
			);				
		}
		
		if( $blocks = $this->parent->layer->get_blocks_info($product)){
			
			$tabs[] = array(
				
				'slug'		=> 'blocks',
				'name'		=> '<i class="fa fa-cubes" aria-hidden="true"></i> Blocks',
				'content'	=> $blocks,
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
	
	public function get_product_gallery_ids($product){
		
		$ids = array();
		
		if( $id = get_post_thumbnail_id($product) ){
			
			$ids[] = $id;
		}
		
		if( $gallery = get_post_meta($product->ID,'layer-gallery',true) ){
			
			foreach( $gallery as $id ){
				
				$id = intval($id);
				
				if( !in_array($id,$ids) ){
					
					$ids[] = $id;
				}
			}
		}
		
		if( empty($ids) ){
			
			// set alt image
			
			return array(0); 
		}
		
		return $ids;
	}

	public function get_product_image_url($image_id,$size='medium_large'){
		
		$url = $this->parent->assets_url . 'images/default_item.png';
		
		if ($src = wp_get_attachment_image_src( $image_id, $size )){
		
			$url = $src[0];
		}
		
		return $url;
	}
	
	public function get_product_info($product){
		
		echo'<div class="col-xs-12">';
			
			if( $tabs = $this->get_product_tabs($product) ){
				
				echo'<ul class="nav nav-tabs nav-resizable my-3" role="tablist">';
					
					$active=' active';
					
					foreach( $tabs as $tab ){
						
						echo'<li role="presentation" class="nav-item"><a class="nav-link'.$active.'" href="#'.$tab['slug'].'" aria-controls="'.$tab['slug'].'" role="tab" data-toggle="tab" aria-expanded="true">'.$tab['name'].'</a></li>';
					
						$active = '';
					}
					
				echo'</ul>';

				echo'<div class="panel panel-default">';								
				
					echo'<div class="panel-body tab-content" style="font-size:16px;line-height:2em;">';
						
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
