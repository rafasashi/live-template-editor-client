<?php 

	if(isset($_SESSION['message'])){ 
	
		//output message
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}

	if( $this->user->is_affiliate ){
						
		$company_name = ucfirst(get_bloginfo('name'));
	
		$tab = 1; //accordion tabs

		// ------------- output panel --------------------
		
		echo'<div id="media_library">';

			echo'<div class="col-xs-3 col-sm-2">';
			
				echo'<ul class="nav nav-tabs tabs-left">';
					
					echo'<li class="gallery_type_title">Affiliate Program</li>';
					
					echo'<li class="active"><a href="#overview" data-toggle="tab">Overview</a></li>';
					
					echo'<li><a href="#urls" data-toggle="tab">My Referral urls</a></li>';
					
					echo'<li><a href="#material" data-toggle="tab">Marketing Material</a></li>';
					
					echo'<li><a href="#rules" data-toggle="tab">Rules & policy</a></li>';
					
					echo'<li><a href="#agreement" data-toggle="tab">Affiliate Agreement</a></li>';

				echo'</ul>';
				
			echo'</div>';

			echo'<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">';
				
				echo'<div class="tab-content">';

					//overview
					
					echo'<div class="tab-pane active" id="overview">';

						echo'<div class="bs-callout bs-callout-primary">';
						
							echo'<h4>';
							
								echo'Overview';
								
							echo'</h4>';
						
							echo'<p>';
							
								echo 'Your earnings snapshot and affiliate information';
							
							echo'</p>';	

						echo'</div>';							

						echo'<div class="tab-content row">';

							echo'<div class="col-xs-12 col-sm-4">';
							
								echo'<div class="panel panel-info text-info">';
								
									echo'<div class="panel-heading text-center">';
										echo'<h3 style="margin:0;">Clicks</h3>';	
									echo'</div>';
									
									$this->programs->get_affiliate_overview($this->user->affiliate_clicks);

								echo'</div>';	
								
								echo'<i>';
									echo'* daily unique IPs';	
								echo'</i>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-4">';
							
								echo'<div class="panel panel-warning text-warning">';
								
									echo'<div class="panel-heading text-center">';
										echo'<h3 style="margin:0;">Referrals</h3>';	
									echo'</div>';
									
									$this->programs->get_affiliate_overview($this->user->affiliate_referrals);

								echo'</div>';
								
								echo'<i>';
									echo'* new user registrations';	
								echo'</i>';										
								
							echo'</div>';

							echo'<div class="col-xs-12 col-sm-4">';
							
								echo'<div class="panel panel-success text-success">';
								
									echo'<div class="panel-heading text-center">';
										echo'<h3 style="margin:0;">Commission</h3>';	
									echo'</div>';
									
									$this->programs->get_affiliate_overview($this->user->affiliate_commission,true,'$');									
									
								echo'</div>';

								echo'<i>';
									echo'* new plan subscriptions';
								echo'</i>';					
						
							echo'</div>';	

						echo'</div>';
						
						echo'<div class="clearfix"></div>';	
						echo'<hr></hr>';							

						echo'<div class="row">';
						echo'<div class="col-xs-12">';
						
							echo'<div class=" panel panel-default" style="margin-bottom:0;">';
							
								echo'<table class="table table-striped table-hover">';
								
								echo'<tbody>';
									
									echo'<tr style="font-size:18px;font-weight:bold;">';
										
										echo'<td>Pending balance</td>';
										
										echo'<td>' . $this->programs->get_affiliate_balance($this->user->ID) . '</td>';
									echo'</tr>';
								
								echo'</tbody>';
								
								echo'</table>';
							
							echo'</div>';
							
						echo'</div>';
						echo'</div>';
						
						echo'<div class="row">';
						echo'<div class="col-xs-12">';
						
							echo'<div class="well" style="display:inline-block;width:100%;margin-top:20px;">';
								
								echo'<div class="col-xs-3">';
									
									echo'Your referral url ';

								echo'</div>';
								
								echo'<div class="col-xs-9">';
									
									echo'<input class="form-control" type="text" value="' . $this->urls->editor . '?ri=' . $this->user->refId . '" />';
								
								echo'</div>';
						
								echo'<div class="clearfix"></div>';
								
								echo'<hr></hr>';
								
								echo'<div class="col-xs-12 col-sm-7">';
								
									echo'<div class="row">';
									
										echo'<div class="col-xs-6">';
										
											echo'Sale Commission';
											
										echo'</div>';
										
										echo'<div class="col-xs-6">';
										
											echo'<b>50%</b> on the first month of any subscription';
											echo'<br>';
											echo'<b>25%</b> on any one-time fee charges';
											
										echo'</div>';	

									echo'</div>';

								echo'</div>';
								
								echo'<div class="clearfix"></div>';	
								echo'<hr></hr>';								
								
								echo'<div class="col-xs-12 col-sm-7">';
								
									echo'<div class="row">';

										echo'<div class="col-xs-6">';
										
											echo'Minimum payout';
											
										echo'</div>';
										
										echo'<div class="col-xs-6">';
										
											echo'<b>$50.00</b> <i style="font-size:11px;">( payout fee 3,4% + $0,30 )</i>';
											
										echo'</div>';											
									
									echo'</div>';

								echo'</div>';
								
								echo'<div class="clearfix"></div>';	
								echo'<hr></hr>';								
								
								echo'<div class="col-xs-12 col-sm-12">';
								
									echo'<div class="row">';

										echo'<div class="col-xs-3">';
										
											echo'Paypal Account';
											
										echo'</div>';
										
										echo'<div class="col-xs-6">';
										
											echo'<form action="' . $this->urls->current . '" method="post" class="tab-content row">';
				
												echo'<div class="row">';

													echo'<div class="col-xs-6">';				
				
														$this->admin->display_field( array(
									
															'type'				=> 'text',
															'id'				=> $this->_base . '_paypal_email',
															'placeholder' 		=> 'myemail@example.com',
															'description'		=> ''
															
														), $this->user );
							
													echo'</div>';
								
													echo'<div class="col-xs-6">';				
				
														echo'<button class="btn btn-sm btn-warning" style="width:50px;">Save</button>';
								
													echo'</div>';								
								
												echo'</div>';
								
											echo'</form>';											
											
										echo'</div>';											
									
									echo'</div>';

								echo'</div>';
								
							echo'</div>';
						
						echo'</div>';
						echo'</div>';						

					echo'</div>';
					
					// ref urls
					
					echo'<div class="tab-pane" id="urls">';
					
						echo'<div class="bs-callout bs-callout-primary">';
						
							echo'<h4>';
							
								echo'Referral ID & Urls';
								
							echo'</h4>';
						
							echo'<p>';
							
								echo 'List of urls to be used in your marketing campaigns';
							
							echo'</p>';	

						echo'</div>';							

						echo'<div class="tab-content row" style="margin:20px;">';

							echo'<div class="col-xs-12 col-sm-6">';
								
								echo'<div class="form-group">';
								
									echo'<h3>My Referral ID</h3>';
								
									echo'<input class="form-control" type="text" value="' . $this->user->refId . '" />';
								
								echo'</div>';
								
								echo'<div class="form-group">';
							
									echo'<h3>My ref link to the main page</h3>';
								
									echo'<input class="form-control" type="text" value="' . $this->urls->editor . '?ri=' . $this->user->refId . '" />';
								
								echo'</div>';
								
								echo'<div class="form-group">';
							
									echo'<h3>My ref link to the login page</h3>';
								
									echo'<input class="form-control" type="text" value="' . $this->urls->login . '?ri=' . $this->user->refId . '" />';
								
								echo'</div>';
								
								echo'<div class="form-group">';
							
									echo'<h3>My ref link to the plans</h3>';
								
									echo'<input class="form-control" type="text" value="' . $this->urls->plans . '?ri=' . $this->user->refId . '" />';
								
								echo'</div>';
								
							echo'</div>';
							
						echo'</div>';						
						
					echo'</div>'; //urls

					// material
					
					echo'<div class="tab-pane" id="material">';

						echo'<div class="tab-content row">';
						
						if( !empty($this->programs->banners['key']) ){
							
							foreach($this->programs->banners['key'] as $i => $title){
								
								$url = $this->programs->banners['value'][$i];

								echo'<div class="col-xs-12 col-sm-4">';
								
									echo'<div class="panel panel-default">';
									
										echo'<div class="panel-heading text-left">';
											echo'<b>'.$title.'</b>';	
										echo'</div>';
										
										echo'<div class="panel-body">';
										
											echo '<a href="' . $this->urls->editor . '?ri=' . $this->user->refId . '"><img src="'.$url.'"/></a>';

										echo'</div>';
										
										echo'<div class="panel-footer">';
										
											echo'<input type="text" class="form-control" value="'.htmlentities('<a href="' . $this->urls->editor . '?ri=' . $this->user->refId . '"><img src="'.$url.'"/></a>').'"/>';
										
										echo'</div>';
										
									echo'</div>';							
								
								echo'</div>';
							}
						}
						
						echo'</div>';
						
					echo'</div>'; //material
					
					// rules
					
					echo'<div class="tab-pane" id="rules">';

						echo'<div class="tab-content row">';
						
							// accordion

							echo'<div class="panel-default">';
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#rules" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Foreword';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo 'As a member of this affiliate program you are part of the team.'.PHP_EOL;
										echo '<br>';
										echo 'Anything you can possibly require to promote the product is our number one priority because we want to build human relationships with our collaborators and grow with them.'.PHP_EOL;
										echo 'Therefore we can provide you with custom banners, texts and any other marketing material you d\'like to try. Just ask.'.PHP_EOL; 
										echo '<br>';
										echo 'You can reach us everyday of the week for a question or suggestion. The door is always open.'.PHP_EOL;
																			
									echo'</div>';
								  
								echo'</div>';

								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#rules" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Commission';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';
									
										echo'<b>50% commission</b> on the first month of any subscription';
										echo'<br>';
										echo'<b>25% commission</b> on any one-time fee charges';
									
									echo'</div>';
								  
								echo'</div>';								

								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#rules" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Rules';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';
											
										echo'<ul style="margin-left:10px;">';	
											
											echo'<li>Refer to our range of services, software and tools by "<b>'.$company_name.'</b>"</li>'.PHP_EOL;
											echo'<li>Commission payout will happen <b>at the end of the month</b></li>'.PHP_EOL;
											echo'<li>The minimum payout is <b>$50</b></li>'.PHP_EOL;
											echo'<li>Please share your affiliate discount through closed channels that you own and manage</li>'.PHP_EOL;
										
										echo'</ul>';	
									
									echo'</div>';
								  
								echo'</div>';								
								
								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#rules" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Policy';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'We may cancel your application if we determine that your channel is unsuitable for the Program, including if it:'.PHP_EOL;
									
										echo'<br>';
									
										echo'<ul style="margin-left:10px;">';	
										
											//echo'<li>Promotes Pornographic Material</li>'.PHP_EOL;
											echo'<li>Promotes violence'.PHP_EOL;
											echo'<li>Promotes discrimination based on race, sex, religion, nationality, disability, sexual orientation, or age'.PHP_EOL;
											echo'<li>Promotes illegal activities '.PHP_EOL;
											echo'<li>Incorporates any materials which infringe or assist others to infringe on any copyright, trademark or other intellectual property rights or to violate the law '.PHP_EOL;
											echo'<li>Is otherwise in any way unlawful, harmful, threatening, defamatory, obscene, harassing, or racially, ethnically or otherwise objectionable to us in our sole discretion. '.PHP_EOL;
											echo'<li>You may not create or design your website or any other website that you operate, explicitly or implied in a manner which leads customers to believe you are "'.$company_name.'" or any other affiliated business.'.PHP_EOL;
												
										echo'</ul>';
											
									echo'</div>';
								  
								echo'</div>';								
								
							echo'</div>';					
						
						echo'</div>';
					
					echo'</div>'; //rules
					
					// agreement
					
					echo'<div class="tab-pane" id="agreement">';
						
						echo'<div class="tab-content row">';
						
							// accordion

							echo'<div class="panel-default">';
								
								++$tab;

								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Agreement';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'PLEASE READ THE ENTIRE AGREEMENT.'.PHP_EOL;
										echo '<br><br>';
										echo'YOU MAY PRINT THIS PAGE FOR YOUR RECORDS.'.PHP_EOL;
										echo '<br><br>';
										echo'THIS IS A LEGAL AGREEMENT BETWEEN YOU AND ' . strtoupper($company_name) . PHP_EOL;
										echo '<br><br>';

									echo'</div>';
								  
								echo'</div>';
								
								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Description';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'The Affiliate Program permits you to monetize your website, social media user-generated content, or online software application (referred to here as your "Channels"), by placing links to '.$company_name.' website on your Channels.'.PHP_EOL;
										echo '<br><br>';
										echo'The links must properly use the special "referral" link formats we provide and comply with this Agreement.'.PHP_EOL;
										echo '<br><br>';
										echo'When our customers click through your "referral Link" to purchase an item sold or services offered on '.$company_name.' (a "Product") or take other actions, you can receive program fees for qualifying purchases, as further described in the Rules & Policy section of the program.'.PHP_EOL;
										echo '<br><br>';
										echo'In order to facilitate your advertisement of Products, we may make available to you data, images, text, link formats, widgets, links, marketing content, and other linking tools, application program interfaces, and other information in connection with the Affiliate Program ("Content").'.PHP_EOL;							

									echo'</div>';
								  
								echo'</div>';
								
								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Customers';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'Our customers are not, by virtue of your participation in the Affiliate Program, your customers.' . PHP_EOL;
										echo '<br><br>';
										echo'As between you and us, all pricing, terms of sale, rules, policies, and operating procedures concerning customer orders, customer service, and product sales set forth on '.$company_name.' will apply to those customers, and we may change them at any time.' . PHP_EOL;
										echo '<br><br>';
										echo'You will not handle or address any contacts with any of our customers, and, if contacted by any of our customers for a matter relating to interaction with '.$company_name.' service, you will state that those customers must follow contact directions on '.$company_name.' to address customer service issues.' . PHP_EOL;						

									echo'</div>';
								  
								echo'</div>';
																
								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Warranties';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'You represent, warrant, and covenant that :'.PHP_EOL;
										
										echo'<ul style="margin-left:10px;">';	
										
											echo'<li>you will participate in the Affiliate Program and create, maintain, and operate your Channels in accordance with this Agreement';
											echo'<li>neither your participation in the Program nor your creation, maintenance, or operation of your Channels will violate any applicable laws, ordinances, rules, regulations, orders, licenses, permits, industry standards, self-regulatory rules, judgments, decisions, or other requirements of any applicable governmental authority (including all such rules governing communications and marketing)';
											echo'<li>you are lawfully able to enter into contracts (e.g. you are not a minor)';
											echo'<li>the information you provide in connection with the Affiliate Program is accurate and complete at all times';
											
										echo'</ul>';	
											
										echo '<br>';
											
										echo'We do not make any representation, warranty, or covenant regarding the amount of traffic or fees you can expect at any time in connection with the Affiliate Program, and we will not be liable for any actions you undertake based on your expectations.'.PHP_EOL;
						
									echo'</div>';
								  
								echo'</div>';
								
								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Identifying Yourself as an Affiliate';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'You must clearly state somewhere on your Channels that you are a participant in the '.$company_name.' Affiliate Program, an affiliate advertising program designed to provide a means to earn fees by linking to '.$company_name;										
										
									echo'</div>';
								  
								echo'</div>';
								
								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Term and Termination';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'The term of this Agreement will begin upon your registration on or use of the Affiliate Site and will end when terminated by either you or us. ';
										echo '<br><br>';
										echo'Either you or we may terminate this Agreement at any time, with or without cause, by giving the other party written notice of termination.';
										echo '<br><br>';
										echo'Upon any termination of this Agreement, all rights and obligations of the parties will be extinguished';
										echo '<br><br>';
										echo'No termination of this Agreement will relieve either party for any liability for any breach of, or liability accruing under, this Agreement prior to termination.';						
										

									echo'</div>';
								  
								echo'</div>';
								
								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Disclaimers';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'THE AFFILIATE PROGRAM, THE '.strtoupper($company_name).' SITE, ANY PRODUCTS AND SERVICES OFFERED ON THE '.strtoupper($company_name).' SITE, ';
										echo'ANY SPECIAL LINKS, LINK FORMATS, CONTENT, THE PRODUCT ADVERTISING API, DATA FEED, PRODUCT ADVERTISING CONTENT, OUR DOMAIN NAMES, TRADEMARKS AND LOGOS (INCLUDING THE '.strtoupper($company_name).' MARKS), AND ALL TECHNOLOGY, SOFTWARE, FUNCTIONS, MATERIALS, DATA, IMAGES, TEXT, AND OTHER INFORMATION AND CONTENT PROVIDED OR USED BY OR ON BEHALF OF US OR OUR AFFILIATES OR LICENSORS IN CONNECTION WITH THE AFFILIATE PROGRAM (COLLECTIVELY THE "SERVICE OFFERINGS") ARE PROVIDED "AS IS" AND "AS AVAILABLE". ';
										echo'NEITHER WE NOR ANY OF OUR AFFILIATES OR LICENSORS MAKE ANY REPRESENTATION OR WARRANTY OF ANY KIND, WHETHER EXPRESS, IMPLIED, STATUTORY, OR OTHERWISE, WITH RESPECT TO THE SERVICE OFFERINGS. EXCEPT TO THE EXTENT PROHIBITED BY APPLICABLE LAW, WE AND OUR AFFILIATES AND LICENSORS DISCLAIM ALL WARRANTIES WITH RESPECT TO THE SERVICE OFFERINGS, INCLUDING ANY IMPLIED WARRANTIES OF MERCHANTABILITY, SATISFACTORY QUALITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT, AND ANY WARRANTIES ARISING OUT OF ANY COURSE OF DEALING, PERFORMANCE, OR TRADE USAGE. ';
										echo'WE MAY DISCONTINUE ANY SERVICE OFFERING, OR MAY CHANGE THE NATURE, FEATURES, FUNCTIONS, SCOPE, OR OPERATION OF ANY SERVICE OFFERING, AT ANY TIME AND FROM TIME TO TIME. NEITHER WE NOR ANY OF OUR AFFILIATES OR LICENSORS WARRANT THAT THE SERVICE OFFERINGS WILL CONTINUE TO BE PROVIDED, WILL FUNCTION AS DESCRIBED, CONSISTENTLY OR IN ANY PARTICULAR MANNER, OR WILL BE UNINTERRUPTED, ACCURATE, ERROR FREE, OR FREE OF HARMFUL COMPONENTS. NEITHER WE NOR ANY OF OUR AFFILIATES OR LICENSORS WILL BE RESPONSIBLE FOR ';
										echo'(A) ANY ERRORS, INACCURACIES, OR SERVICE INTERRUPTIONS, INCLUDING POWER OUTAGES OR SYSTEM FAILURES OR ';
										echo'(B) ANY UNAUTHORIZED ACCESS TO OR ALTERATION OF, OR DELETION, DESTRUCTION, DAMAGE, OR LOSS OF, YOUR CHANNELS OR ANY DATA, IMAGES, TEXT, OR OTHER INFORMATION OR CONTENT. ';
										echo'NO ADVICE OR INFORMATION OBTAINED BY YOU FROM US OR FROM ANY OTHER PERSON OR ENTITY OR THROUGH THE AFFILIATE PROGRAM, CONTENT, THE PRODUCT ADVERTISING API, DATA FEED, PRODUCT ADVERTISING CONTENT, PROGRAM POLICIES, THE AFFILIATE SITE, OR ANY '.strtoupper($company_name).' SITE WILL CREATE ANY WARRANTY NOT EXPRESSLY STATED IN THIS AGREEMENT. ';
										echo'FURTHER, NEITHER WE NOR ANY OF OUR AFFILIATES OR LICENSORS WILL BE RESPONSIBLE FOR ANY COMPENSATION, REIMBURSEMENT, OR DAMAGES ARISING IN CONNECTION WITH (X) ANY LOSS OF PROSPECTIVE PROFITS OR REVENUE, ANTICIPATED SALES, GOODWILL, OR OTHER BENEFITS, (Y) ANY INVESTMENTS, EXPENDITURES, OR COMMITMENTS BY YOU IN CONNECTION WITH YOUR PARTICIPATION IN THE AFFILIATE PROGRAM, OR (Z) ANY TERMINATION OR SUSPENSION OF YOUR PARTICIPATION IN THE AFFILIATE PROGRAM.';								

									echo'</div>';
								  
								echo'</div>';	
						
								/*
								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Limitations on Liability';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'NEITHER WE NOR ANY OF OUR AFFILIATES OR LICENSORS WILL BE LIABLE FOR INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR EXEMPLARY DAMAGES (INCLUDING ANY LOSS OF REVENUE, PROFITS, GOODWILL, USE, OR DATA) ARISING IN CONNECTION WITH THE SERVICE OFFERINGS, EVEN IF WE HAVE BEEN ADVISED OF THE POSSIBILITY OF THOSE DAMAGES. ';
										echo'FURTHER, OUR AGGREGATE LIABILITY ARISING IN CONNECTION WITH THE SERVICE OFFERINGS WILL NOT EXCEED THE TOTAL FEES PAID OR PAYABLE TO YOU UNDER THIS AGREEMENT IN THE TWELVE MONTHS IMMEDIATELY PRECEDING THE DATE ON WHICH THE EVENT GIVING RISE TO THE MOST RECENT CLAIM OF LIABILITY OCCURRED. ';
										echo'YOU HEREBY WAIVE ANY RIGHT OR REMEDY IN EQUITY, INCLUDING THE RIGHT TO SEEK INJUNCTIVE OR OTHER EQUITABLE RELIEF IN CONNECTION WITH THIS AGREEMENT.	';									

									echo'</div>';
								  
								echo'</div>';

								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Indemnification';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'WE WILL HAVE NO LIABILITY FOR ANY MATTER DIRECTLY OR INDIRECTLY RELATING TO THE CREATION, MAINTENANCE, OR OPERATION OF YOUR CHANNELS OR YOUR VIOLATION OF THIS AGREEMENT (INCLUDING ANY PROGRAM POLICY), AND YOU AGREE TO DEFEND, INDEMNIFY, AND HOLD US, OUR AFFILIATES AND LICENSORS, AND OUR AND THEIR RESPECTIVE EMPLOYEES, OFFICERS, DIRECTORS, AND REPRESENTATIVES, HARMLESS FROM AND AGAINST ALL CLAIMS, DAMAGES, LOSSES, LIABILITIES, COSTS, AND EXPENSES (INCLUDING ATTORNEYS FEES) RELATING TO ';
										echo'(A) your Channels OR ANY MATERIALS THAT APPEAR ON YOUR CHANNELS, INCLUDING THE COMBINATION OF YOUR CHANNELS OR THOSE MATERIALS WITH OTHER APPLICATIONS, CONTENT, OR PROCESSES, ';
										echo'(B) THE USE, DEVELOPMENT, DESIGN, MANUFACTURE, PRODUCTION, ADVERTISING, PROMOTION, OR MARKETING OF YOUR CHANNELS OR ANY MATERIALS THAT APPEAR ON OR WITHIN YOUR CHANNELS, ';
										echo'(C) YOUR USE OF ANY CONTENT, WHETHER OR NOT SUCH USE IS AUTHORIZED BY OR VIOLATES THIS AGREEMENT, ANY OPERATIONAL DOCUMENTATION, OR APPLICABLE LAW, ';
										echo'(D) YOUR VIOLATION OF ANY TERM OR CONDITION OF THIS AGREEMENT (INCLUDING ANY PROGRAM POLICY), OR ';
										echo'(E) YOUR OR YOUR EMPLOYEES OR CONTRACTORS NEGLIGENCE OR WILLFUL MISCONDUCT.';										

									echo'</div>';
								  
								echo'</div>';

								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Disputes';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'Any dispute relating in any way to the Affiliate Program or this Agreement will be resolved by binding arbitration, rather than in court, except that you may assert claims in small claims court if your claims qualify. ';
										echo'<br><br>';
										echo'We each agree that any dispute resolution proceedings will be conducted only on an individual basis and not in a class, consolidated, or representative action. If for any reason a claim proceeds in court rather than in arbitration, we each waive any right to a jury trial. ';
										echo'<br><br>';
										echo'We also both agree that you or we may bring suit in court to enjoin infringement or other misuse of intellectual property rights.';
										echo'<br><br>';	
										echo'Notwithstanding anything to the contrary in this Agreement, we may seek injunctive or other relief in any state, federal, or national court of competent jurisdiction for any actual or alleged infringement of our or any other person or entity\'s intellectual property or proprietary rights. ';
										echo'<br><br>';
										echo'You further acknowledge and agree that our rights in the Content are of a special, unique, extraordinary character, giving them peculiar value, the loss of which cannot be readily estimated or adequately compensated for in monetary damages.		';								

									echo'</div>';
								  
								echo'</div>';
								*/

								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Additional Provisions';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'By accepting this Agreement, you hereby consent to us: ';
										echo'<br><br>';
										echo'(a) sending you emails relating to the Affiliate Program from time to time,'; 
										echo'<br>';
										echo'(b) monitoring, recording, using, and disclosing information about your Channels and users of your Channels that we obtain in connection with your display of Special Links and Content (for example, that a particular '.$company_name.' customer clicked through a Special Link from your Channels before buying a Product on '.$company_name.')';
										echo'<br>';
										echo'(c) reviewing, monitoring, crawling, and otherwise investigating your Channels to verify compliance with this Agreement, and ';
										echo'<br>';
										echo'(d) using, reproducing, distributing, and displaying your implementation of Content displayed on your Channels as examples of best practices in our educational materials.';
										echo'<br><br>';
										
										echo'You may not assign this Agreement, by operation of law or otherwise, without our express prior written approval. Subject to that restriction, this Agreement will be binding on, inure to the benefit of, and be enforceable against the parties and their respective successors and assigns.';
										echo'<br><br>';
										echo'This Agreement incorporates, and you agree to comply with, the most up-to-date version of all policies, appendices, specifications, guidelines, schedules, and other rules referenced in this Agreement or accessible on '.$company_name.', including any updates of the Program Policies from time to time. ';
										echo'<br><br>';										
										echo'In the event of any conflict between this Agreement and any Program Policy, this Agreement will control. This Agreement (including the Program Policies) is the entire agreement between you and us regarding the Affiliate Program and supersedes all prior agreements and discussions.';
										echo'<br><br>';
										echo'All non-public information provided by us in connection with this Agreement or the Affiliate Program is considered confidential information, and you will maintain the same in strict confidence and not disclose the same to any third party (other than your affiliates) or use the same for any purpose other than your performance under this Agreement, which restriction will be in addition to the terms of any confidentiality or non-disclosure agreement between the parties.';
										echo'<br><br>';
										echo'You and we are independent contractors, and nothing in this Agreement will create any partnership, joint venture, agency, franchise, sales representative, or employment relationship between you and us or our respective affiliates. You will have no authority to make or accept any offers or representations on our or our affiliates’ behalf. You will not make any statement, whether on your Channels or otherwise, that contradicts or may contradict anything in this paragraph. If you authorize, assist, encourage, or facilitate another person or entity to take any action related to the subject matter of this Agreement, you will be deemed to have taken the action yourself.	';									

									echo'</div>';
								  
								echo'</div>';						
								
								++$tab;
								
								echo'<div style="height:60px;border-bottom:1px solid #DDDDDD;" class="panel-heading" role="tab" id="heading_'.$tab.'">';
									
									echo'<button style="color:rgb(138, 206, 236);background:none;text-align:left;font-size:21px;width: 100%;padding:8px;border:none;" role="button" data-toggle="collapse" data-parent="#agreement" data-target="#collapse_'.$tab.'" aria-expanded="true" aria-controls="collapse_'.$tab.'">';
									  
										echo'Modification';
									
									echo'</button>';
								
								echo'</div>';
								
								echo'<div id="collapse_'.$tab.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading_'.$tab.'">';
								 
									echo'<div class="panel-body">';

										echo'We reserve the right to modify any of the terms and conditions contained in this Agreement (including those in any Program Policy) at any time and in our sole discretion by posting a change notice, revised Agreement, or revised Program Policy on the Affiliate Site or by sending notice of such modification to you by email to the primary email address then-currently Affiliated with your Affiliate account (any such change by email will be effective on the date specified in such email but will in no event be less than two business days after the date the email is sent). ';
										echo'<br><br>';	
										echo'YOUR CONTINUED PARTICIPATION IN THE AFFILIATE PROGRAM FOLLOWING THE EFFECTIVE DATE OF SUCH NOTICE WILL CONSTITUTE YOUR ACCEPTANCE OF THE MODIFICATIONS. IF ANY MODIFICATION IS UNACCEPTABLE TO YOU, YOUR ONLY RECOURSE IS TO TERMINATE THIS AGREEMENT	';									

									echo'</div>';
								  
								echo'</div>';								
								
							echo'</div>';					
						
						echo'</div>';
					
					echo'</div>'; //agreement

				echo'</div>';
				
			echo'</div>';	

		echo'</div>';
	}
	else{
		
		echo '<div class="alert alert-warning">';
		
			echo 'You need to be a member of the Affiliate Program to access this area. Please contact us.';
		
		echo '</div>';
	}
	
	?>
	
	<script>

		;(function($){		
			
			$(document).ready(function(){
				
				
			});
			
		})(jQuery);

	</script>