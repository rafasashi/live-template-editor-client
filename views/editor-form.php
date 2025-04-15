<?php 

$ltple = LTPLE_Client::instance();

$layer = LTPLE_Editor::instance()->get_layer();

$action_url = add_query_arg( array(
    
    'quick' => '',
    
),$ltple->urls->current);

get_header();

echo'<div class="container-fluid" style="min-height:calc( 100vh - 50px );">';

    echo '<div class="col-xs-12 col-sm-12 col-lg-6" style="padding:20px;">';
        
        echo '<h3 class="pull-left">'.$layer->post_title.'</h3>';
        
        echo '<hr class="clearfix">';
        
        echo '<form target="_self" action="'.$action_url.'" method="post" style="width:100%;background:#FFFFFF;">';
            
            if( $fields = $ltple->layer->get_form_fields($layer->ID) ){
            
                foreach( $fields as $field ) {
                    
                    echo  $ltple->admin->display_meta_box_field($field,$layer,false); 
                }
            }
           
            echo '<div class="clearfix">';
            
                echo '<input class="btn btn-primary btn-md" type="submit" value="Start" />';
            
            echo '</div>';
            
        echo '</form>';
        
    echo '</div>';
	
echo'</div>';

get_footer();