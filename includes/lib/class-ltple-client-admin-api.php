<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Admin_API {
	
	var $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 1 );	
	}

	/**
	 * Generate HTML for displaying fields
	 * @param  array   $field Field data
	 * @param  boolean $echo  Whether to echo the field HTML or return it
	 * @return void
	 */
	public function display_field ( $data = array(), $post = false, $echo = true ) {

		// Get field info
		if ( isset( $data['field'] ) ) {
			$field = $data['field'];
		} else {
			$field = $data;
		}

		// Check for prefix on option name
		$option_name = '';
		if ( isset( $data['prefix'] ) ) {
			$option_name = $data['prefix'];
		}

		// Get saved data
		$data = '';
		if ( $post ) {

			// Get saved field data
			$option_name .= $field['id'];
			$option = get_post_meta( $post->ID, $field['id'], true );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}

		} else {

			// Get saved option
			$option_name .= $field['id'];
			$option = get_option( $option_name );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}

		}

		// Show default data if no option saved and default is supplied
		if ( $data === false && isset( $field['default'] ) ) {
			$data = $field['default'];
		} elseif ( $data === false ) {
			$data = '';
		}

		$html = '';

		switch( $field['type'] ) {

			case 'text':
			case 'url':
			case 'email':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
			break;
			case 'margin':
				
				$value = esc_attr( $data );
				
				if($value == ''){
					
					$value = esc_attr( $field['default'] );
				}
				
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $value . '" />' . "\n";
			break;

			case 'password':
			case 'number':
			case 'hidden':
				$min = '';
				if ( isset( $field['min'] ) ) {
					$min = ' min="' . esc_attr( $field['min'] ) . '"';
				}

				$max = '';
				if ( isset( $field['max'] ) ) {
					$max = ' max="' . esc_attr( $field['max'] ) . '"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . '/>' . "\n";
			break;

			case 'text_secret':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" />' . "\n";
			break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" style="width:100%;height:300px;" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>'. "\n";
			break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' == $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
			break;

			case 'checkbox_multi':
			
				foreach ( $field['options'] as $k => $v ) {
					
					$checked = false;
					if ( in_array( $k, (array) $data ) ) {
						$checked = true;
					}
					
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
					$html .= '<br>';
				}
			break;
			
			case 'checkbox_multi_plan_options':
				
				$total_price_amount = 0;
				$total_price_period='month';
				$total_price_currency='$';
				
				$html .= '<table class="widefat fixed striped" style="border:none;">';
				
				foreach ( $field['options'] as $taxonomy => $terms ) {
					
					$html .= '<tr>';
						
						$html .= '<th style="width:200px;">';
							
							$html .= '<label for="' . $taxonomy . '">'.$taxonomy.'</label> ';
								
						$html .= '</th>';
						
						$html .= '<td style="width:250px;">';
						
						foreach($terms as $term){

							$checked = false;
							
							if ( in_array( $term->slug, (array) $data ) ) {
								
								$checked = true;
							}
							
							$html .= '<span style="display:block;padding:1px 0;margin:0;">';
								
								$html .= '<label for="' . esc_attr( $field['id'] . '_' . $term->slug ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $term->slug ) . '" id="' . esc_attr( $field['id'] . '_' . $term->slug ) . '" /> ' . $term->name . '</label> ';
							
							$html .= '</span>';
						}
						
						$html .= '</td>';

						$html .= '<td>';
						
							$taxonomy_options = [];
							
							foreach($terms as $i => $term){
							
								$taxonomy_options[$i] = LTPLE_Client()->get_custom_taxonomy_options( $taxonomy, $term );
								
								if ( in_array( $term->slug, (array) $data ) ) {
									
									$total_price_amount = LTPLE_Client()->sum_custom_taxonomy_total_price_amount( $total_price_amount, $taxonomy_options[$i], $total_price_period);
									
									$total_storage = LTPLE_Client()->sum_custom_taxonomy_total_storage( $total_storage, $taxonomy_options[$i]);
								}

								$html .= '<span style="display:block;padding:1px 0;margin:0;">';
								
									if($taxonomy_options[$i]['storage_unit']=='templates'&&$taxonomy_options[$i]['storage_amount']==1){
										
										$html .= '+'.$taxonomy_options[$i]['storage_amount'].' template';
									}
									elseif($taxonomy_options[$i]['storage_amount']>0){
										
										$html .= '+'.$taxonomy_options[$i]['storage_amount'].' '.$taxonomy_options[$i]['storage_unit'];
									}	
									else{
										
										$html .= $taxonomy_options[$i]['storage_amount'].' '.$taxonomy_options[$i]['storage_unit'];
									}														
						
								$html .= '</span>';
							}
						
						$html .= '</td>';
						
						$html .= '<td>';
						
						foreach($terms as $i => $term){
							
							$html .= '<span style="display:block;padding:1px 0;margin:0;">';
							
								$html .= $taxonomy_options[$i]['price_amount'].$taxonomy_options[$i]['price_currency'].' / '.$taxonomy_options[$i]['price_period'];							
						
							$html .= '</span>';
						}
						
						$html .= '</td>';
						
					$html .= '</tr>';
						
				}
				
	
				
				$html .= '<tr style="font-weight:bold;">';
					
					$html .= '<th style="width:200px;">';
						
						$html .= '<label style="font-weight:bold;" for="totals">TOTALS</label> ';
							
					$html .= '</th>';
					
					$html .= '<td style="width:250px;"></td>';
					
					$html .= '<td>';
						
						if(!empty($total_storage)){
							
							foreach($total_storage as $storage_unit => $total_storage_amount){
								
								$html .= '<span style="display:block;">';
								
									if($storage_unit=='templates'&&$total_storage_amount==1){
										
										$html .= '+'.$total_storage_amount.' template';
									}
									elseif($total_storage_amount>0){
										
										$html .= '+'.$total_storage_amount.' '.$storage_unit;
									}									
									else{
										
										$html .= $total_storage_amount.' '.$storage_unit;
									}
									
								$html .= '</span>';
							}							
						}
						
					$html .= '</td>'; 
					
					$html .= '<td>';

						$html .= round($total_price_amount, 2).$total_price_currency.' / '.$total_price_period;
					
					$html .= '</td>';				
				
				$html .= '</table>';
				
			break;
			
			case 'email_series':
			
				if( isset($data['model']) && isset($data['days']) ){
					
					$email_series = $data;
				}
				else{
					
					$email_series = ['model' => [ 0 => '' ], 'days' => [ 0 => 0 ]];
				}
				
				$html .= '<div id="email_series">';
					
					$html .= ' <a href="#" class="add-input-group" style="line-height:40px;">Add email</a>';
				
					$html .= '<div class="input-group">';
						
						foreach( $email_series['model'] as $e => $model) {
											
							$html .= '<div class="input-group-row">';
						
								$html .= 'Send  ';
								
								$html .= '<select name="email_series[model][]" id="plan_email_model">';

								foreach ( $field['email-models'] as $k => $v ) {
									
									$selected = false;
									if ( $k == $model ) {
										
										$selected = true;
									}
									elseif(isset($field['model-selected']) && $field['model-selected'] == $k ){
										
										$selected = true;
									}
									
									$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
								}
								$html .= '</select> ';

								$html .= ' + ';
								
								$html .= '<input type="number" step="1" min="0" max="10" placeholder="0" name="email_series[days][]" id="plan_email_days" style="width: 50px;" value="'.$email_series['days'][$e].'">';
								
								$html .= ' day(s) after triggered ';
								
								if( $e > 0 ){
									
									$html .= '<a class="remove-input-group" href="#">[ x ]</a> ';
								}
								

							$html .= '</div>';						
						}
						
					$html .= '</div>';
					
				$html .= '</div>';

			break;

			case 'radio':
			
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( $k == $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
				
			break;
			
			case 'dropdown_categories':

				$html .=wp_dropdown_categories(array(
				
					'show_option_none' => 'None',
					'taxonomy'     => $field['taxonomy'],
					'name'    	   => $field['name'],
					'show_count'   => false,
					'hierarchical' => true,
					'selected'     => $field['selected'],
					'echo'		   => false,
					'hide_empty'   => false
				));			
			
			break;
			
			case 'select':

				if(isset($field['name'])){
					
					$html .= '<select name="' . $field['name'] . '" id="' . esc_attr( $field['id'] ) . '">';
				}
				else{
					
					$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				}

				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k == $data ) {
						
						$selected = true;
					}
					elseif(isset($field['selected']) && $field['selected'] == $k ){
						
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				
				
			break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;
			
			case 'select_main_app':
			
				//get admin IDs
				
				$users = get_users(array('role' => 'administrator'));
				
				$ids=[];
				
				foreach($users as $user){
					
					$ids[]=$user->ID;
				}

				//get app accounts
				
				$apps = get_posts(array(
				
					'author__in'  => $ids,
					'post_type'   => 'user-app',
					'post_status' => 'publish',
					'numberposts' => -1
				));
				
				$selected_id 	= get_option( $this->parent->settings->base . $field['id'] );
				$options 		= [];
				
				foreach($apps as $app){

					if(strpos($app->post_name, $field['app'] . '-')===0){
						
						$options[$app->ID] = str_replace($field['app'].' - ','',$app->post_title);
					}
				}
				
				if(isset($field['name'])){
					
					$html .= '<select name="' . $field['name'] . '" id="' . esc_attr( $field['id'] ) . '">';
				}
				else{
					
					$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				}

				foreach ( $options as $k => $v ) {
					
					$selected = false;
					if ( $k == $data ) {
						
						$selected = true;
					}
					elseif($selected_id == $k ){
						
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				
				
			break;

			case 'image':
				$image_thumb = '';
				if ( $data ) {
					$image_thumb = wp_get_attachment_thumb_url( $data );
				}
				$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'live-template-editor-client' ) . '" data-uploader_button_text="' . __( 'Use image' , 'live-template-editor-client' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'live-template-editor-client' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'live-template-editor-client' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
			break;

			case 'color':
				?><div class="color-picker" style="position:relative;">
			        <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
			        <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
			    </div>
			    <?php
			break;

		}

		//output description
		
		switch( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;

			default:
				if ( ! $post ) {
					$html .= '<label for="' . esc_attr( $field['id'] ) . '">' . "\n";
				}

				$html .= '<div>' . $field['description'] . '</div>' . "\n";

				if ( ! $post ) {
					$html .= '</label>' . "\n";
				}
			break;
		}

		if ( ! $echo ) {
			return $html;
		}

		echo $html;

	}

	/**
	 * Validate form field
	 * @param  string $data Submitted value
	 * @param  string $type Type of field to validate
	 * @return string       Validated value
	 */
	public function validate_field ( $data = '', $type = 'text' ) {

		switch( $type ) {
			case 'text': $data = esc_attr( $data ); break;
			case 'url': $data = esc_url( $data ); break;
			case 'email': $data = is_email( $data ); break;
		}

		return $data;
	}

	/**
	 * Add meta box to the dashboard
	 * @param string $id            Unique ID for metabox
	 * @param string $title         Display title of metabox
	 * @param array  $post_types    Post types to which this metabox applies
	 * @param string $context       Context in which to display this metabox ('advanced' or 'side')
	 * @param string $priority      Priority of this metabox ('default', 'low' or 'high')
	 * @param array  $callback_args Any axtra arguments that will be passed to the display function for this metabox
	 * @return void
	 */
	public function add_meta_box ( $id = '', $title = '', $post_types = array(), $context = 'advanced', $priority = 'default', $callback_args = null ) {

		// Get post type(s)
		if ( ! is_array( $post_types ) ) {
			
			$post_types = array( $post_types );
		}

		// Generate each metabox
		foreach ( $post_types as $post_type ) {
			
			add_meta_box( $id, $title, array( $this, 'meta_box_content' ), $post_type, $context, $priority, $callback_args );
		}
	}

	/**
	 * Display metabox content
	 * @param  object $post Post object
	 * @param  array  $args Arguments unique to this metabox
	 * @return void
	 */
	public function meta_box_content ( $post, $args ) {

		$fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type );

		if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

		echo '<div class="custom-field-panel">' . "\n";

		foreach ( $fields as $field ) {

			if ( ! isset( $field['metabox'] ) ) continue;

			if ( ! is_array( $field['metabox'] ) ) {
				
				$field['metabox'] = array( $field['metabox'] );
			}

			if ( in_array( $args['id'], $field['metabox'] ) ) {

				$this->display_meta_box_field( $field, $post );
			}
		}

		echo '</div>' . "\n";

	}

	/**
	 * Dispay field in metabox
	 * @param  array  $field Field data
	 * @param  object $post  Post object
	 * @return void
	 */
	public function display_meta_box_field ( $field = array(), $post ) {

		if ( ! is_array( $field ) || 0 == count( $field ) ) return;

		$field = '<p class="form-field"><label for="' . $field['id'] . '">' . $field['label'] . '</label>' . $this->display_field( $field, $post, false ) . '</p>' . "\n";

		echo $field;
	}

	/**
	 * Save metabox fields
	 * @param  integer $post_id Post ID
	 * @return void
	 */
	public function save_meta_boxes ( $post_id = 0 ) {

		if ( ! $post_id ) return;

		$post_type = get_post_type( $post_id );

		$fields = apply_filters( $post_type . '_custom_fields', array(), $post_type );

		if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

		foreach ( $fields as $field ) {
			
			if ( isset( $_REQUEST[ $field['id'] ] ) ) {
				
				update_post_meta( $post_id, $field['id'], $this->validate_field( $_REQUEST[ $field['id'] ], $field['type'] ) );
			} 
			else {
				
				update_post_meta( $post_id, $field['id'], '' );
			}
		}
	}

}