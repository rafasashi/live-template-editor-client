<?php

if ( ! defined( 'ABSPATH' ) ) exit;

	class LTPLE_Client_Admin_API {
		
		var $parent;
		var $html;
		
		/**
		 * Constructor function
		 */
		public function __construct ( $parent ) {
			
			$this->parent 	= $parent;
			
			add_action('save_post', array( $this, 'save_meta_boxes' ), 10, 1 );
			
			add_shortcode('ltple-client-admin', array( $this , 'get_admin_frontend' ) );
			
			do_action('updated_option', array( $this, 'settings_updated' ), 10, 3 );
		}
		
		public function get_admin_frontend(){
			
			
		}
		
		public function sanitize_id($id){
			
			return str_replace(array('[',']'),array('_',''),$id);
		}

		/**
		 * Generate HTML for displaying fields
		 * @param  array   $field Field data
		 * @param  boolean $echo  Whether to echo the field HTML or return it
		 * @return void
		 */
		public function display_field( $data = array(), $item = false, $echo = true ){

			// Get field info
			
			$field = ( isset( $data['field'] ) ? $data['field'] : $data );
			
			// Get field id
			
			// Check for prefix on option name
			
			$option_name = ( isset( $data['prefix'] ) ? $data['prefix'] : '' ) . ( !empty($field['name']) ? $field['name'] : $field['id']);
			
			// Get default
			
			$default = isset($field['default']) ? $field['default'] : null;
			
			// Get saved data
			
			$data = '';
			
			if ( !empty( $field['data'] ) ) {
				
				$data = $field['data'];
			}
			elseif( !empty($field['callback']) ){
				
				add_filter('ltple_admin_api_get_' . $field['id'],$field['callback'],10,1);
				
				$data = apply_filters('ltple_admin_api_get_' . $field['id'],$item);
			}
			elseif( !empty($item->ID) ){
				
				if ( !empty( $item->user_email ) ) {
					
					// Get saved field data

					$data = get_user_meta( $item->ID, $field['id'], true );
				} 
				else{

					// Get saved field data
					
					$data = get_post_meta( $item->ID, $field['id'], true );
				}
			}
			elseif ( !empty($item->term_id) ) {

				// Get saved field data
				
				$data = get_term_meta( $item->term_id, $field['id'], true );
			} 
			else{

				// Get saved option
				
				$data = get_option($option_name,$default);
			}
			
			// Show default data if no option saved and default is supplied

			if( $data === '' && !is_null($default) ) {
				
				$data = $default;
			} 
			elseif( $data === false ) {
				
				$data = '';
			}
			
			// get field id
			
			$id = !empty($field['id']) ? $this->sanitize_id($field['id']) : ( !empty($field['name']) ? $this->sanitize_id($field['name']) : 'f_' . rand(1000,9999) );
			
			// get field style
			
			$style = '';
			
			if( !empty($field['style']) ){
				
				$style = ' style="'.$field['style'].'"';
			}
			
			$disabled = ( ( isset($field['disabled']) && $field['disabled'] === true ) ? ' disabled="disabled"' : '' );

			$required = ( ( isset($field['required']) && $field['required'] === true ) ? ' required="true"' : '' );
			
			$placeholder = ( isset($field['placeholder']) ? $field['placeholder'] : '' );
			
			$html = '';
			
			switch( $field['type'] ) {
				
				case 'html':
					$html .= $data;
				break;
				case 'text':
				case 'url':
				case 'email':
					
					if( !empty($disabled) ){
						
						$html .= '<span class="form-group" style="margin:7px 0;">';
							
							$html .= '<input' . $style . ' class="form-control" id="' . $id . '" type="text" name="disabled_' . esc_attr( $option_name ) . '" value="' . esc_attr( $data ) . '" data-origine="' . esc_attr( $data ) . '" '.$disabled.'/>' . "\n";
							$html .= '<input type="hidden" name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $data ) . '" data-origine="' . esc_attr( $data ) . '" '.$required.'/>' . "\n";
						 
						$html .= '</span>';						
					}
					else{
					
						$html .= '<span class="form-group" style="margin:7px 0;">';
							
							$html .= '<input' . $style . ' class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="' . esc_attr( $data ) . '" data-origine="' . esc_attr( $data ) . '" '.$required.$disabled.'/>' . "\n";
						
						$html .= '</span>';
					}
					
				break;
				case 'file':
				
					$html .= wp_nonce_field( $this->parent->file, $id . '_nonce',true,false);

					$html .= '<input' . $style . ' class="form-control" id="' . $id . '" type="file" accept="'. ( !empty( $field['accept'] ) ? $field['accept'] : '' ) .'" name="' . esc_attr( $option_name ) . '" value="" '.$required.$disabled.'/>' . "\n";
				
					if( !empty($field['script']) ){
				
						wp_register_script( $this->parent->_token . '_file_script_'.$id, '', array('jquery') );
					
						wp_enqueue_script( $this->parent->_token . '_file_script_' . $id );
					
						wp_add_inline_script( $this->parent->_token . '_file_script_' . $id, $field['script'] );
					}
					
				break;
				
				case 'message':
				
					$html .= '<div' . $style . ' class="alert '. ( !empty( $field['class'] ) ? $field['class'] : 'alert-info' ) .'">'. $field['value'].'</div>';
					
				break;
				
				case 'slug':
					$html .= '<div' . $style . ' class="input-group">' . "\n";
					
						if( !empty($field['base']) ){
							
							$html .= '<span class="input-group-addon">'.$field['base'] . '</span>' . "\n";
						}
						else{
							
							$html .= '<span class="input-group-addon">' . $this->parent->urls->home . '/</span>' . "\n";
						}	
						 
						$html .= '<input class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="' . esc_attr( $data ) . '" '.$required.$disabled.'/>' . "\n";
						
						if( !isset($field['slash']) || $field['slash'] === true ){
							
							$html .= '<span class="input-group-addon">/</span>' . "\n";
						}
						
					$html .= '</div>' . "\n";
				break;
				
				case 'password':
					
					if ( isset( $field['show'] ) && $field['show'] === true ) {
					
						$html .= '<div class="input-group">';
					}
					
					$html .= '<input class="form-control" id="' . esc_attr($id) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="' . esc_attr( $data ) . '"' . '/>' . "\n";
					
					if ( isset( $field['show'] ) && $field['show'] === true ) {
						
						$html .= '<span class="input-group-btn">';
					
							$html .= '<input type="submit" class="btn btn-default show-password" data-target="#'.$field['id'].'" value="Show" />';
						
						$html .= '</span>';
						
						$html .= '</div>';
					}
					
				break;
				case 'hidden':
					$html .= '<input class="form-control" id="' . esc_attr($id) . '" type="hidden" name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $data ) . '"' . '/>' . "\n";
				break;
				case 'random':
					
					if( empty($data) ){
						
						$data = floor( mt_rand() * 1000000000 / ( mt_getrandmax() + 1) );
					}
					
					$html .= '<input data-random="number" class="form-control" id="' . esc_attr($id) . '" type="hidden" name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $data ) . '"' . '/>' . "\n";
				
				break;
				case 'number':
				case 'length':
				case 'distance':
				case 'weight':
				case 'mass':
				case 'price':
				
					$min = '';
					if ( isset( $field['min'] ) ) {
						$min = ' min="' . esc_attr( $field['min'] ) . '"';
					}

					$max = '';
					if ( isset( $field['max'] ) ) {
						$max = ' max="' . esc_attr( $field['max'] ) . '"';
					}
					
					$html .= '<span class="form-group" style="margin:7px 0;">';
					
						$html .= '<input'.$style.' class="form-control" id="' . esc_attr($id) . '" type="number" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="' . esc_attr( $data ) . '" data-origine="' . esc_attr( $data ) . '"' . $min . '' . $max . '/>' . "\n";
				
					$html .= '</span>';
					
				break;
				
				case 'text_secret':
					$html .= '<input class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="" '.$required.$disabled.'/>' . "\n";
				break;

				case 'textarea':
					
					if( !empty($data) ){
						
						if( is_array($data) ){
							
							$data = json_encode($data, JSON_PRETTY_PRINT);
						}	

						if( !isset($field['stripcslashes']) || $field['stripcslashes'] == true ){

							$data = stripcslashes($data);
						}
						
						if( !isset($field['htmlentities']) || $field['htmlentities'] == true ){
							
							$data = htmlentities($data);
						}
					}
					
					$maxlength = isset($field['maxlength']) && is_numeric($field['maxlength']) ? ' maxlength="' . $field['maxlength'] . '"' : '';
				
					$html .= '<textarea'.$style.' class="form-control" id="' . $id . '" style="width:100%;height:300px;" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '"'.$maxlength.$required.$disabled.'>' . $data . '</textarea>'. "\n";
				
				break;
				
				case 'terms':
				
					$id = $field['id'].'_input';
					
					$script = 'jQuery(document).ready(function($) {' . PHP_EOL;

						$script .= 'let _tag_input_suggestions_data = null;' . PHP_EOL;

						// Handle click of the input area
						 
						$script .= '$("#'.$id.'").click(function () {' . PHP_EOL;
							$script .= '$(this).find("input").focus();' . PHP_EOL;
						$script .= '});' . PHP_EOL;

						// handle the click of close button on the tags

						$script .= '$(document).on("click", "#'.$id.' .data .tag .close", function() {' . PHP_EOL;
							
							$script .= '$(this).parent().remove()' . PHP_EOL;

						$script .= '})' . PHP_EOL;

						// Handle the click of one suggestion

						$script .= '$(document).on("click", "#'.$id.' .autocomplete-items div", function() {' . PHP_EOL;
							
							$script .= 'let index=$(this).index()' . PHP_EOL;
							$script .= 'let data=_tag_input_suggestions_data[index];' . PHP_EOL;
							$script .= 'let data_holder = $(this).parents().eq(4).find("#'.$id.' .data")' . PHP_EOL;
							
							$script .= 'let template="<span class=\"tag button button-default\"><span class=\"text\">"+data.name+"</span><span class=\"close\">&times;</span><input type=\"hidden\" value=\'"+data.id+"\' name=\"tax_input['.$field['taxonomy'].'][]\"/></span>\n";' . PHP_EOL;
							
							$script .= '$(data_holder).parents().eq(2).find("#'.$id.' .data").append(template);' . PHP_EOL;
							$script .= '$(data_holder).val("")' . PHP_EOL;
							
							$script .= '$("#'.$id.' .autocomplete-items").html("");' . PHP_EOL;

						$script .= '})' . PHP_EOL;

						// detect enter on the input
						 
						$script .= '$("#'.$id.' input").on( "keydown", function(e) {' . PHP_EOL;
							
							$script .= 'if(e.which == 13){' . PHP_EOL;
							
								$script .= 'e.preventDefault();' . PHP_EOL;
								
								$script .= 'return false;' . PHP_EOL;
								
							$script .= '}' . PHP_EOL;

						$script .= '});' . PHP_EOL;

						$script .= '$("#'.$id.' input").on( "focusout", function(event) {' . PHP_EOL;
							
							$script .= '$(this).val("")' . PHP_EOL;
							$script .= 'var that = this;' . PHP_EOL;
							$script .= 'setTimeout(function(){ $(that).parents().eq(2).find(".autocomplete .autocomplete-items").html(""); }, 500);' . PHP_EOL;
						
						$script .= '});' . PHP_EOL;
						
						$script .= 'var typing;' . PHP_EOL;
						
						$script .= '$("#'.$id.' input").on( "keyup", function(event) {' . PHP_EOL;
							
							$script .= 'clearTimeout(typing);' . PHP_EOL;

							$script .= 'var query = $(this).val()' . PHP_EOL;

							$script .= 'if(event.which == 8) {' . PHP_EOL;
								
								$script .= 'if(query==""){' . PHP_EOL;
									
									// clear suggestions
								
									$script .= '$("#'.$id.' .autocomplete-items").html("");' . PHP_EOL;
									
									$script .= 'return;' . PHP_EOL;
								
								$script .= '}' . PHP_EOL;
							
							$script .= '}' . PHP_EOL;
							
							$script .= 'if( query.length < 3 ){' . PHP_EOL;
								
								$script .= 'return false;' . PHP_EOL;
							
							$script .= '}' . PHP_EOL;
							
							$script .= '$("#'.$id.' .autocomplete-items").html("");' . PHP_EOL;

							$script .= 'var element = $(this);' . PHP_EOL;
							 
							$script .= 'let sug_area=$(element).parents().eq(2).find(".autocomplete .autocomplete-items");' . PHP_EOL;
														
							$script .= 'typing = setTimeout(function() {' . PHP_EOL;

								// using ajax to populate suggestions
							 
								$script .= '$.ajax({' . PHP_EOL;
									$script .= 'url : ajaxurl,' . PHP_EOL;
									$script .= 'type: "GET",' . PHP_EOL;
									$script .= 'dataType : "json",' . PHP_EOL;
									$script .= 'data : {' . PHP_EOL;
										$script .= 's : query,' . PHP_EOL;
										$script .= 'action : "' . $field['action'] . '",' . PHP_EOL;
									$script .= '},' . PHP_EOL;
								$script .= '}).done(function( data ) {' . PHP_EOL;
									
									$script .= '_tag_input_suggestions_data = data;' . PHP_EOL;
									
									$script .= '$.each(data,function (key,value) {' . PHP_EOL;
										
										$script .= 'let template = $("<div>"+value.name+"</div>").hide()' . PHP_EOL;
										$script .= 'sug_area.append(template)' . PHP_EOL;
										$script .= 'template.show()' . PHP_EOL;

									$script .= '})' . PHP_EOL;
									
								$script .= '});' . PHP_EOL;
	
							$script .= '}, 800);' . PHP_EOL;
							
						$script .= '});' . PHP_EOL;
						
					$script .= '})' . PHP_EOL;
					
					// tag script
					
					wp_register_script( $this->parent->_token . '_tags_'.$id, '', array( 'jquery' ) );
					
					wp_enqueue_script( $this->parent->_token . '_tags_' . $id );
					
					wp_add_inline_script( $this->parent->_token . '_tags_' . $id, $script );
					
					// tag style
					
					wp_register_style($this->parent->_token . '-tags', false,array());
					wp_enqueue_style($this->parent->_token . '-tags');
					wp_add_inline_style($this->parent->_token . '-tags', '
						
						.tags-input .tag{
							margin:5px;
						}
						.tags-input .tag .close{
							padding-left: 4px;
							cursor: pointer;
						}
						.tags-input .autocomplete {
							position: relative;
							display: inline-block;
							margin-top:2px;
						}
						.tags-input .autocomplete-items {
							position: absolute;
							margin-top:1px;
							border: 1px solid #d4d4d4;
							border-top:none;
							border-bottom:none;
							z-index: 9999;
							top: 100%;
							left: 0;
							right: 0;
							max-height: 150px;
							overflow-y: auto;
						}
						.tags-input .autocomplete-items div {
							padding: 10px;
							cursor: pointer;
							background-color: #fff;
							border-bottom: 1px solid #d4d4d4;
						}
						.tags-input .autocomplete-items div:hover {
							background-color: #e9e9e9;
						}
						.tags-input .autocomplete-active {
							background-color: DodgerBlue !important;
							color: #ffffff;
						}
					' );
					
					$html .= '<div class="tags-input" id="'.$id.'">';
						
						$html .= '<span class="data">';
							
							// default empty value
							
							$html .= '<input type="hidden" value="-1" name="tax_input['.$field['taxonomy'].'][]"/>';
							
							if( !empty($data) ){
								
								foreach($data as $term){
								
									$html .= '<span class="tag button button-default"><span class="text">' . $term->name . '</span><span class="close m-0 p-0 border-0 bg-transparent">&times;</span><input type="hidden" value="' . $term->term_id . '" name="tax_input['.$field['taxonomy'].'][]"/></span>';
								}
							}
							
						$html .= '</span>';

						$html .= '<span class="autocomplete">';
							$html .= '<input style="border:none;" type="text" placeholder="add item...">';
							$html .= '<div class="autocomplete-items"></div>';
						$html .= '</span>';
						
					$html .= '</div>';
					
				break;
				
				case 'input_multi':
					
					// prepare inputs
					
					$inputs = !empty($field['fields']) ? $field['fields'] : array([
						
						'type' 			=> 'text',
						'placeholder' 	=> '',
						'style' 		=> '',
					]);
					
					// prepare data
					
					$f = !empty($inputs[0]['id']) ? sanitize_title($inputs[0]['id']) : key($inputs);
					
					if( !isset($data[$f]) ){

						$data = [
						
							$f => [ 0 => '' ]
						];
					}
					
					// render html
					
					$html .= '<div id="'.$field['id'].'" class="sortable">';
						
						$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'" style="line-height:40px;">Add field</a>';
					
						$html .= '<ul class="input-group ui-sortable">';
							
							foreach( $data[$f] as $e => $v ) {

								if($e > 0){
									
									$class='input-group-row ui-state-default ui-sortable-handle';
								}
								else{
									
									$class='input-group-row ui-state-default ui-state-disabled';
								}
						
								$html .= '<li class="'.$class.'" style="display:inline-block;width:100%;">';
									
									
									foreach( $inputs as $i => $input ){
										
										$key = !empty($input['id']) ? sanitize_title($input['id']) : $i;
										
										$input['id'] 	= $option_name.'['.$key.'][]';
										
										$input['data'] 	= isset($data[$key][$e]) ? str_replace('\\\'','\'',$data[$key][$e]) : '';
										
										if( !empty($input['before']) ){
										
											$html .= $input['before'];
										}
										
										if( !empty($input['label']) ){
											
											$html .= '<div style="font-weight:600;">'.ucfirst($input['label']).'</div>';
										}
										
										$html .= $this->display_field( $input, $item, false ) . PHP_EOL;
						
										if( !empty($input['after']) ){
											
											$html .= $input['after'];
										}
									}
									
									if( $e > 0 ){
										
										$html .= '<a class="remove-input-group" href="#">x</a> ';
									}

								$html .= '</li>';						
							}
						
						$html .= '</ul>';					
						
					$html .= '</div>';
					
				break;
				
				case 'text_editor':
					
					$settings = !empty($field['settings']) ? $field['settings'] : array();
					
					ob_start();

					wp_editor($data,$option_name,$settings);

					$html .= ob_get_clean();

					$html .= '<br/>';					
 
				break;
				
				case 'code_editor':
					
					$code = !empty($field['code']) ? $field['code'] : 'html';
			
					$type = ( $code == 'json' ? 'application' : 'text' ) . '/' . $code;
					
					$action = !empty($field['action']) ? $field['action'] : 'edit';
					
					if( !empty($data) ){
						
						if( is_array($data) ){
							
							$data = json_encode($data, JSON_PRETTY_PRINT);
						}	

						if( !isset($field['stripcslashes']) || $field['stripcslashes'] == true ){

							$data = stripcslashes($data);
						}
						
						if( !isset($field['htmlentities']) || $field['htmlentities'] == true ){
							
							$data = htmlentities($data);
						}
					}
					
					$html .= '<div id="' . $id . '" style="width:100%;height:300px;">';
					
						$html .= '<div class="btn-wrapper" style="background:#fbfbfb;border:1px solid #eee;padding:120px 0;text-align:center;">';
							
							// using <button> triggers post update if page not ready
							
							$html .= '<a href="#edit_'.$id.'" class="button button-primary button-hero btn btn-lg btn-primary">';
								
								$html .= ucfirst($action) . ' ' . strtoupper(($code=='javascript'?'js':$code)) . ' ' . ( $code == 'ssh' ? 'Key' : 'Code' );
							
							$html .= '</a>';
						
						$html .= '</div>';
						
						$html .= '<textarea style="display:none;" class="code-editor" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '"'.$required.$disabled.'>' . $data . '</textarea>'. "\n";
						
					$html .= '</div>';
					
					// enqueue script
					
					$settings = wp_enqueue_code_editor( array( 
						
						'type' 			=> $type,
						'codemirror'	=> array(
						
							'lint' => is_admin() ? true : false,
						),
					));
					
					$script = 'jQuery(document).ready(function($) {' . PHP_EOL;
						
						$script .= '$(\'#' . $id . ' a\').one(\'click\',function(e){' . PHP_EOL;
							
							$script .= 'e.preventDefault();' . PHP_EOL;
							$script .= 'e.stopPropagation();' . PHP_EOL;
							
							$script .= '$(\'#' . $id . ' .btn-wrapper\').hide();' . PHP_EOL;
							
							$script .= 'wp.codeEditor.initialize($(\'#' . $id . ' textarea\'), '.wp_json_encode( $settings ).');' . PHP_EOL;
							
						$script .= '})' . PHP_EOL;
							
					$script .= '})' . PHP_EOL;
					
					wp_register_script( $this->parent->_token . '_code_editor_'.$id, '', array( 'wp-theme-plugin-editor' ) );
					
					wp_enqueue_script( $this->parent->_token . '_code_editor_' . $id );
					
					wp_add_inline_script( $this->parent->_token . '_code_editor_' . $id, $script );
					
				break;
				
				case 'switch':
					
					$checked = '';
					
					if( $data && $data == 'on' ) {
						
						$checked = 'checked="checked"';
					}
					
					$html .= '<label class="switch">';
					
						$html .= '<input'.$style.' class="form-control" id="' . $id . '" type="checkbox" name="' . esc_attr( $option_name ) . '" ' . $checked . ''.$required.$disabled.'/>' . "\n";
						$html .= '<div class="slider round"></div>';
					
					$html .= '</label>';
					
				break;

				case 'checkbox':
					$checked = '';
					if ( $data && 'on' == $data ) {
						$checked = 'checked="checked"';
					}
					$html .= '<input'.$style.' class="form-control" id="' . $id . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . ''.$required.$disabled.'/>' . "\n";
				break;

				case 'checkbox_multi':
					
					$html .= '<div'.$style.' class="form-check">';
					
						foreach ( $field['options'] as $k => $v ) {
							
							$checked = false;
							if ( in_array( $k, (array) $data ) ) {
								$checked = true;
							}
							
							$html .= '<div for="' . $this->sanitize_id( $field['id'] . '_' . $k ) . '" class="form-check-label checkbox_multi"><input class="form-check-input" type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" data-origine="'.($checked ? 'true' : 'false').'" id="' . $this->sanitize_id( $field['id'] . '_' . $k ) . '" '.$required.$disabled.'/> ' . $v . '</div> ';
							//$html .= '<br>';
						}
					
					$html .= '</div>';
					
				break;
				
				case 'plan_value':
					
					$total_price_amount 	= $field['plan']['info']['total_price_amount'];
					$total_fee_amount 		= $field['plan']['info']['total_fee_amount'];
					$total_price_period		= $field['plan']['info']['total_price_period'];
					$total_fee_period		= $field['plan']['info']['total_fee_period'];
					$total_price_currency	= $field['plan']['info']['total_price_currency'];
					
					$html .= '<span style="color:red;font-weight:bold;font-size:20px;">';
					
						if( $total_fee_amount > 0 ){
							
							$html .= htmlentities(' ').round($total_fee_amount, 2).$total_price_currency.' '.$total_fee_period;
							$html .= '<br>+';
						}				
				
						$html .= round($total_price_amount, 2).$total_price_currency.' / '.$total_price_period;		
					
					$html .= '</span>';
					
				break;
				
				case 'checkbox_multi_plan_options':
					
					$plan_options = (array) $data;

					$build_tree = function ($terms, $parent_id = 0, $indentLevel = 0) use (&$build_tree) {
						
						$tree = array();

						foreach ($terms as $term) {
							
							if ($term->parent == $parent_id) {
								
								if( $term->parent > 0 ){
									
									$term->name = str_repeat('—', $indentLevel) . ' ' . $term->name;
								}
								else{
									
									$term->name = '<b>' . $term->name . '</b>';
								}
								
								$tree[] = $term;
								
								$tree = array_merge($tree, $build_tree($terms, $term->term_id, $indentLevel + 1));
							}
						}

						return $tree;
					};

					$dir_tree = array();

					foreach ($field['options'] as $taxonomy => $terms) {
						
						$dir_tree[$taxonomy] = $build_tree($terms);
					}
					
					$html .= '<table class="form-table">';
						
						foreach ( $dir_tree as $taxonomy => $terms ) {
							
							$html .= '<tr>';
							
								$html .= '<th style="padding:0;">';
									
									$html .= $taxonomy;
										
								$html .= '</th>';

							$html .= '</tr>';
							
							$html .= '<tr>';
		
								// attribute column
								
								$html .= '<td style="padding-left:0;">';
									
									$html .= '<table class="wp-list-table widefat fixed striped table-view-list">';
										
										foreach( $terms as $term ){
											
											$html .= '<tr>';

												$checked = false;
												
												if ( in_array( $term->slug, $plan_options ) ) {
													
													$checked = true;
												}
												
												$html .= '<td style="width:30%;">';
												
													$html .= '<span style="display:block;padding:1px 0;margin:0;">';
														
														$html .= '<div for="' . $this->sanitize_id( $field['id'] . '_' . $term->slug ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $term->slug ) . '" id="' . $this->sanitize_id( $field['id'] . '_' . $term->slug ) . '" /> ' . $term->name . '</div> ';
													
													$html .= '</span>';
													
												$html .= '</td>';

												// storage column
												
												$html .= '<td style="width:20%;">';

													if( !empty($term->options['storage']) ){
														
														foreach( $term->options['storage'] as $storage_unit => $storage_amount ){
															
															if( $storage_amount > 0){
																
																$html .='<span class="label label-primary">+' . $storage_amount . '</span> <span class="label label-info">' . $storage_unit . '</span><br>';
															}
															else{
																
																$html .='<span class="label label-primary">' . $storage_amount . '</span> <span class="label label-info">' . $storage_unit . '</span><br>';
															}
														}
													}
													
												$html .= '</td>';
												
												$html .= '<td style="width:25%;">';
												
													if( $term->taxonomy == 'account-option' ){
														
														if( !empty($term->options['bandwidth_amount']) ){
														
															if( $term->options['bandwidth_amount'] < 1000 ){
																
																$html .= '+'.$term->options['bandwidth_amount'].' GB' .'<br>';
															}
															elseif( $term->options['bandwidth_amount'] >= 1000 ){
																
																$html .= '+' . ( $term->options['bandwidth_amount'] / 1000 ) .' TB' .'<br>';
															}
														}
														
														// get addon options
														
														$html .= apply_filters('ltple_api_layer_plan_option','',$term);
													}
													
												$html .= '</td>';

												// price column
												
												$html .= '<td style="width:25%;text-align:right;">';

													$html .= '<span>';
													
														$html .= $term->options['price_amount'].$term->options['price_currency'].' / '.$term->options['price_period'];							
												
													$html .= '</span>';
													
												$html .= '</td>';
												
											$html .= '</tr>';
										}
									
									$html .= '</table>';
								
								$html .= '</td>';

							$html .= '</tr>';
						}
						
					$html .= '</table>';
					
				break;
				
				case 'addon_plugins':
					
					$html .= '<div id="the-list">';
					
						foreach( $this->parent->settings->addons as $addon ){
					
							$html .= '<div class="panel panel-default plugin-card plugin-card-akismet">';
							
								$html .= '<div class="panel-body plugin-card-top">';
								
									$html .= '<h3>';
									
										$html .= '<a href="'.$addon['addon_link'].'" class="thickbox open-plugin-details-modal">';
											
											$html .= $addon['title'];	
											
										$html .= '</a>';
										
									$html .= '</h3>';
									
									$html .= '<p>'.$addon['description'].'</p>';
									$html .= '<p class="authors"> <cite>By <a target="_blank" href="'.$addon['author_link'].'">'.$addon['author'].'</a></cite></p>';
									
								$html .= '</div>';
								
								$html .= '<div class="panel-footer plugin-card-bottom text-right">';
									
									$plugin_file = $addon['addon_name'] . '/' . $addon['addon_name'] . '.php';
									
									if( !file_exists( WP_PLUGIN_DIR . '/' . $addon['addon_name'] . '/' . $addon['addon_name'] . '.php' ) ){
										
										$url = $addon['source_url'];
										
										$html .= '<a href="' . $url . '" class="button install-now" aria-label="Install">Install Now</a>';
									}
									else{
										
										$html .= '<span>Installed</span>';
									}
								
								$html .= '</div>';
							
							$html .= '</div>';
						}
					
					$html .= '</div>';
				
				break;
				
				case 'key_value':
					
					if( !isset($data['key']) || !isset($data['value']) ){

						$data = [
						
							'key' 	=> [ 0 => '' ], 
							'value' => [ 0 => '' ]
						];
					}

					if( !empty($field['inputs']) && is_string($field['inputs']) ){
						
						$inputs = $field['inputs'];
					}
					elseif( empty($field['inputs']) || !is_array($field['inputs']) ) {
						
						$inputs = ['string','text','number','password','url','parameter','xpath','attribute','folder','filename'];
					}				
					else{
					
						$inputs = $field['inputs'];
					}

					$html .= '<div id="'.$field['id'].'" class="sortable">';
						
						$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'" style="line-height:40px;">Add field</a>';
					
						$html .= '<ul class="input-group ui-sortable">';
							
							foreach( $data['key'] as $e => $key) {

								if($e > 0){
									
									$class='input-group-row ui-state-default ui-sortable-handle';
								}
								else{
									
									$class='input-group-row ui-state-default ui-state-disabled';
								}
							
								$value = str_replace('\\\'','\'',$data['value'][$e]);
										
								$html .= '<li class="'.$class.'" style="display:inline-block;width:100%;">';
									
									if( is_array($inputs) ){
										
										$html .= '<select name="'.$option_name.'[input][]" style="float:left;">';

											foreach ( $inputs as $input ) {
												
												$selected = false;
												if ( isset($data['input'][$e]) && $data['input'][$e] == $input ) {
													
													$selected = true;
												}
												
												$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $input ) . '">' . $input . '</option>';
											}
										
										$html .= '</select> ';
									}
									
									$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['key']) ? $field['placeholder']['key'] : 'key' ).'" name="'.$option_name.'[key][]" style="width:30%;float:left;" value="'.$data['key'][$e].'">';
									
									if( is_string($inputs) ){
										
										$input = $inputs;
									}
									elseif(isset($data['input'][$e])){
										
										$input = $data['input'][$e];
									}
									
									if(!empty($input)){
										
										if($input == 'number'){
											
											$html .= '<input type="number" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'number' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
										elseif($input == 'password'){
											
											$html .= '<input type="password" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'password' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
										elseif($input == 'text'){
											
											$html .= '<textarea placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'text' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;height:200px;">' . $value . '</textarea>';
										}
										elseif( $input == 'select' && !empty($field['options']) ){
											
											$html .= '<select name="'.$option_name.'[value][]" style="float:left;">';
												
												foreach ( $field['options'] as $option => $name ) {
													
													$selected = false;

													if ( isset($data['value'][$e]) && $data['value'][$e] == $option ) {
														
														$selected = true;
													}
													
													$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $option ) . '">' . $name . '</option>';
												}
											
											$html .= '</select> ';								
										}
										else{
											
											$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'value' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
									}
									else{
										
										$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'value' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
									}

									if( $e > 0 ){
										
										$html .= '<a class="remove-input-group" href="#">x</a> ';
									}

								$html .= '</li>';						
							}
						
						$html .= '</ul>';					
						
					$html .= '</div>';

				break;
				
				case 'form':
					
					$data = !is_array($data) ? array() : $data;
					
					if( !empty($data) ){
							
						$defaults = $this->get_form_default_fields();
						
						foreach( $defaults as $key => $arr ){
							
							$len = count($arr);
							
							$data[$key] = isset($data[$key]) && is_array($data[$key]) ? $data[$key] : array();
							
							if( count($data[$key]) != $len ){
								
								$data[$key] = array_pad($data[$key],$len,'');
							}
						}
					}
		
					$id = ( !empty($field['id']) ? $field['id'] : 'form' );
					
					$inputs = !empty($field['inputs']) && is_array($field['inputs']) ? $field['inputs'] : array(
					
						'checkbox',
						'select',
						'text',
						'textarea',
						'number',
						'length',
						'weight',
						'password',
						'url',
						'color',
						'submit',
					);
					
					$html .= '<div id="'.$id.'" class="sortable">';
						
						if( !isset($field['action']) ){
						
							$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'" style="line-height:40px;" data-html="' . esc_html($this->display_field(array(
												
								'id'		=> $id,
								'name'		=> $id,
								'type'		=> 'form_fields',
								'inputs'	=> $inputs,
								'data'		=> array(
									
									'input'		=> '',
									'name'		=> '',
									'label'		=> '',
									'required'	=> 'optional',
									'value'		=> '',
								),
							
							),null,false)) . '">Add field</a>';
							
							$html .= '<ul class="input-group ui-sortable" style="width:100%;">';
								
								if( !empty($data['name']) ){
									
									foreach( $data['name'] as $e => $name ){
										
										$name = str_replace('-','_',sanitize_title($name));
										
										$html .= $this->display_field(array(
															
											'id'		=> $id,
											'name'		=> $id,
											'type'		=> 'form_fields',
											'inputs'	=> $inputs,
											'data'		=> array(
												
												'input' 	=> $data['input'][$e],
												'name' 		=> $name,
												'label' 	=> isset($data['label'][$e]) ? $data['label'][$e] : ucfirst(str_replace('_',' ',$name)),
												'required' 	=> isset($data['required'][$e])	? str_replace('\\\'','\'',$data['required'][$e]): 'optional',
												'value' 	=> isset($data['value'][$e])	? str_replace('\\\'','\'',$data['value'][$e])	: '',
											),
										
										),false,false);
									}
								}
								
							$html .= '</ul>';
						}
						else{
							
							$method = ( ( isset($field['method']) && $field['method'] == 'post' ) ? 'post' : 'get' );
							
							$html .= '<form id="formFilters" action="'.$field['action'].'" method="'.$method.'">';
							
							foreach( $data['name'] as $e => $name) {
								
								$name = str_replace('-','_',sanitize_title($name));

								if( isset($data['input'][$e]) ){

									$required = ( empty($data['required'][$e]) || $data['required'][$e] == 'required' ) ? true : false;
									
									$label = isset($data['label'][$e]) ? $data['label'][$e] : ucfirst(str_replace('_',' ',$name));
									
									$html .= '<label class="label label-default" style="padding:6px;margin:7px 0;text-align:left;display:block;font-weight:bold;font-size:14px;">'.$label.'</label>';
									
									if( $data['input'][$e] == 'submit' ){
										
										$html .= '<span class="form-group" style="margin: 7px 0 0 0;">';
										
											$html .= '<button style="width:100%;" type="'.$data['input'][$e].'" id="'.ucfirst($data['name'][$e]).'" class="control-input pull-right btn btn-sm btn-primary">'.ucfirst(ucfirst($data['value'][$e])).'</button>';
										
										$html .= '</span>';
									}
									elseif( $data['input'][$e] == 'checkbox' || $data['input'][$e] == 'select' ){

										if( $values = explode(PHP_EOL,$data['value'][$e]) ){
									
											$options = [];
											
											if( $data['input'][$e] == 'select' ){
												
												$options[] = '';
											}
									
											foreach( $values as $value ){
												
												$value = trim($value);
												
												if( !empty($value) ){
												
													$options[strtolower($value)] = ucfirst($value);
												}
											}
									
											if( $data['input'][$e] == 'checkbox' ){
									
												$html .= $this->display_field( array(
										
													'type'				=> 'checkbox_multi',
													'id'				=> $id.'['.$name.']',
													'options' 			=> $options,
													'required' 			=> false,
													'description'		=> '',
													'style'				=> 'margin:0px 10px;',
													'default'			=> ( isset($_REQUEST[$id][$name]) ? $_REQUEST[$id][$name] : ''),
													
												), false, false ); 
											}
											else{
												
												$html .= $this->display_field( array(
										
													'type'				=> 'select',
													'id'				=> $id.'['.$name.']',
													'options' 			=> $options,
													'required' 			=> $required,
													'description'		=> '',
													'style'				=> 'height:30px;padding:0px 5px;',
													'default'			=> ( isset($_REQUEST[$id][$name]) ? $_REQUEST[$id][$name] : ''),
													
												), false, false ); 											
											}
										}									
									}								
									else{
										
										$html .= $this->display_field( array(
								
											'type'				=> $data['input'][$e],
											'id'				=> $id.'['.$name.']',
											'value' 			=> $data['value'][$e],
											'required' 			=> $required,
											'placeholder' 		=> '',
											'description'		=> '',
											'default'			=> ( isset($_REQUEST[$id][$name]) ? $_REQUEST[$id][$name] : ''),
											
										), false, false ); 
									}
								}							
							}
							
							$html .= '</form>';
						}
						
					$html .= '</div>';
					
				break;
				
				case 'form_fields':

					$required = array('required','optional');
					
					$class='input-group-row ui-state-default ui-sortable-handle';
	
					$html = '<li class="'.$class.'" style="display:inline-block;width:100%;border-top:1px solid #eee;padding:15px 0 10px 0;margin:0;">';
				
						// inputs
						
						$html .= '<select class="form-control" name="'.$field['name'].'[input][]" style="width:100px;height:35px;float:left;">';

							foreach ( $field['inputs'] as $input ) {
								
								$selected = false;
								
								if ( isset($data['input']) && $data['input'] == $input ) {
									
									$selected = true;
								}
								
								$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $input ) . '">' . $input . '</option>';
							}
						
						$html .= '</select> ';
						
						// label
				
						$html .= '<input class="form-control" type="text" placeholder="label" name="'.$field['name'].'[label][]" style="width:20%;height:35px;float:left;" value="'.$data['label'].'">';
			
						// name
				
						$html .= '<input class="form-control" type="text" placeholder="name" name="'.$field['name'].'[name][]" style="width:20%;height:35px;float:left;" value="'.$data['name'].'">';

						// required
				
						$html .= '<select class="form-control" name="'.$field['name'].'[required][]" style="width:90px;height:35px;float:left;">';

							foreach ( $required as $r ) {
								
								$selected = false;
								
								if ( empty($disabled) && isset($data['required']) && $data['required'] == $r ) {
									
									$selected = true;
								}
								
								$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $r ) . '">' . $r . '</option>';
							}
						
						$html .= '</select> ';

						// value
						
						if(isset($data['input'])){
							
							if( in_array($data['input'],array(
								
								'number',
								'length',
								'weight',
							
							))){
								
								$html .= '<input class="form-control" type="number" placeholder="number" name="'.$field['name'].'[value][]" style="width:30%;height:35px;float:left;" value="'.$data['value'].'">';
							}
							elseif($data['input'] == 'password'){
								
								$html .= '<input class="form-control" type="password" placeholder="password" name="'.$field['name'].'[value][]" style="width:30%;height:35px;float:left;" value="'.$data['value'].'">';
							}
							elseif($data['input'] == 'textarea'){
								
								$html .= '<textarea class="form-control" placeholder="text" name="'.$field['name'].'[value][]" style="width:30%;height:35px;float:left;height:100px;">' . $data['value'] . '</textarea>';
							}									
							elseif($data['input'] == 'text'){
								
								$html .= '<input class="form-control" type="text" placeholder="value" name="'.$field['name'].'[value][]" style="width:30%;height:35px;float:left;" value="'.$data['value'].'">';
							}
							elseif($data['input'] == 'color'){
								
								$html .= '<input type="color" name="'.$field['name'].'[value][]" class="color form-control" value="' . $data['value'] . '" style="width:30%;height:35px;float:left;" />';
							}
							else{
								
								$html .= '<textarea class="form-control" placeholder="values" name="'.$field['name'].'[value][]" style="width:30%;height:100px;float:left;">' . $data['value'] . '</textarea>';
							}
						}
						else{
							
							$html .= '<textarea class="form-control" placeholder="values" name="'.$field['name'].'[value][]" style="width:30%;height:100px;float:left;">' . $data['value'] . '</textarea>';
						}

						$html .= '<a class="remove-input-group" href="#">x</a> ';

					$html .= '</li>';
					
				break;
				
				case 'element':
					
					$types = $this->parent->element->get_default_sections();
					
					if( !is_array($data) || !isset($data['name']) ){

						$data = array(
						
							'name' 		=> [ 0 => '' ],
							'category' 	=> [ 0 => '' ],
							'image' 	=> [ 0 => '' ],
							'content' 	=> [ 0 => '' ],
							'drop' 		=> [ 0 => '' ],
						);
					}

					$id = ( !empty($field['id']) ? $field['id'] : 'elements' );

					$html .= '<div id="'.$id.'" class="sortable">';
						
						$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'" style="line-height:40px;">Add element</a>';
					
						$html .= '<ul class="input-group ui-sortable" style="width:100%;">';
							
							foreach( $data['name'] as $e => $name) {
								
								$image 		= ( !empty($data['image'][$e]) ? $data['image'][$e] : '' );
								$content 	= ( !empty($data['content'][$e]) ? stripslashes($data['content'][$e]) : '' );
								$drop 		= ( !empty($data['drop'][$e]) ? $data['drop'][$e] : 'out' );
								
								if($e > 0){
									
									$class='input-group-row ui-state-default ui-sortable-handle';
								}
								else{
									
									$class='input-group-row ui-state-default ui-state-disabled';
								}								
									
								$html .= '<li class="'.$class.' '.$field['id'].'-row" style="display:inline-block;width:100%;border-top:1px solid #eee;padding:15px 0 10px 0;margin:0;">';
									
									$html .= '<div style="width:90%;float:left;">';
										
										// name
										
										$html .= '<div class="form-group" style="padding-bottom:10px;">';
									
											$html .= '<label>Name</label>';
											
											$html .= '<div>';
											
												$html .= '<input class="form-control" style="width:100%;" type="text" placeholder="value" name="'.$field['name'].'[name][]" value="'.$name.'">';
										
											$html .= '</div>';
										
										$html .= '</div>';
										
										// type
										
										$html .= '<div class="form-group" style="float:left;">';
										
											$html .= '<label>Type</label>';
									
											$html .= '<div>';
												
												$html .= '<select style="height:35px;" class="form-control" name="'.$field['name'].'[type][]">';

													foreach ( $types as $type => $name ) {
														
														$selected = false;
														
														if ( isset($data['type'][$e]) && $data['type'][$e] == $type ) {
															
															$selected = true;
														}
														
														$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $type ) . '">' . ucfirst($name) . '</option>';
													}
												
												$html .= '</select> ';
											
											$html .= '</div>';
											
										$html .= '</div>';
										
										// drop
										
										$html .= '<div class="form-group" style="float:left;">';
									
											$html .= '<label>Drop</label>';
											
											$html .= '<div>';
	
												$html .= '<select style="height:35px;" class="form-control" name="'.$field['name'].'[drop][]">';
													
													$html .= '<option value="in"' .( $drop == 'in' ? '  selected="selected"' : '' ).'/>In</option>';
													$html .= '<option value="out"'.( $drop == 'out' ? ' selected="selected"' : '' ).'>Out</option>';
												
												$html .= '</select> ';
												
											$html .= '</div>';
										
										$html .= '</div>';	

										// content
										
										$html .= '<div class="form-group" style="clear:both;padding-top:10px;">';
									
											$html .= '<label>Content</label>';
											
											$html .= '<div>';
											
												$html .= '<textarea class="form-control" style="height:150px;" placeholder="HTML content" name="'.$field['name'].'[content][]">' . $content . '</textarea>';
										
											$html .= '</div>';
										
										$html .= '</div>';			
										
										// image
										
										$html .= '<div class="form-group" style="clear:both;padding-bottom:10px;">';
									
											$html .= '<label>Image</label>';
											
											$html .= '<div>';
											
												$html .= '<input class="form-control" style="width:100%;" type="text" placeholder="https://" name="'.$field['name'].'[image][]" value="'.$image.'">';
										
											$html .= '</div>';
										
										$html .= '</div>';
										
										//$html .= '<input class="form-control" style="width:100%;" type="hidden" name="'.$field['name'].'[image][]" value="'.$image.'">';

									$html .= '</div>';
									
									if( $e > 0 ){
										
										$html .= '<div style="padding:0;float:right;margin-top: -10px;">';
										
											$html .= '<a class="remove-input-group" href="#">x</a> ';
										
										$html .= '</div>';
									}

								$html .= '</li>';						
							}
							
						$html .= '</ul>';
						
					$html .= '</div>';

				break;

				case 'radio':
					
					$i = 0;
					
					foreach ( $field['options'] as $k => $v ) {
						
						$checked = false;
						
						if( $k == $data || ( empty($data) && $i == 0 ) ) {
							
							$checked = true;
						}
						
						$html .= '<div for="' . esc_attr( $field['id'] . '_' . $k ) . '">';
						
						$html .= '<input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ';
							
							$html .= $v; 
							
						$html .= '</div> ';
						
						if( isset($field['inline']) && $field['inline'] === false ){
							
							$html .= '<br>'; 
						}
						
						$i++;
					}
					
				break;
				
				case 'avatar':
					
					$checked = array();
					
					$image_url = add_query_arg('_',time(),$data);

					$html .= '<img loading="lazy" class="img-circle" src="' . $image_url . '" height="125" width="125" title="My avatar" />'; 

					$html .= '<div class="input-group col-xs-10" style="margin:10px 0;">';
					
						$html .= '<input style="padding:2px;height:26px;" class="form-control input-sm" type="file" name="avatar" accept="image/*">';
						
						$html .= '<div class="input-group-btn">';
						
							$html .= '<input style="height: 26px;line-height: 0px;" class="btn btn-sm btn-default" value="Upload" type="submit">';
						
						$html .= '</div>';
						
					$html .= '</div>';
					
				break;
				
				case 'banner': 
					
					$html .= '<img loading="lazy" src="'.$field['default'].'" />';
					
					$html .= '<div class="input-group col-xs-10" style="margin:10px 0;">';
					
						$html .= '<input style="padding:2px;height:26px;" class="form-control input-sm" type="file" name="banner" accept="image/*">';
						
						$html .= '<div class="input-group-btn">';
						
							$html .= '<input style="height: 26px;line-height: 0px;" class="btn btn-sm btn-default" value="Upload" type="submit">';
						
						$html .= '</div>';
						
					$html .= '</div>';
					
				break;
				
				case 'select':
					
					$html .= '<select class="form-control'. ( !empty( $field['class'] ) ? ' ' . $field['class'] : '' ) .'" name="' . esc_attr( $option_name ) . '" id="' . $id . '"'.$style.'>';
					
					foreach ( $field['options'] as $key => $value ) {
						
						if( is_array($value) ){
							
							$html .= '<optgroup label="'.$key.'">';
								
								foreach( $value as $k => $v ){
									
									$selected = $k == $data ? true : false;
								
									$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
								}
								
							$html .= '</optgroup>';
						}
						else{

							$selected = $key == $data ? true : false;

							$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $key ) . '">' . $value . '</option>';
						}
					}
					
					$html .= '</select> ';
				break;

				case 'select_multi':
					$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . $id . '" multiple="multiple">';
					foreach ( $field['options'] as $k => $v ) {
						$selected = false;
						if ( in_array( $k, (array) $data ) ) {
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
					}
					$html .= '</select> ';
				break;
				
				case 'dynamic_tags':
					
					$html .= post_tags_meta_box( $item, array(
					
						'id' 		=> 'tagsdiv-' . $field['taxonomy'],
						'callback' 	=> !empty($field['callback']) ? $field['callback'] : '',
						'args' 		=> array(
							'taxonomy' => $field['taxonomy'],
						),
					));
				
				break;
				
				case 'dropdown_categories':
					
					$html .= wp_dropdown_categories(array(
					
						'show_option_none' => 'None',
						'taxonomy'     => $field['taxonomy'],
						'name'    	   => $option_name,
						'id'    	   => $field['id'],
						'show_count'   => false,
						'hierarchical' => true,
						'selected'     => $data,
						'echo'		   => false,
						'class'		   => 'form-control',
						'hide_empty'   => false
					));			
				
				break;			
				
				case 'dropdown_main_apps':
				
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
					
					$selected_id 	= get_option( $this->parent->_base . $field['id'] );
					$options 		= array( -1 => 'none');
					
					foreach($apps as $app){

						if(strpos($app->post_name, $field['app'] . '-')===0){
							
							$options[$app->ID] = str_replace($field['app'].' - ','',$app->post_title);
						}
					}
					
					if(isset($field['name'])){
						
						$html .= '<select class="form-control" name="' . $field['name'] . '" id="' . $id . '">';
					}
					else{
						
						$html .= '<select class="form-control" name="' . esc_attr( $option_name ) . '" id="' . $id . '">';
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

				case 'action_schedule':
					
					$html .= '<div id="'.$option_name.'">';

						$html .= ucfirst($field['action']).' ';				
						
						if( !empty($field['appId']) ){
							
							$html .= '<input type="hidden" name="'.$option_name.'[args][]" id="'.$option_name.'_app_id" value="'.$field['appId'].'"> ';
						}							
						
						if( $field['last'] === true ){
						
							$html .= 'last ';
						
							$html .= '<input type="number" step="1" min="0" max="100" placeholder="0" name="'.$option_name.'[args][]" id="'.$option_name.'_last" style="width: 50px;" value="'.( !empty($data['args'][1]) ? $data['args'][1] : 10 ).'"> ';
							
							$html .= ucfirst($field['unit']).' ';
						}
						
						$html .= 'every ';
						
						$html .= '<input type="number" step="5" min="15" max="60" placeholder="0" name="'.$option_name.'[every]" id="'.$option_name.'_every" style="width: 50px;" value="'.( isset($data['every']) ? $data['every'] : 15 ).'"> ';
						
						$html .= 'minutes ';

					$html .= '</div>';

				break;
				
				case 'importer':
					
					if( !wp_style_is($this->parent->_token . '-importer') ){
					
						// add importer style
					
						wp_register_style($this->parent->_token . '-importer', false,array());
						wp_enqueue_style($this->parent->_token . '-importer');
						wp_add_inline_style($this->parent->_token . '-importer', $this->get_importer_style() );
					}
					
					if( !wp_script_is($this->parent->_token . '-importer') ){
					
						// add importer script
					
						wp_register_script( $this->parent->_token . '-importer', '', array( 'jquery', 'jquery-touch-punch', 'jquery-ui-dialog' ) );
						wp_enqueue_script( $this->parent->_token . '-importer' );
						wp_add_inline_script( $this->parent->_token . '-importer', $this->get_importer_script() );
					}
					
					$source = $field['source'];
					
					$id = hash('crc32',$source);
					
					$html = '<div id="importer-buttons-'.$id.'" class="importer-buttons">';

						$html .= '<button data-id="'.$id.'" data-source="'.$source.'" data-toggle="dialog" data-target="#importerConsole" class="importer-button button button-default button-small">';
							
							$html .= $field['name'];
							
						$html .= '</button>';

					$html .= '</div>';
					
					$html .= '<div id="importer-meter-'.$id.'" class="importer-meter" style="display:none;">';
						
						$html .= '<span class="progress" style="width:0%;"></span>';
						
					$html .= '</div>';
					
					$html .= '<div id="importer-message-'.$id.'" class="importer-message">';
					
						$html .= '<span class="completed" style="display:none;">Completed!</span>';
				
					$html .= '</div>';
					
				break;
				
				case 'gallery':
				
					$html .='<div id="gallery-metabox">';
						
						$html .='<div style="padding:5px 0px;">';
							
							$html .='<a href="#" class="btn btn-xs btn-primary gallery-add" data-uploader-title="Add images" data-uploader-button-text="Add images">Add image</a>';
						
						$html .='</div>';
					
						$html .='<ul id="gallery-metabox-list" style="list-style:none;">';
						
							if ($data) : foreach($data as $value) : $image = wp_get_attachment_image_src($value);

							  $html .='<li class="pull-left" style="height:75px;width:75px;">';
								$html .='<input type="hidden" name="' . $option_name . '[]" value="' . $value . '">';
								$html .='<img loading="lazy" class="image-preview" src="' . $image[0] . '" style="max-width:73px;height:73px;">';
								$html .='<a class="remove-image" href="#">x</a>';
							  $html .='</li>';

							endforeach; endif;
							
						$html .='</ul>';
						
					$html .='</div>';
					
					wp_register_style( $this->parent->_token . '_gallery_style_'.$id, false, array());
					wp_enqueue_style( $this->parent->_token . '_gallery_style_'.$id );
				
					wp_add_inline_style( $this->parent->_token . '_gallery_style_'.$id, '
					
						#gallery-metabox-list {
							margin-bottom:10px;
							display:inline-block;
							list-style: none;
						}
						#gallery-metabox-list li {
							width: 75px;
							height: 75px;
							float: left;
							cursor: move;
							border: 1px solid #d5d5d5;
							margin: 9px 9px 0 0;
							background-color: #f7f7f7;
							background-position:center center;
							background-repeat: no-repeat;
							background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' xmlns:xlink=\'http://www.w3.org/1999/xlink\' version=\'1.1\' id=\'L4\' x=\'0px\' y=\'0px\' viewBox=\'0 0 100 100\' enable-background=\'new 0 0 0 0\' xml:space=\'preserve\'%3E%3Ccircle fill=\'%23007eff\' stroke=\'none\' cx=\'44\' cy=\'50\' r=\'1\'%3E%3Canimate attributeName=\'opacity\' dur=\'1s\' values=\'0;1;0\' repeatCount=\'indefinite\' begin=\'0.1\'/%3E%3C/circle%3E%3Ccircle fill=\'%23007eff\' stroke=\'none\' cx=\'47\' cy=\'50\' r=\'1\'%3E%3Canimate attributeName=\'opacity\' dur=\'1s\' values=\'0;1;0\' repeatCount=\'indefinite\' begin=\'0.2\'/%3E%3C/circle%3E%3Ccircle fill=\'%23007eff\' stroke=\'none\' cx=\'50\' cy=\'50\' r=\'1\'%3E%3Canimate attributeName=\'opacity\' dur=\'1s\' values=\'0;1;0\' repeatCount=\'indefinite\' begin=\'0.3\'/%3E%3C/circle%3E%3C/svg%3E");
							border-radius: 2px;
							position: relative;
							box-sizing: border-box;
						}
						#gallery-metabox-list img {
							max-width: 73px;
							height: 73px;
							background: #fff;
							display: block;
							border: none;
							margin: 0 auto;
							text-align: center;					
						}
						#gallery-metabox-list .remove-image {
							position: absolute;
							top: 0;
							text-align: center;
							color: #fff;
							border: 2px solid #fff;
							background: #ff0000;
							border-radius: 250px;
							height: 20px;
							width: 20px;
							font-size: 12px;
							font-weight: bold;
							margin: -5px;
							right: 0;
							text-decoration: none;
						}
						
						.gallery-add {
							
							display:inline-block;
						}
					');
					
					wp_enqueue_media();
					
					wp_register_script( $this->parent->_token . '_gallery_script_'.$id, '', array('jquery','jquery-ui-sortable') );
				
					wp_enqueue_script( $this->parent->_token . '_gallery_script_'.$id );
				
					wp_add_inline_script( $this->parent->_token . '_gallery_script_'.$id,'
					
						jQuery(function($) {
					
						  var file_frame;

						  $(document).on(\'click\', \'#gallery-metabox a.gallery-add\', function(e) {
							
							e.preventDefault();

							if (file_frame) file_frame.close();

							file_frame = wp.media.frames.file_frame = wp.media({
								
								title	: $(this).data(\'uploader-title\'),
								frame	: \'select\',
								library	: { 
									type: \'image\',
								},
								button	: {
									
									text: $(this).data(\'uploader-button-text\'),
								},
								multiple: true
							});
							
							file_frame.on(\'select\',function(){
								
								file_frame.state().get(\'selection\').map(function(attachment, i) {
									
									attachment = attachment.toJSON();
									$(\'#gallery-metabox-list\').append(\'<li class="pull-left"><input type="hidden" name="' . $option_name . '[]" value="\' + attachment.id + \'"><img loading="lazy" class="image-preview" src="\' + attachment.sizes.thumbnail.url + \'"><a class="remove-image" href="#">x</a></li>\');
								});
							});

							makeSortable();
							
							file_frame.open();

						  });

						  function resetIndex() {
							$(\'#gallery-metabox-list li\').each(function(i) {
							  $(this).find(\'input:hidden\').attr(\'name\', \'' . $option_name . '[\' + i + \']\');
							});
						  }

						  function makeSortable() {
							$(\'#gallery-metabox-list\').sortable({
							  opacity: 0.6,
							  stop: function() {
								resetIndex();
							  }
							});
						  }

						  $(document).on(\'click\', \'#gallery-metabox a.remove-image\', function(e) {
							e.preventDefault();

							$(this).parents(\'li\').animate({ opacity: 0 }, 200, function() {
							  $(this).remove();
							  resetIndex();
							});
						  });

						  makeSortable();

						});
					');

				break;
				
				case 'image':
				
					wp_enqueue_media();
		
					$image_thumb = '';
					
					if ( $data ) {
						
						$image_thumb = wp_get_attachment_thumb_url( $data );
					}
					
					$html .= '<img loading="lazy" id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
					$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'live-template-editor-client' ) . '" data-uploader_button_text="' . __( 'Use image' , 'live-template-editor-client' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'live-template-editor-client' ) . '" />' . "\n";
					$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'live-template-editor-client' ) . '" />' . "\n";
					$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
				
				break;

				case 'color':
				
					wp_enqueue_script('iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
					
					wp_register_script( $this->parent->_token . 'colorpicker', '', array('jquery') );
					wp_enqueue_script( $this->parent->_token . 'colorpicker' );
					wp_add_inline_script( $this->parent->_token . 'colorpicker',';(function($){
						
						$(document).ready(function(){
														
							var styleId = "colorPickerStyle";
							
							if ($("#" + styleId).length === 0) {
								
								var style = $("<style>").attr("id", styleId);

								style.html(".iris-picker {position:absolute;z-index:999;}");

								$("body").append(style);
							}
							
							$(".color-picker").iris({
								
								defaultColor: true,
								change: function(event,ui){
									
									var selectedColor = ui.color.toString();

									$(this).css("background-color",selectedColor);
								},
								clear: function(){
									
									$(this).css("background-color","");
								},
								//target: ".target-elem",
								palettes: false,
								hide: true
								
							}).on("click", function(){
								
								$(".color-picker").iris("hide");

								$(this).iris("show");
							});
							
							$(document).on("click", function(e) {
								
								var $visiblePicker = $(".iris-picker:visible");
								 
								if( !$visiblePicker.is(e.target) && $visiblePicker.has(e.target).length === 0 && !$visiblePicker.prev(".color-picker").is(e.target) ) {
										
									$visiblePicker.hide();
								}
							});
						}); 
							
					})(jQuery);');
					
					$value = !empty($data) ? esc_attr( $data ) : '#fff';
					
					$html .='<input type="text" name="' . esc_attr( $option_name ) . '" class="color-picker form-control" value="' . $value . '" style="background-color:'.$value.';max-width:200px;" '.$required.'/>';
					
				break;

			}

			//output description
			
			switch( $field['type'] ) {

				case 'checkbox_multi':
				case 'radio':
				case 'select_multi':
				
					if( !empty($field['description']) ){
						
						$html .= '<br/><span class="description">' . $field['description'] . '</span>';
					}
					
				break;

				default:
					
					if(!empty($field['description'])){
					
						if ( ! $item ) {
							
							$html .= '<div for="' . $id . '">' . "\n";
						}

						$html .= '<div><i style="color:#aaa;">' . $field['description'] . '</i></div>' . "\n";

						if ( ! $item ) {
							
							$html .= '</div>' . "\n";
						}
					}
					
				break;
			}

			if ( !$echo ) {
				
				return $html;
			}

			echo $html;
		}

		public function get_form_default_fields(){
		
			return array(
				
				'input' 	=> [ 0 => '' ],
				'label' 	=> [ 0 => '' ],
				'name' 		=> [ 0 => '' ],
				'required' 	=> [ 0 => '' ],
				'value' 	=> [ 0 => '' ],
			);
		}
		
		public function get_currency_options(){
			
			return array(
			
				'-1'  => 'Select a currency',
				'usd' => 'US Dollar',
				'eur' => 'Euro',
				'gbp' => 'British Pound',
				'aud' => 'Australian Dollar',
				'cad' => 'Canadian Dollar',
				'chf' => 'Swiss Franc',
				'dkk' => 'Danish Krone',
				'hkd' => 'Hong Kong Dollar',
				'jpy' => 'Japanese Yen',
				'nzd' => 'New Zealand Dollar',
				'sek' => 'Swedish Krona',
				'sgd' => 'Singapore Dollar',
				'cny' => 'Chinese Yuan',
				'brl' => 'Brazilian Real',
				'czk' => 'Czech Koruna',
				'isz' => 'Israeli Shekel',
				'mxn' => 'Mexican Peso',
				'myr' => 'Malaysian Ringgit',
				'php' => 'Philippine Peso',
				'pln' => 'Polish Zloty',
				'rub' => 'Russian Ruble',
				'thb' => 'Thai Baht',
				'try' => 'Turkish Lira',
				'ars' => 'Argentine Peso',
				'bam' => 'Bosnia and Herzegovina Convertible Mark',
				'clp' => 'Chilean Peso',
				'cop' => 'Colombian Peso',
				'dop' => 'Dominican Peso',
				'egp' => 'Egyptian Pound',
				'fjd' => 'Fiji Dollar',
				'gtq' => 'Guatemalan Quetzal',
				'huf' => 'Hungarian Forint',
				'idr' => 'Indonesian Rupiah',
				'isk' => 'Icelandic Krona',
				'jmd' => 'Jamaican Dollar',
				'kes' => 'Kenyan Shilling',
				'kwd' => 'Kuwaiti Dinar',
				'lkr' => 'Sri Lankan Rupee',
				'mad' => 'Moroccan Dirham',
				'mmk' => 'Myanmar Kyat',
				'mop' => 'Macau Pataca',
				'mur' => 'Mauritian Rupee',
				'mwk' => 'Malawian Kwacha',
				'ngn' => 'Nigerian Naira',
				'omr' => 'Omani Rial',
				'pen' => 'Peruvian Sol',
				'pgk' => 'Papua New Guinean Kina',
				'ron' => 'Romanian Leu',
				'sar' => 'Saudi Riyal',
				'twd' => 'Taiwan New Dollar',
				'uzs' => 'Uzbekistani Som',
				'vnd' => 'Vietnamese Dong',
				'zar' => 'South African Rand',
				'aoa' => 'Angolan Kwanza',
				'azn' => 'Azerbaijani Manat',
				'bsd' => 'Bahamian Dollar',
				'bgn' => 'Bulgarian Lev',
				'bob' => 'Bolivian Boliviano',
				'bwp' => 'Botswana Pula',
				'xcd' => 'East Caribbean Dollar',
				'hrk' => 'Croatian Kuna',
				'dzd' => 'Algerian Dinar',
				'etb' => 'Ethiopian Birr',
				'ghs' => 'Ghanaian Cedi',
				'gnf' => 'Guinean Franc',
				'kyd' => 'Cayman Islands Dollar',
				'khr' => 'Cambodian Riel',
				'kzt' => 'Kazakhstani Tenge',
				'lbp' => 'Lebanese Pound',
				'lsl' => 'Lesotho Loti',
				'mga' => 'Malagasy Ariary',
				'mwk' => 'Malawian Kwacha',
				'myr' => 'Malaysian Ringgit',
				'mvr' => 'Maldivian Rufiyaa',
				'mtn' => 'Mauritanian Ouguiya',
				'mzn' => 'Mozambican Metical',
				'nad' => 'Namibian Dollar',
				'nio' => 'Nicaraguan Córdoba',
				'pab' => 'Panamanian Balboa',
				'pyg' => 'Paraguayan Guarani',
				'ron' => 'Romanian Leu',
				'rsd' => 'Serbian Dinar',
				'scr' => 'Seychellois Rupee',
				'sll' => 'Sierra Leonean Leone',
				'sos' => 'Somali Shilling',
				'szp' => 'Eswatini Lilangeni',
				'tjs' => 'Tajikistani Somoni',
				'ttd' => 'Trinidad and Tobago Dollar',
				'tnd' => 'Tunisian Dinar',
				'ugx' => 'Ugandan Shilling',
				'wst' => 'Samoan Tala',
				'xaf' => 'Central African CFA Franc',
				'xof' => 'West African CFA Franc',
				'yer' => 'Yemeni Rial',
				'zmw' => 'Zambian Kwacha',
			);
		}
		
		public function get_country_options(){

			return array(
			
				'-1'  => 'Select a country',
				'AF'  => 'Afghanistan',
				'AX'  => 'Åland Islands',
				'AL'  => 'Albania',
				'DZ'  => 'Algeria',
				'AS'  => 'American Samoa',
				'AD'  => 'Andorra',
				'AO'  => 'Angola',
				'AI'  => 'Anguilla',
				'AQ'  => 'Antarctica',
				'AG'  => 'Antigua and Barbuda',
				'AR'  => 'Argentina',
				'AM'  => 'Armenia',
				'AW'  => 'Aruba',
				'AU'  => 'Australia',
				'AT'  => 'Austria',
				'AZ'  => 'Azerbaijan',
				'BS'  => 'Bahamas',
				'BH'  => 'Bahrain',
				'BD'  => 'Bangladesh',
				'BB'  => 'Barbados',
				'BY'  => 'Belarus',
				'BE'  => 'Belgium',
				'BZ'  => 'Belize',
				'BJ'  => 'Benin',
				'BM'  => 'Bermuda',
				'BT'  => 'Bhutan',
				'BO'  => 'Bolivia, Plurinational State of',
				'BQ'  => 'Bonaire, Sint Eustatius and Saba',
				'BA'  => 'Bosnia and Herzegovina',
				'BW'  => 'Botswana',
				'BV'  => 'Bouvet Island',
				'BR'  => 'Brazil',
				'IO'  => 'British Indian Ocean Territory',
				'BN'  => 'Brunei Darussalam',
				'BG'  => 'Bulgaria',
				'BF'  => 'Burkina Faso',
				'BI'  => 'Burundi',
				'KH'  => 'Cambodia',
				'CM'  => 'Cameroon',
				'CA'  => 'Canada',
				'CV'  => 'Cape Verde',
				'KY'  => 'Cayman Islands',
				'CF'  => 'Central African Republic',
				'TD'  => 'Chad',
				'CL'  => 'Chile',
				'CN'  => 'China',
				'CX'  => 'Christmas Island',
				'CC'  => 'Cocos (Keeling) Islands',
				'CO'  => 'Colombia',
				'KM'  => 'Comoros',
				'CG'  => 'Congo',
				'CD'  => 'Congo, the Democratic Republic of the',
				'CK'  => 'Cook Islands',
				'CR'  => 'Costa Rica',
				'CI'  => 'Côte d\'Ivoire',
				'HR'  => 'Croatia',
				'CU'  => 'Cuba',
				'CW'  => 'Curaçao',
				'CY'  => 'Cyprus',
				'CZ'  => 'Czech Republic',
				'DK'  => 'Denmark',
				'DJ'  => 'Djibouti',
				'DM'  => 'Dominica',
				'DO'  => 'Dominican Republic',
				'EC'  => 'Ecuador',
				'EG'  => 'Egypt',
				'SV'  => 'El Salvador',
				'GQ'  => 'Equatorial Guinea',
				'ER'  => 'Eritrea',
				'EE'  => 'Estonia',
				'ET'  => 'Ethiopia',
				'FK'  => 'Falkland Islands (Malvinas)',
				'FO'  => 'Faroe Islands',
				'FJ'  => 'Fiji',
				'FI'  => 'Finland',
				'FR'  => 'France',
				'GF'  => 'French Guiana',
				'PF'  => 'French Polynesia',
				'TF'  => 'French Southern Territories',
				'GA'  => 'Gabon',
				'GM'  => 'Gambia',
				'GE'  => 'Georgia',
				'DE'  => 'Germany',
				'GH'  => 'Ghana',
				'GI'  => 'Gibraltar',
				'GR'  => 'Greece',
				'GL'  => 'Greenland',
				'GD'  => 'Grenada',
				'GP'  => 'Guadeloupe',
				'GU'  => 'Guam',
				'GT'  => 'Guatemala',
				'GG'  => 'Guernsey',
				'GN'  => 'Guinea',
				'GW'  => 'Guinea-Bissau',
				'GY'  => 'Guyana',
				'HT'  => 'Haiti',
				'HM'  => 'Heard Island and McDonald Islands',
				'VA'  => 'Holy See (Vatican City State)',
				'HN'  => 'Honduras',
				'HK'  => 'Hong Kong',
				'HU'  => 'Hungary',
				'IS'  => 'Iceland',
				'IN'  => 'India',
				'ID'  => 'Indonesia',
				'IR'  => 'Iran, Islamic Republic of',
				'IQ'  => 'Iraq',
				'IE'  => 'Ireland',
				'IM'  => 'Isle of Man',
				'IL'  => 'Israel',
				'IT'  => 'Italy',
				'JM'  => 'Jamaica',
				'JP'  => 'Japan',
				'JE'  => 'Jersey',
				'JO'  => 'Jordan',
				'KZ'  => 'Kazakhstan',
				'KE'  => 'Kenya',
				'KI'  => 'Kiribati',
				'KP'  => 'Korea, Democratic People\'s Republic of',
				'KR'  => 'Korea, Republic of',
				'KW'  => 'Kuwait',
				'KG'  => 'Kyrgyzstan',
				'LA'  => 'Lao People\'s Democratic Republic',
				'LV'  => 'Latvia',
				'LB'  => 'Lebanon',
				'LS'  => 'Lesotho',
				'LR'  => 'Liberia',
				'LY'  => 'Libya',
				'LI'  => 'Liechtenstein',
				'LT'  => 'Lithuania',
				'LU'  => 'Luxembourg',
				'MO'  => 'Macao',
				'MK'  => 'Macedonia, the former Yugoslav Republic of',
				'MG'  => 'Madagascar',
				'MW'  => 'Malawi',
				'MY'  => 'Malaysia',
				'MV'  => 'Maldives',
				'ML'  => 'Mali',
				'MT'  => 'Malta',
				'MH'  => 'Marshall Islands',
				'MQ'  => 'Martinique',
				'MR'  => 'Mauritania',
				'MU'  => 'Mauritius',
				'YT'  => 'Mayotte',
				'MX'  => 'Mexico',
				'FM'  => 'Micronesia, Federated States of',
				'MD'  => 'Moldova, Republic of',
				'MC'  => 'Monaco',
				'MN'  => 'Mongolia',
				'ME'  => 'Montenegro',
				'MS'  => 'Montserrat',
				'MA'  => 'Morocco',
				'MZ'  => 'Mozambique',
				'MM'  => 'Myanmar',
				'NA'  => 'Namibia',
				'NR'  => 'Nauru',
				'NP'  => 'Nepal',
				'NL'  => 'Netherlands',
				'NC'  => 'New Caledonia',
				'NZ'  => 'New Zealand',
				'NI'  => 'Nicaragua',
				'NE'  => 'Niger',
				'NG'  => 'Nigeria',
				'NU'  => 'Niue',
				'NF'  => 'Norfolk Island',
				'MP'  => 'Northern Mariana Islands',
				'NO'  => 'Norway',
				'OM'  => 'Oman',
				'PK'  => 'Pakistan',
				'PW'  => 'Palau',
				'PS'  => 'Palestinian Territory, Occupied',
				'PA'  => 'Panama',
				'PG'  => 'Papua New Guinea',
				'PY'  => 'Paraguay',
				'PE'  => 'Peru',
				'PH'  => 'Philippines',
				'PN'  => 'Pitcairn',
				'PL'  => 'Poland',
				'PT'  => 'Portugal',
				'PR'  => 'Puerto Rico',
				'QA'  => 'Qatar',
				'RE'  => 'Réunion',
				'RO'  => 'Romania',
				'RU'  => 'Russian Federation',
				'RW'  => 'Rwanda',
				'BL'  => 'Saint Barthélemy',
				'SH'  => 'Saint Helena, Ascension and Tristan da Cunha',
				'KN'  => 'Saint Kitts and Nevis',
				'LC'  => 'Saint Lucia',
				'MF'  => 'Saint Martin (French part)',
				'PM'  => 'Saint Pierre and Miquelon',
				'VC'  => 'Saint Vincent and the Grenadines',
				'WS'  => 'Samoa',
				'SM'  => 'San Marino',
				'ST'  => 'Sao Tome and Principe',
				'SA'  => 'Saudi Arabia',
				'SN'  => 'Senegal',
				'RS'  => 'Serbia',
				'SC'  => 'Seychelles',
				'SL'  => 'Sierra Leone',
				'SG'  => 'Singapore',
				'SX'  => 'Sint Maarten (Dutch part)',
				'SK'  => 'Slovakia',
				'SI'  => 'Slovenia',
				'SB'  => 'Solomon Islands',
				'SO'  => 'Somalia',
				'ZA'  => 'South Africa',
				'GS'  => 'South Georgia and the South Sandwich Islands',
				'SS'  => 'South Sudan',
				'ES'  => 'Spain',
				'LK'  => 'Sri Lanka',
				'SD'  => 'Sudan',
				'SR'  => 'Suriname',
				'SJ'  => 'Svalbard and Jan Mayen',
				'SZ'  => 'Swaziland',
				'SE'  => 'Sweden',
				'CH'  => 'Switzerland',
				'SY'  => 'Syrian Arab Republic',
				'TW'  => 'Taiwan, Province of China',
				'TJ'  => 'Tajikistan',
				'TZ'  => 'Tanzania, United Republic of',
				'TH'  => 'Thailand',
				'TL'  => 'Timor-Leste',
				'TG'  => 'Togo',
				'TK'  => 'Tokelau',
				'TO'  => 'Tonga',
				'TT'  => 'Trinidad and Tobago',
				'TN'  => 'Tunisia',
				'TR'  => 'Turkey',
				'TM'  => 'Turkmenistan',
				'TC'  => 'Turks and Caicos Islands',
				'TV'  => 'Tuvalu',
				'UG'  => 'Uganda',
				'UA'  => 'Ukraine',
				'AE'  => 'United Arab Emirates',
				'GB'  => 'United Kingdom',
				'US'  => 'United States',
				'UM'  => 'United States Minor Outlying Islands',
				'UY'  => 'Uruguay',
				'UZ'  => 'Uzbekistan',
				'VU'  => 'Vanuatu',
				'VE'  => 'Venezuela, Bolivarian Republic of',
				'VN'  => 'Viet Nam',
				'VG'  => 'Virgin Islands, British',
				'VI'  => 'Virgin Islands, U.S.',
				'WF'  => 'Wallis and Futuna',
				'EH'  => 'Western Sahara',
				'YE'  => 'Yemen',
				'ZM'  => 'Zambia',
				'ZW'  => 'Zimbabwe',
			);		
		}
		
		public function get_importer_script(){

			$script = '
				
				;(function($){
					
					// define a new console
					
					var console = (function(oldCons){
						
						return {
						
							log: function(text){
								
								oldCons.log(text);
								
								$("#importerLogs").append("<p style=\"margin-top:0px;color:green;\">" + text + "</p>");
							},
							info: function (text) {
								
								oldCons.info(text);
								
								$("#importerLogs").append("<p style=\"margin-top:0px;font-weight:bold;\">" + text + "</p>");
							},
							warn: function (text) {
								
								oldCons.warn(text);
								
								$("#importerLogs").append("<p style=\"margin-top:0px;color:orange;\">" + text + "</p>");
							},
							error: function (text) {
								
								oldCons.error(text);
								
								$("#importerLogs").append("<p style=\"margin-top:0px;color:red;\">" + text + "</p>");
							}
						};
						
					}(window.console));

					//Then redefine the old console
					
					window.console = console;

					$(document).ready(function(){
						
						// add importer console
						
						$("body").append("<div id=\'importerConsole\' style=\'display:none;\' title=\'Importer Console\'><div id=\'importerLogs\' style=\'height:50vh;width:50vw;\'></div></div>");
				
						$("#importerConsole").dialog({
							
							autoOpen 	: false,
							width 		: "auto",
							height 		: "auto",
							resizable 	: false
						});
						
						// requests handler
						
						var ajaxQueue = $({});

						$.ajaxQueue = function( ajaxOpts ) {
							
							var jqXHR,
								dfd = $.Deferred(),
								promise = dfd.promise();

							// queue our ajax request
							ajaxQueue.queue( doRequest );

							// add the abort method
							promise.abort = function( statusText ) {

								// proxy abort to the jqXHR if it is active
								if ( jqXHR ) {
									return jqXHR.abort( statusText );
								}

								// if there wasnt already a jqXHR we need to remove from queue
								var queue = ajaxQueue.queue(),
									index = $.inArray( doRequest, queue );

								if ( index > -1 ) {
									queue.splice( index, 1 );
								}

								// and then reject the deferred
								dfd.rejectWith( ajaxOpts.context || ajaxOpts,
									[ promise, statusText, "" ] );

								return promise;
							};

							// run the actual query
							function doRequest( next ) {
								jqXHR = $.ajax( ajaxOpts )
									.done( dfd.resolve )
									.fail( dfd.reject )
									.then( next, next );
							}

							return promise;
						};
						
						// bind buttons
						
						$("button[data-target=\'\\#importerConsole\']").each(function(i,$btn){
							
							$(this).on("click",function(){
								
								var id = $(this).attr("data-id");
			
								var $meter 		= $("#importer-meter-" + id);
								var $progress 	= $("#importer-meter-" + id + " .progress");
								var $completed 	= $("#importer-message-" + id + " .completed");
								
								$(this).prop("disabled",true);
								$meter.show();
								$completed.hide();
								
								var source = $(this).attr("data-source");
								
								$.ajaxQueue({
									
									type 		: "GET",
									url  		: source,
									cache		: false,
									beforeSend	: function(){
										
										
									},
									error: function() {
									
										console.error(source + " error");
																										
										$meter.hide();
										$(this).prop("disabled",false);
									},
									success: function(sources) {
									
										var proto = window.location.href.split("/")[0];

										// get total requests
										
										var total = sources.length;
										
										if( total > 0 ){
											
											$progress.css("width", ( 100 / total / 10 ) + "%");
											
											var r = 0;
											
											$.each(sources,function(i,source){

												$.ajaxQueue({
													
													type 		: "GET",
													url  		: source,
													cache		: false,
													beforeSend	: function(){
														
														if( i === 0 ){
															
															console.info("Importing data...");
														}
													},
													error: function() {
													
														console.error(source + " error");
													},
													success: function(response) {
														
														console.log(JSON.stringify(response) );
													},
													complete: function(){
														
														++r;
														
														var progress = r * ( 100 / total );
														
														$progress.css("width", progress + "%");
														
														if( progress > 99 ){
															
															$progress.bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function(){

																$meter.hide();
																$progress.css("width", "0%");
																$(this).prop("disabled",false);
																$completed.show();
															});
															
														}
													}
												});
											});
										}
										else{
											
											console.warn("Nothing to import");
														
											$meter.hide();
											$progress.css("width", "0%");
											$(this).prop("disabled",false);
											$completed.show();
										}
									},
									complete: function(){
										
										
									}
								});
							});
						});
					});
					
				})(jQuery);
			';	

			return $script;
		}
		
		
		public static function get_importer_style(){
			
			$style = '
								
				.importer-buttons {
					margin-bottom:10px;
				}
				
				.importer-buttons button {
					margin-right:5px !important;
				}
				
				.importer-message {
					padding:0 !important;
				}
						
				.importer-meter { 
					height: 10px;
					padding: 5px;
					position: relative;
					background: #555;
					-moz-border-radius: 25px;
					-webkit-border-radius: 25px;
					border-radius: 25px;
					box-shadow: inset 0 -1px 1px rgba(255,255,255,0.3);
				}
				.importer-meter > span {
				  display: block;
				  height: 100%;
				  border-top-right-radius: 8px;
				  border-bottom-right-radius: 8px;
				  border-top-left-radius: 20px;
				  border-bottom-left-radius: 20px;
				  background-color: rgb(43,194,83);
				  background-image: linear-gradient(
					center bottom,
					rgb(43,194,83) 37%,
					rgb(84,240,84) 69%
				  );
				  box-shadow: 
					inset 0 2px 9px  rgba(255,255,255,0.3),
					inset 0 -2px 6px rgba(0,0,0,0.4);
				  position: relative;
				  overflow: hidden;
				  transition: width 5s;
				}

				.importer-meter > span:after {
					content: "";
					position: absolute;
					top: 0; left: 0; bottom: 0; right: 0;
					background-image: 
					   -webkit-gradient(linear, 0 0, 100% 100%, 
						  color-stop(.25, rgba(255, 255, 255, .2)), 
						  color-stop(.25, transparent), color-stop(.5, transparent), 
						  color-stop(.5, rgba(255, 255, 255, .2)), 
						  color-stop(.75, rgba(255, 255, 255, .2)), 
						  color-stop(.75, transparent), to(transparent)
					   );
					background-image: 
						-moz-linear-gradient(
						  -45deg, 
						  rgba(255, 255, 255, .2) 25%, 
						  transparent 25%, 
						  transparent 50%, 
						  rgba(255, 255, 255, .2) 50%, 
						  rgba(255, 255, 255, .2) 75%, 
						  transparent 75%, 
						  transparent
					   );
					z-index: 1;
					-webkit-background-size: 50px 50px;
					-moz-background-size: 50px 50px;
					-webkit-animation: move 2s linear infinite;
					   -webkit-border-top-right-radius: 8px;
					-webkit-border-bottom-right-radius: 8px;
						   -moz-border-radius-topright: 8px;
						-moz-border-radius-bottomright: 8px;
							   border-top-right-radius: 8px;
							border-bottom-right-radius: 8px;
						-webkit-border-top-left-radius: 20px;
					 -webkit-border-bottom-left-radius: 20px;
							-moz-border-radius-topleft: 20px;
						 -moz-border-radius-bottomleft: 20px;
								border-top-left-radius: 20px;
							 border-bottom-left-radius: 20px;
					overflow: hidden;
				}
				
				@-webkit-keyframes move {
					0% {
					   background-position: 0 0;
					}
					100% {
					   background-position: 50px 50px;
					}
				}				
			';
			
			return $style;
		}

		/**
		 * Validate form field
		 * @param  string $data Submitted value
		 * @param  string $type Type of field to validate
		 * @return string       Validated value
		 */
		public function validate_output ( $data = '', $type = 'text' ) {
			
			switch( $type ) {
				
				case 'text'		: $data = esc_attr( $data ); break;
				case 'url'		: $data = esc_url( $data ); break;
				case 'email'	: $data = is_email( $data ); break;
			}

			return $data;
		}
		
		public function validate_input( $data = '', $type = 'text' ) {
			
			if( is_array($data) ){
				
				foreach( $data as $key => $value ){
					
					if( is_string($value) || is_numeric($value) ){
					
						$data[$key] = $this->validate_input($value,$type);
					}
				}					
			}
			else{
				
				switch( $type ) {
					
					case 'textarea'	: $data = sanitize_textarea_field( $data ); break;
					case 'url'		: $data = sanitize_url( $data ); break;
					case 'email'	: $data = sanitize_email( $data ); break;
					default			: $data = sanitize_text_field( $data ); break;
				}
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

		public function add_meta_boxes($fields){
			
			if( !empty($fields) ){
				
				foreach( $fields as $field ){
					
					if( !empty($field['metabox']) ){
					
						if( !isset($field['metabox']['add_new']) || $field['metabox']['add_new'] || !empty($_REQUEST['post']) ){
						
							if( !empty($field['metabox']['name']) && !empty($field['metabox']['title']) && !empty($field['metabox']['screen']) && !empty($field['metabox']['context']) ){
								
								$this->add_meta_box(
									
									$field['metabox']['name'],
									$field['metabox']['title'],
									$field['metabox']['screen'],
									$field['metabox']['context']
								);						
							}
						}
					}
				}
			}
		}
		
		/**
		 * Display metabox content
		 * @param  object $post Post object
		 * @param  array  $args Arguments unique to this metabox
		 * @return void
		 */
		public function meta_box_content( $post, $args ) {
			
			if( !empty($post->post_type) ){
				
				$fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type, $post );
				
				if ( is_array( $fields ) && !empty($fields) ){

					echo '<div class="custom-field-panel" style="display:inline-block;width:100%;margin-top:10px;">' . "\n";
					
						foreach ( $fields as $field ) {
							
							if( isset($field['metabox']) ){
							
								if( is_string( $field['metabox'] ) ) {
									
									$field['metabox'] = array( $field['metabox'] );
								}

								if( is_array($field['metabox']) && $field['metabox']['name'] == $args['id'] ){
									
									$this->display_meta_box_field( $field, $post );
								}
							}
						}

					echo '</div>' . "\n";
				}
			}
		}

		/**
		 * Dispay field in metabox
		 * @param  array  $field Field data
		 * @param  object $post  Post object
		 * @return void
		 */
		public function display_meta_box_field( $field = array(), $post, $echo = true ) {

			if( is_array($field) && !empty($field)){

				$meta_box  = '<div class="form-field form-group' . ( !empty($field['class']) ? ' ' . $field['class'] : '' ) . '">' . PHP_EOL;
				
					if( !empty($field['label']) && !empty($field['id']) ){
						
						$meta_box .= '<div style="font-weight:600;margin:15px 0;" for="' . $field['id'] . '">' . $field['label'] . '</div> ' . PHP_EOL;
					}
					
					$meta_box .= $this->display_field( $field, $post, false ) . PHP_EOL;
					
				$meta_box .= '</div>' . PHP_EOL;
				
				if( $echo === true ){
				
					echo $meta_box;
				}
				else{
					
					return $meta_box;
				}
			}
		}
		
		public function display_frontend_metaboxes( $fields, $post, $context = 'advanced' ) {

			$metaboxes = array();
			
			foreach ( $fields as $field ) {
				
				if( !isset($field['metabox']['frontend']) || $field['metabox']['frontend'] === true ){
				
					if( !isset($field['metabox']['context']) || $field['metabox']['context'] == $context ){
						
						$name = $field['metabox']['frontend'];
						
						if( !isset($metaboxes[$name]) ){
							
							$metaboxes[$name] = array(
							
								'title' 	=> $field['metabox']['title'],
								'content' 	=> $this->display_meta_box_field( $field, $post, false),
							);
						}
						else{
							
							$metaboxes[$name]['content'] .= $this->display_meta_box_field( $field, $post, false);
						}
					}
				}
			}
			
			if( !empty($metaboxes) ){ 
				
				foreach( $metaboxes as $metabox ){
					
					echo'<div class="panel panel-default">';
						
						if( !empty($metabox['title']) ){	
							
							echo'<div class="panel-heading">';
							
								echo $metabox['title'];
							
							echo'</div>';
						}
							
						echo'<div class="panel-body">';
						
							echo $metabox['content'];
						
						echo'</div>';
						
					echo'</div>';
					
				}
			}
		}

		/**
		 * Save metabox fields
		 * @param  integer $post_id Post ID
		 * @return void
		 */
		public function save_meta_boxes ( $post_id = 0 ) {
			
			if( !$post_id || isset($_POST['_inline_edit']) || isset($_GET['bulk_edit']) ) return;
			
			$post_type = get_post_type( $post_id );
			
			$fields = apply_filters( $post_type . '_custom_fields', array(), $post_type );
			
			if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

			foreach($fields as $field) {
				
				$field_id = isset($field['name']) ? $field['name'] : $field['id'];
				
				if( strpos($field_id,'[') ){
					
					$field_id = strstr($field_id,'[',true);
				}
				
				if( isset($_REQUEST[$field_id])) {
					
					update_post_meta($post_id,$field_id,$this->validate_input($_REQUEST[$field_id],$field['type']));
				} 
				elseif( empty($field['disabled']) ){
					
					update_post_meta($post_id,$field_id,'');
				}
			}
		}
	}
