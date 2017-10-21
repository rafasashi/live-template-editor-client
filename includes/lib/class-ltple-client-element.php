<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Element extends LTPLE_Client_Object { 
	
	public $parent;

	
	/**
	 * Constructor function
	 */ 
	 
	public function __construct( $parent ) {
		
		$this->parent = $parent;

		$this->parent->register_taxonomy( 'element-library', __( 'Element Library', 'live-template-editor-client' ), __( 'Element Library', 'live-template-editor-client' ),
			
			array('cb-default-layer'), 
			
			array(
				'hierarchical' 			=> true,
				'public' 				=> false,
				'show_ui' 				=> true,
				'show_in_nav_menus' 	=> false,
				'show_tagcloud' 		=> false,
				'meta_box_cb' 			=> null,
				'show_admin_column' 	=> true,
				'update_count_callback' => '',
				'show_in_rest'          => true,
				'rewrite' 				=> false,
				'sort' 					=> '',
			)
		);
		
		add_action( 'add_meta_boxes', function(){

			global $post;
			
			if( $post->post_type == 'user-element' ){
				
				$this->parent->admin->add_meta_box (
					
					'element-content',
					__( 'HTML content', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				$this->parent->admin->add_meta_box (
					
					'element-image',
					__( 'Image URL', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
			}

		});		

		add_filter('init', array( $this, 'init_element' ));
		
		add_filter('admin_init', array( $this, 'init_element_backend' ));
		
		add_filter('init', array( $this, 'init_element_frontend' ));
		
		add_action('wp_loaded', array($this,'get_element_types'));
	}
	
	public function get_element_types(){

		$this->types = $this->get_terms( 'element-library', array(
			
			'bootstrap-3-grid' => array(
			
				'name' 		=> 'Bootstrap 3 - Grid',
				'options'	=> array(
				
					'elements'	=> $this->index_keys(array(
					
						array(
						
							'name' 		=> '1 block',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/1-block.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);">block<span></span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '2 columns',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/2-columns.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '3 columns',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/3-columns.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '4 columns',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/4-columns.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '2 rows',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/2-rows.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '3 rows',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/3-rows.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="clearfix"></div></div>',
						),	
						array(
						
							'name' 		=> 'landing page',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/landing-page.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12 text-center" style="background: rgba(128, 194, 249, 0.18);"><span>block</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'nav left',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/nav-left.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>nav</span></div><div class="col-sm-9" style="background: rgba(128, 194, 249, 0.18);"><span>block</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'nav right',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/nav-right.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-9" style="background: rgba(128, 194, 249, 0.18);"><span>block</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>nav</span></div><div class="clearfix"></div>',
						),
						array(
						
							'name' 		=> 'L grid',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/l-grid.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="clearfix"></div></div>',
						),	
						array(
						
							'name' 		=> 'M grid',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/m-grid.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'S grid',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/s-grid.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'XS grid',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/xs-grid.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div></div>',
						),							
					)),
				),
			),
			/*
			'bootstrap-3-blog' => array(
			
				'name' 		=> 'Bootstrap 3 - Blog',
				'options'	=> array(
				
					'elements'	=> $this->index_keys(array(
					
						array(
						
							'name' 		=> '1 block',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/1-block.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);">block<span></span></div><div class="clearfix"></div></div>',
						),
					)),
				),
			),
			*/
		));
	}

	public function init_element(){

	
	}
	
	public function init_element_backend(){

		add_action('element-library_edit_form_fields', array( $this, 'get_fields' ) );
	
		add_action('create_element-library', array( $this, 'save_fields' ) );
		add_action('edit_element-library', array( $this, 'save_fields' ) );	
	
		add_action( 'load-edit-tags.php', function(){
			
			if( !empty($_GET['taxonomy']) && $_GET['taxonomy'] == 'element-library' ){
				
				$screen = get_current_screen();
				
				add_filter( 'bulk_actions-' . $screen->id, array( $this, 'get_bulk_actions' ) );
				add_filter( 'handle_bulk_actions-' . $screen->id, array( $this, 'handle_bulk_actions' ), 10, 3 );
				
				if( !isset($_GET['tag_ID']) ){
				
					add_action('ltple_taxonomy_action', array( $this, 'get_import_field' ) );
				}
				
				if( !empty($_FILES['importedElementLibrary']) ){
					
					foreach ($_FILES as $file => $array) {
						
						if($_FILES[$file]['error'] !== UPLOAD_ERR_OK ) {
							
							if( intval($_FILES[$file]['error']) != 4 ){
								
								echo "upload error : " . $_FILES[$file]['error'];
								exit;
							}
						}
						elseif( $_FILES[$file]['type'] !== 'application/octet-stream' ) {
							
							echo 'This is not a valid file type...';
							exit;							
						}
						elseif( $json = file_get_contents($_FILES[$file]['tmp_name'])){
							
							if( $library = json_decode($json,true) ){
								
								foreach( $library as $slug => $elements){

									if( !empty($elements['name']) && !empty($elements['options']['elements']) ){
										
										$library[$slug]['options']['elements'] = $this->index_keys($elements['options']['elements']);
									}
									else{
										
										unset($library[$slug]);
									}
								}
								
								if( !empty($library) ){
									
									$this->types = array_merge($this->types, $this->get_terms( 'element-library', $library ));
								}
							}
							else{
								
								echo 'This is not a valid json type...';
								exit;									
							}
						}
					}
				}				
			}
		});
	}
	
	public function init_element_frontend(){

	
	}
	
	public function get_fields($term){

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Elements</label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field( array(
				
					'type'				=> 'element',
					'id'				=> 'elements_'.$term->slug,
					'name'				=> 'elements_'.$term->slug,
					'array' 			=> [],
					'description'		=> ''
					
				), false );
				
			echo'</td>';
			
		echo'</tr>';		
	}
	
	public function save_fields($term_id){

		//collect all term related data for this new taxonomy
		
		$term = get_term($term_id);

		//save our custom fields as wp-options
		
		if( isset($_POST['elements_'.$term->slug]) ){

			update_option('elements_'.$term->slug, $_POST['elements_'.$term->slug]);			
		}
	}
	
	public function get_bulk_actions($bulk_actions){
		
		$bulk_actions['export'] = 'Export';
		
		return $bulk_actions;
	}
	
	public function handle_bulk_actions( $redirect_to, $action, $ids ){
		
		if($action == 'export'){
			
			$terms = get_terms( 'element-library', array(
			
				'include' 	 => $ids,
				'hide_empty' => false,
			) );
			
			if(!empty($terms)){
				
				$content = array();
				
				foreach( $terms as $term ){
					
					$elements = get_option( 'elements_' . $term->slug );
					
					if( !empty($elements['name'][0]) ){
						
						$elements = $this->group_keys($elements);

						$content[$term->slug] = array(
						
							'name' 		=> $term->name,
							'options'	=> array(
							
								'elements'	=> $elements
							)
						);
					}
				}
				
				// get json file
				
				$json = json_encode($content, JSON_PRETTY_PRINT);

				// output the file	
				
				header('Content-type: application/json');
				header('Content-Disposition: attachment; filename="elements.json"');
				header('Content-Length: ' . strlen($json));
				
				echo $json;
				exit;				
			}
		}
	}
	
	public function get_import_field(){
		
		echo '<div style="background:#f1f1f1;padding:5px 15px;border-radius:4px;">';
		
			echo '<h2>Import Element Library (JSON)</h2>';
			
			echo'<form method="post" action="" enctype="multipart/form-data">';
		
				echo '<div class="input-group" style="margin:15px 0;">';
				
					echo '<input style="padding:5px;" class="form-control" type="file" name="importedElementLibrary" accept=".json">';
					
					echo '<div class="input-group-btn">';
					
						echo '<input class="btn btn-default" value="Import" type="submit">';

					echo '</div>';
					
				echo '</div>';
				
			echo'</form>';
			
		echo '</div>';
	}
}
