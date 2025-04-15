<?php

$ltple = LTPLE_Client::instance();

$layer = LTPLE_Editor::instance()->get_layer();

$layer_type = $ltple->layer->get_layer_type($layer->ID);

$user_plan 	= $ltple->plan->get_user_plan_info($ltple->user->ID);

$total_storage 	= isset($user_plan['info']['total_storage'][$layer_type->name]) ? $user_plan['info']['total_storage'][$layer_type->name] : 0;
    
get_header();		

if( !$ltple->inWidget ){
    
    include('navbar.php');
    
    if( $total_storage > 0 ){
            
        $iframe_url = add_query_arg( array(
        
            'output' => 'widget'
            
        ),$ltple->urls->current);
        
        echo'<iframe class="full-height" data-src="' . $iframe_url . '" style="width:100%;border:0;min-height:calc(100vh - 130px);overflow:hidden;"></iframe>';
    }
    else{
        
        include('editor-form.php');
    }
}
else{
    
    $layer_plan = $ltple->plan->get_layer_plan( $layer->ID, 'min' );
    
    $storage_type = get_post_type_object($layer_type->storage);

    $storage_name = !empty($storage_type->labels->singular_name) ? strtolower($storage_type->labels->singular_name) : 'project';

    $plan_usage = $ltple->plan->get_user_plan_usage( $ltple->user->ID );
    
    if( $layer->is_editable && $ltple->layer->is_editable_output($layer->output) && !$ltple->layer->is_hosted_output($layer->output) ){
        
        // get start url
        
        $quick_start_url = add_query_arg( array(
        
            'quick' 	=> '',
            
        ), remove_query_arg('output',$ltple->urls->current) );
        
        // get download button
        
        $quick_start = '';
        
        if( $layer->output == 'image' || $layer->output == 'canvas' ){
            
            $button_name ='Edit image ( without saving )';
        }
        elseif( $layer->output == 'vector' ){
            
            $button_name ='Edit vector ( without saving )';
        }
        elseif( $layer->output == 'inline-css' || $layer->output == 'external-css' ){
            
            $button_name ='Get the code ( without hosting )';				
        }
        else{
            
            $button_name = apply_filters('ltple_editor_starter_button','Launch the app',$layer);
        }
        
        $quick_start = '<a target="_parent" href="'.$quick_start_url.'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">'.$button_name.'</a>';
    }
    else{
        
        $quick_start = apply_filters('ltple_quick_start_action','',$layer);
    }

    echo '<div style="min-height:calc( 100vh - ' . ( $ltple->inWidget ? 0 : 145 ) . 'px );overflow:hidden;">';
        
        echo '<div class="col-xs-12 col-sm-12 col-lg-6" style="padding:20px;">';
            
            echo '<h3 class="pull-left">Start a new '. $storage_name .'</h3>';
            
            if( $total_storage > 0 ){
            
                echo '<a class="pull-right" target="_parent" href="' . $ltple->urls->account . '?tab=plan-details"><span class="label label-default" style="font-size:18px;"> ' . ( !empty($plan_usage[$layer_type->name]) ? $plan_usage[$layer_type->name] : 0 ) . ' / ' . $total_storage . ' </span></a>';
            }
            
            echo '<hr class="clearfix">';

            if( !$layer->is_media && ( $layer_plan['amount'] === floatval(0) || $ltple->user->remaining_days > 0 ) ){
                
                if( $ltple->plan->remaining_storage_amount($layer->ID) > 0 ){
                    
                    // get editor url
                    
                    $start_url = remove_query_arg('output',$this->parent->urls->current);			
                    
                    echo'<form target="_parent" class="col-xs-8" action="' . $start_url . '" id="savePostForm" method="post">';
                        
                        do_action('ltple_editor_start_' . $layer_type->storage);
                        
                        echo'<div class="input-group">';					
                            
                            echo'<input type="text" name="postTitle" id="postTitle" value="" class="form-control input-lg required" placeholder="'.ucfirst($storage_name).' Title">';
                            echo'<input type="hidden" name="postContent" id="postContent" value="">';
                            
                            wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );

                            echo'<input type="hidden" name="submitted" id="submitted" value="true">';
                            
                            echo'<span class="input-group-btn">';

                                echo'<input type="hidden" name="postAction" id="postAction" value="save">';
                                    
                                echo'<input formtarget="_parent" class="btn btn-lg btn-primary" type="submit" id="saveBtn" style="padding:11px 15px;height:42px;" value="Start" />';
                            
                            echo'</span>';
                            
                        echo'</div>';
                        
                    echo'</form>';
                    
                    if( !empty($quick_start) ){
                    
                        echo'<div style="font-size:18px;width:100%;display:inline-block;padding:35px 20px 20px 20px;">OR</div>';
                    }
                }
                elseif( $total_storage > 0 ){
                    
                    echo'<div class="alert alert-warning">';
                        
                        echo'You can\'t save more '.$storage_name.'s from the <b>' . $layer_type->name . '</b> gallery with the current plan. Delete an old '.$storage_name.' or upgrade to increase your storage space.';
                
                    echo'</div>';				
                }
            }
            
            if( !empty($quick_start) ){
                
                echo $quick_start;
            }

        echo'</div>';
        
        echo'<div class="col-xs-12 col-sm-12 col-lg-6" style="padding:20px;">';
            
            if( $projects = $ltple->layer->get_user_projects($ltple->user->ID,$layer_type) ){

                echo'<h3 class="pull-left">Saved '.$storage_name.'s </h3><a class="pull-right" target="_parent" href="' . $ltple->urls->dashboard . '?list=' . $layer_type->storage . '"><span class="label" style="font-size:12px;color:#cacaca;padding:10px;line-height:30px;">see all</span></a>';
                
                echo'<hr class="clearfix">';
                
                echo'<div style="height:calc( 100vh - ' . ( $ltple->inWidget ? 115 : 260 ) . 'px );overflow:auto;">';
                
                    foreach( $projects as $project ){
                        
                        echo'<div style="margin: 5px 0;display: inline-block;width: 100%;">';
                        
                            echo'<div class="col-xs-6">';
                                
                                echo $project->post_title;
                        
                            echo'</div>';
                        
                            echo'<div class="col-xs-6 text-right">';
                            
                                echo $ltple->layer->get_action_buttons($project,$layer_type,'_parent');
                                
                            echo'</div>';
                            
                        echo'</div>';
                    }
                
                echo'</div>';
            }
            
        echo'</div>';
        
    echo'</div>';
}
    
get_footer();