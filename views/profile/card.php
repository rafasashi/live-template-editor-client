<?php 

	$ltple = LTPLE_Client::instance();

	ob_clean(); 
	
	// get site name
	
	$site_name = ucfirst(get_bloginfo('name'));
	
	// get site logo
	
	$site_logo = ( !empty($ltple->settings->options->logo_url) ? $ltple->settings->options->logo_url : $ltple->assets_url . 'images/home.png' );
	
	// get site icon
	
	$site_icon = get_site_icon_url(512,WP_CONTENT_URL .  '/favicon.jpeg');

	// get description
	
	$description = wp_trim_words(get_user_meta($ltple->profile->id, 'description', true),50,' [...]');

	if( empty($description) ){
		
		$description = 'Nothing to say';
	}
	
	// get page title
	
	$title = $ltple->profile->name;
	
	$locale = get_locale();
	$robots = 'index,follow';
	
	$canonical_url = $ltple->urls->home;
	
	$sitemap_url = trailingslashit($ltple->urls->home) . 'wp-sitemap.xml';
	$feed_url 	 = trailingslashit($ltple->urls->home) . 'feed/';
	
?>
<!DOCTYPE html>
<html>
	<head>
		
		<title><?php echo $title; ?></title>
		
		<link rel="shortcut icon" type="image/jpeg" href="<?php echo $site_icon; ?>" />
		
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.min.css">
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<meta name="subject" content="<?php echo $title; ?>" />
		<meta property="og:title" content="<?php echo $title; ?>" />
		<meta name="twitter:title" content="<?php echo $title; ?>" />
		
		<meta name="author" content="<?php echo $ltple->profile->name; ?>" />
		<meta name="creator" content="<?php echo $ltple->profile->name; ?>" />
		<meta name="owner" content="<?php echo $title; ?>" />
		
		<meta name="language" content="<?php echo $locale; ?>" />
		
		<meta name="robots" content="<?php echo $robots; ?>" />
		
		<meta name="description" content="<?php echo $description; ?>" />
		<meta name="abstract" content="<?php echo $description; ?>" />
		<meta name="summary" content="<?php echo $description; ?>" />
		<meta property="og:description" content="<?php echo $description; ?>" />
		<meta name="twitter:description" content="<?php echo $description; ?>" />
		
		<meta name="classification" content="Business" />

		<meta name="copyright" content="<?php echo $site_name; ?>" />
		<meta name="designer" content="<?php echo $site_name; ?> team" />

		<meta name="url" content="<?php echo $canonical_url; ?>" />
		<meta name="canonical" content="<?php echo $canonical_url; ?>" />
		<meta name="original-source" content="<?php echo $canonical_url; ?>" />
		<link rel="original-source" href="<?php echo $canonical_url; ?>" />
		<meta property="og:url" content="<?php echo $canonical_url; ?>" />
		<meta name="twitter:url" content="<?php echo $canonical_url; ?>" />
		
		<link rel="sitemap" href="<?php echo $sitemap_url; ?>" type="application/xml" />
		<link rel="alternate" href="<?php echo $feed_url; ?>"  type="application/rss+xml" title="RSS Feed" />
		
		<meta name="rating" content="General" />
		<meta name="directory" content="submission" />
		<meta name="coverage" content="Worldwide" />
		<meta name="distribution" content="Global" />
		<meta name="target" content="all" />
		<meta name="medium" content="blog" />
		<meta property="og:type" content="article" />
		<meta name="twitter:card" content="summary" />
				
		<style><?php echo $ltple->profile->get_card_style(); ?></style>
	
	</head>
	<body>
	<?php wp_body_open(); ?>
		<div id="wrapper">
		
		  <div id="content">
		  
			<a id="logo" href="<?php echo $ltple->urls->primary; ?>">
			
				<img src="<?php echo $site_logo; ?>">
			
			</a>		  
					  
			<div id="card">
			  <div id="front">
				<div id="arrow"><i class="fa fa-arrow-left"></i></div>
				<div id="top-pic"></div>
				<div id="avatar"></div>
				<div id="info-box">
				  <div class="info">
					<h1><?php echo $ltple->profile->name; ?></h1>
					
					<h2>
						
						<?php

							echo'<span class="fa fa-star" aria-hidden="true"></span>'; 
							
							if( $ltple->settings->is_enabled('ranking') ){
							
								echo $ltple->stars->get_count($ltple->profile->id);
							}
							
						?>
					
					</h2>
					
				  </div>
				</div>
				<div id="social-bar">
				  <a href="javascript:void" class="more-info">
					<i class="fa fa-user"></i> Flip
				  </a>
				</div>
			  </div>
			  <div id="back">
				<div class="back-info">
					<h3>About</h3>
					<p><?php echo $description; ?></p>
					<a href="<?php echo $ltple->profile->url . '/about/'; ?>">Read More</a>
				</div>
				<div id="social-bar">
				
				  <a href="javascript:void" class="more-info">
					<i class="fa fa-undo"></i>
				  </a>
				</div>
			  </div>
			</div>
			<div id="background">
			  <div id="background-image"></div>
			</div>
		  </div>
		</div>
	
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	
		<script>

			$(window).load(function(){
				
			  $('#wrapper').addClass('loaded');
			})

			$('.more-info').click(function(){
				
			  $("#card").toggleClass('flip');
			  $('#arrow').remove();
			});
			
			$('#background').click(function(){
				
			  $('#card').removeClass('flip');
			})
		
		</script>
	
	</body>
</html>

<?php exit; ?>