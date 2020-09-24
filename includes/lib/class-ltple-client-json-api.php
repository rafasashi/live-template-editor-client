<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Json_API {
	
	var $parent;

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
	}

	public function get_table( $api_url, $fields=array(), $trash=false, $export=true, $search=true, $toggle=true, $columns=true, $header=true, $pagination=true, $form=true, $toolbar = 'toolbar', $card=false, $itemHeight=235, $fixedHeight=true, $echo=true ){
		
		$show_toolbar = ( ( $search || $export || $toggle || $columns ) ? true : false );
		
		$responsive = ( $card ? false : true );
		
		//$pagination = false;
		
		$table = '<style>';
			
			if(!$show_toolbar){
				
				$table .= '#'.$toolbar.'{display:none;}';
			}
			
			$table .= '
			
			#table, .fixed-table-loading {
				
				background-color:#fbfbfb !important;
				border:none !important;
			}
			
			tbody {
				
				overflow-y: auto;
				overflow-x: hidden;
				display:block;
			}
			
			.bs-bars, .fixed-table-footer, .no-records-found {
				
				display:none;
			}
			
			.float-right{
			    float:right;
			}
			
			.float-left{
			    float:left;
			}
			';
			
			if( $pagination === 'scroll' ){
				
				$table .= '
				
				.bootstrap-table{
					
					position: relative;
					height:auto;					
				}
				
				';
			}
			elseif( $pagination === true ){

				$table .= '
				
				.bootstrap-table{
					
					position: relative;
					height: ' . ( $card === true ? 'calc( 100vh - 130px )' : 'auto' ).';					
				}
			
				.fixed-table-pagination{
					
					padding: 0px 15px;
					border-top: 1px solid #ddd;
					border-bottom: none;
					background: #fbfbfb;
					background-image: -webkit-linear-gradient(#f2f2f2,#fbfbfb);
					background-image: -o-linear-gradient(#f2f2f2,#fbfbfb);
					background-image: -moz-linear-gradient(#f2f2f2,#fbfbfb);
					background-image: linear-gradient(#f2f2f2,#fbfbfb);
					min-height: 54px;
					right: 0;
					left: 0;
					position: ' . ( $card === true ? 'absolute' : 'relative' ).';
				}
				
				.pagination-detail {
					
					position:absolute;
					left: 10px;
				}
				
				.pagination-info {
					
					display:none;
				}

				.page-list .btn {
					
					padding: 5px 10px;
				}
				
				';
			}

			if( $card !== false ){
				
				if( $pagination === 'scroll' ){
				
					$table .= 'tbody {
						
						height:calc( 100vh - 140px);
					}';
				}
				else{
						
					$table .= 'tbody {
						
						height:calc( 100vh - 240px);
					}';					
				}

				$table .= 'tr {
					
					float: left;
					margin: 0;
					padding: 0;
					border-radius: 0;
					border: none;
					height: auto;
					min-height: '.$itemHeight.'px;
					width: 100%;
					overflow: hidden;
					background-color: transparent !important;
					box-shadow: none;
					position:relative;		
					display:inline-block !important;
				}';
				
				$table .= '@media (min-width: 768px) {';

					if( $pagination === 'scroll' ){
					
						$table .= 'tbody {
							
							height:calc( 100vh - ' . ( $this->parent->inWidget ?  45 : 140 ) . 'px);
						}';
					}
					else{
						
						$table .= '#content .nav{';
							
							$table .='padding-right:250px !important;';
							
						$table .='}';
							
						$table .= 'tbody {
							
							height:calc( 100vh - ' . ( $this->parent->inWidget ?  100 : 190 ) . 'px);				
						}';					
					}
					
					$table .= 'tr {';
					
						if( $card === true || $card == 2 || $card == 4 ){
							
							$table .= 'width: 50%; /*sm-6*/';
						}
						elseif( $card == 1 ){
							
							$table .= 'width: 100%; /*lg-12*/';
						}
						
					$table .= '}';
					
				$table .= '}';
				
				$table .= '@media (min-width: 992px) {';
					
					$table .= 'tr {';
						
						if( $card === true || $card == 4 ){
						
							$table .= 'width: 33.33333333%; /*md-4*/';
						
						}
						elseif( $card == 2 ){
						
							$table .= 'width: 50%; /*md-6*/';
						
						}
						elseif( $card == 1 ){
							
							$table .= 'width: 100%; /*lg-12*/';
						}
						
					$table .= '}';
					
				$table .= '}';
				
				$table .= '@media (min-width: 1200px) {';
					
					$table .= 'tr {';
						
						if( $card === true || $card == 4 ){
						
							$table .= 'width: 33.33333333%; /*lg-4*/';
						}
						elseif( $card == 2 ){
						
							$table .= 'width: 50%; /*md-6*/';
						
						}
						elseif( $card == 1 ){
							
							$table .= 'width: 100%; /*lg-12*/';
						}
						
					$table .= '}';
					
				$table .= '}';

				$table .= 'td {
					
					border:none !important;
					left: 0;
					right: 0;
					top: 0;
					bottom: 0;
					position: absolute;
					min-height: ' . ( $itemHeight - 10 ) . 'px;
					
				}';
			
				$table .= '.card-view .title {
					
					display:none !important;
					
				}';
			
			}
			else{
				
				$table .= '
				
				thead, tbody tr {
					
					display:table;
					width:100%;
					table-layout:fixed;/* even columns width , fix width of table too*/
				}
				
				td {
					
					overflow: hidden;
				}
				
				';
				
				if( $header === false && $fixedHeight === true ){
					
					$table .= '
					
					thead {
						width: calc( 100% - 6px );
					}					
					
					tbody {
						
						height:calc( 100vh - 100px);
					}
					
					@media (min-width: 768px) {
						
						tbody {
							
							height:calc( 100vh - 230px);				
						}
					}';
				}
			}
			
		$table .= '</style>';
		
		if( $pagination === 'scroll' ){
			
			// infinit scrolling
			
			$url = parse_url($api_url);
			
			$args = array();
			
			if( !empty($url['query']) ){
				
				parse_str(html_entity_decode($url['query']),$args);
			}
			
			$args['page'] = '[JS_PAGE_VAR]';
			
			$json = json_encode($args);
			
			$json = str_replace('"[JS_PAGE_VAR]"','page',$json);
			
			$table .=  "
			<script>
			;(function($){
				
				$(document).ready(function(){

					var page 	= 2;
					var loading = false;
					var margin	= 300;

					$('#table tbody').scroll(function() {

						if ( $('#table tbody tr').length < 100 || loading == true || loading == 'stopped' ) return;
						
						if( $('#table tbody').scrollTop() + $('#table tbody').innerHeight() + margin >= $('#table tbody').prop('scrollHeight') ) {
							
							loading = true;
							
							$.ajax({
								
								type 		: 'GET',
								url  		: '" . $url['scheme'] . "://" . $url['host'] . $url['path'] . "',
								data		: ".$json.",
								beforeSend	: function() {

									$('#table tbody').append('<tr id=\"tableLoader\"><td style=\"background-repeat:no-repeat;background-position:center center;background-image:url(" . $this->parent->server->url . "/c/p/live-template-editor-server/assets/loader.gif);\"></td></tr>');
								},
								success: function(data) {
									
									++page;
									
									$('#tableLoader').remove();
									
									if( data.length > 0 ){
									
										$.each(data, function(i,item) {
											
											$('#table tbody').append('<tr><td>' + item.item + '</td></tr>');
										});
										
										$('#table').trigger('page-change.bs.table');
									}
									
									if( data.length < 100 ){
										
										loading = 'stopped';
									}
									else{
										
										loading = false;
									}
								},
								complete: function(data){
									
									
								}
							});
						}
					});
				});
				
			})(jQuery);
			
			</script>
			";
		}
		
		if($form){
		
			$table .=  '<form id="tableForm" action="' . $this->parent->urls->current . '" method="post">';
		}
	
		$table .=  '<div id="'.$toolbar.'" class="btn-group">';
			
			/*
			$table .=  '<button id="add" type="button" class="btn btn-default">';
				$table .=  '<i class="glyphicon glyphicon-plus"></i>';
			$table .=  '</button>';
			
			$table .=  '<button id="like" type="button" class="btn btn-default">';
				$table .=  '<i class="glyphicon glyphicon-heart"></i>';
			$table .=  '</button>';
			*/
			
			if($trash){
			
				$table .=  '<button id="trash" type="button" class="btn btn-default">';
					$table .=  '<i class="glyphicon glyphicon-trash"></i>';
				$table .=  '</button>';
			}
			
			if($export){
			
				$table .=  '<button id="export" class="btn btn-default">';
				
					$table .=  '<i class="glyphicon glyphicon-export"></i>';
					
				$table .=  '</button>';
			}
			
		$table .=  '</div>';
		
		$table .=  '<table id="table" class="table table-striped" style="border:none;background:transparent;" ';
			
			$table .=  'data-toggle="table" ';
			//$table .=  'data-height="400" ';
			$table .=  'data-url="' . $api_url . '" ';
			
			if( $pagination === 'scroll' ){
				
				$table 	.=  'data-pagination="false" ';
				$table	.=  'data-side-pagination="server" ';
				$table 	.=  'data-page-size="100" ';
				$table 	.=  'data-show-refresh="false" ';
				$table 	.=  'data-filter-control="false" ';
				$table 	.=  'data-sortable="false" ';  
			}
			else{
				
				$table 	.=  'data-pagination="'.( $pagination ? 'true' : 'false' ).'" ';
				//$table .=  'data-pagination-v-align="both" ';
				$table 	.=  'data-page-size="20" ';
				$table 	.=  'data-page-list="[20, 50, 100, 200, 500]" ';	
				$table 	.=  'data-show-refresh="true" ';
				$table 	.=  'data-filter-control="true" ';
				$table 	.=  'data-sortable="true" ';  
			}
			
			$table .=  'data-search="'.( $search ? 'true' : 'false' ).'" ';
			$table .=  'data-show-header="'.( $header ? 'true' : 'false' ).'" ';
			$table .=  'data-show-toggle="'.( $toggle ? 'true' : 'false' ).'" ';
			$table .=  'data-show-columns="'.( $columns ? 'true' : 'false' ).'" ';
			$table .=  'data-show-export="'.( $export ? 'true' : 'false' ).'" ';
			$table .=  'data-buttons-class="primary" ';
			$table .=  'data-card-view="'.( $card ? 'true' : 'false' ).'" ';
			$table .=  'data-mobile-responsive="'.( $responsive ? 'true' : 'false' ).'" ';
			//$table .=  'data-sort-order="desc" ';   
			//$table .=  'data-sort-name="description" ';
			$table .=  ( $show_toolbar ? 'data-toolbar="#'.$toolbar.'" ' : '' );
		
		$table .=  '>';
		
			$table .=  '<thead>';
			$table .=  '<tr>';
			
				foreach($fields as $field){
					
					$table .=  '<th ';
					
						foreach($field as $key => $value){
							
							if( $key!= 'content' ){
								
								$table .=  'data-'.$key.'="'.$value.'" ';
							}
						}
						
					$table .=  '>'.(!empty($field['content']) ? $field['content'] : '').'</th>';				
				}

			$table .=  '</tr>';
			$table .=  '</thead>';
			
		$table .=  '</table>';

		if($form){	
		
			$table .=  '</form>';
		}
		
		if($echo){
			
			echo $table;
		}
		else{
			
			return $table;
		}
	}
	
	/**
	 * Main LTPLE_Client_Json_API Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Json_API is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Json_API instance
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