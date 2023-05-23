<?php

$ltple = LTPLE_Client::instance();
	
// add head
		
remove_action( 'wp_head', '_wp_render_title_tag', 1 );
		
add_action( 'wp_head', array( $ltple, 'get_header') );
	
$sitemap_url = trailingslashit($ltple->urls->home) . 'wp-sitemap.xml';
$feed_url 	 = trailingslashit($ltple->urls->home) . 'feed/';
	
$output = $ltple->inWidget ? 'widget' : 'ui';
?>
<!DOCTYPE html>	
<html <?php language_attributes(); ?> class="<?php echo apply_filters('ltple_document_classes','ltple-theme'); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="sitemap" href="<?php echo $sitemap_url; ?>" type="application/xml" />
	<link rel="alternate" href="<?php echo $feed_url; ?>"  type="application/rss+xml" title="RSS Feed" />
	<?php wp_head();?>
</head> 
<body <?php body_class('boxedlayout ltple-' . $output); ?>>

	<?php wp_body_open(); ?>

	<div id="ltple-wrapper" class="boxedcontent" style="position:absolute;z-index:auto;border:none;width:100%;top:0;left:0;right:0;display:contents !important;">
