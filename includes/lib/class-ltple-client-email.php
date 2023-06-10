<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Email {
	
	var $parent;
	var $invitationForm;
	var $invitationMessage;
	var $imported;
	var $dnsList = array();
	var $maxRequests = 100;
	var $notification_settings = null;	
	
	/**
	 * Constructor function
	 */
	 
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		$this->parent->register_post_type( 'email-invitation', __( 'User Invitations', 'live-template-editor-client' ), __( 'User Invitation', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> false,
			'show_in_menu'		 	=> 'email-invitation',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail','page-attributes' ),
			'supports' 				=> array( 'title', 'editor', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));

		// setup phpmailer
		
		add_action( 'phpmailer_init', 	function( \PHPMailer\PHPMailer\PHPMailer $phpmailer ) {
			
			$key_name = "key1";
			$urlparts = parse_url(site_url());		
			
			$phpmailer->SMTPOptions = array(
				'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			));

			$phpmailer->DKIM_domain 	= $urlparts ['host'];
			$phpmailer->DKIM_private 	= WP_CONTENT_DIR . "/keys/dkim_" . $key_name . ".ppk";
			$phpmailer->DKIM_selector 	= $key_name;
			$phpmailer->DKIM_passphrase = "";
			$phpmailer->DKIM_identifier = $phpmailer->From;

			$phpmailer->IsSMTP();
		});
		
		// Custom default email address
		
		add_filter('wp_mail_from', function($old){
			
			$urlparts 	= parse_url(site_url());
			$domain 	= $urlparts ['host'];
			
			return 'please-reply@'.$domain;
		});
		
		// newsletter
		
		add_action('ltple_newsletter_campaign_triggers', function($triggers){
			
			unset($triggers['user_register']);
			
			$triggers['ltple_first_log_ever'] = 'User Registration';

			return $triggers;
		});

		add_action('ltple_newsletter_generator_logo_url', function($url){
			
			return $this->parent->settings->options->logo_url;
			
		},10,3);		
		
		add_filter('ltple_newsletter_generator_post_types',function($post_types){
			
			$post_types['cb-default-layer'] = 'Templates';
			
			return $post_types;
		});
		
		add_filter('ltple_newsletter_generator_color_1',function($color){
			
			return $this->parent->settings->mainColor;
		});
		
		add_filter('ltple_newsletter_generator_background_1',function($color){
			
			return $this->parent->settings->navbarColor;
		});
		
		add_filter('ltple_newsletter_generator_text_1',function($color){
			
			return $color;
		});	
		
		add_action('ltple_newsletter_subscription',function($user_id){
		
			update_user_meta($user_id, 'ltple__last_seen',time());
		});
		
		add_filter('ltple_loaded', array( $this, 'init_email' ));
		
		add_action('ltple_users_bulk_imported', array( $this, 'schedule_invitations' ));
		
		add_action('ltple_newsletter_list_meta_query',function($meta_query){
			
			$meta_query[] = array(
				
				'key' 		=> 'ltple__last_seen',
				'compare'	=> 'EXISTS', 
			);
			
			return $meta_query;	
			
		},10,1);
	}
	
	public function send_model($model_id,$user){
		
		if( class_exists('Live_Template_Editor_Newsletter') ){
		
			Live_Template_Editor_Newsletter::instance()->send_model($model_id,$user);
		}
	}
	
	public function is_email($email){
		
		if( $email ){
			
			//core php filter
			
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		}
		
		if( $email ){
			
			//core wp filter
			
			$email = is_email($email);
		}	

		if( $email && 1==2 ){
			
			//filter alias
			
			$email = ( strpos($email,'+') !== false ? false : $email );
		}	

		if( $email ){
			
			//filter disposable email
			
			list(,$domain) = explode('@',$email);
			
			$disposables = array('0815.ru','0815.ru0clickemail.com','0815.ry','0815.su','0845.ru','0clickemail.com','0-mail.com','0wnd.net','0wnd.org','10mail.com','10mail.org','10minut.com.pl','10minutemail.cf','10minutemail.co.za','10minutemail.com','10minutemail.de','10minutemail.ga','10minutemail.gq','10minutemail.ml','10minutemail.net','10minutesmail.com','10x9.com','123-m.com','126.com','12houremail.com','12minutemail.com','12minutemail.net','139.com','163.com','1ce.us','1chuan.com','1fsdfdsfsdf.tk','1mail.ml','1pad.de','1zhuan.com','20mail.it','20minutemail.com','21cn.com','24hourmail.com','2fdgdfgdfgdf.tk','2prong.com','30minutemail.com','33mail.com','3d-painting.com','3mail.ga','3trtretgfrfe.tk','420blaze.it','4gfdsgfdgfd.tk','4mail.cf','4mail.ga','4warding.com','4warding.net','4warding.org','5ghgfhfghfgh.tk','5mail.cf','5mail.ga','60minutemail.com','675hosting.com','675hosting.net','675hosting.org','6hjgjhgkilkj.tk','6ip.us','6mail.cf','6mail.ga','6mail.ml','6paq.com','6url.com','75hosting.com','75hosting.net','75hosting.org','7days-printing.com','7mail.ga','7mail.ml','7tags.com','8127ep.com','8chan.co','8mail.cf','8mail.ga','8mail.ml','99experts.com','9mail.cf','9ox.net','a.mailcker.com','a.vztc.com','a45.in','a-bc.net','abyssmail.com','afrobacon.com','ag.us.to','agedmail.com','ajaxapp.net','akapost.com','akerd.com','aktiefmail.nl','alivance.com','amail4.me','ama-trade.de','ama-trans.de','amilegit.com','amiri.net','amiriindustries.com','anappthat.com','ano-mail.net','anonbox.net','anon-mail.de','anonmails.de','anonymail.dk','anonymbox.com','anonymousmail.org','anonymousspeech.com','antichef.com','antichef.net','antireg.com','antireg.ru','antispam.de','antispam24.de','antispammail.de','armyspy.com','artman-conception.com','asdasd.nl','asdasd.ru','atvclub.msk.ru','auti.st','avpa.nl','azmeil.tk','b2cmail.de','baxomale.ht.cx','beddly.com','beefmilk.com','big1.us','bigprofessor.so','bigstring.com','binkmail.com','bio-muesli.info','bio-muesli.net','blackmarket.to','bladesmail.net','bloatbox.com','blogmyway.org','blogos.com','bluebottle.com','bobmail.info','bodhi.lawlita.com','bofthew.com','bootybay.de','boun.cr','bouncr.com','boxformail.in','boximail.com','br.mintemail.com','brainonfire.net','breakthru.com','brefmail.com','brennendesreich.de','broadbandninja.com','bsnow.net','bspamfree.org','bu.mintemail.com','buffemail.com','bugmenever.com','bugmenot.com','bumpymail.com','bund.us','bundes-li.ga','burnthespam.info','burstmail.info','buymoreplays.com','buyusedlibrarybooks.org','byom.de','c2.hu','cachedot.net','cam4you.cc','card.zp.ua','casualdx.com','cc.liamria','cek.pm','cellurl.com','centermail.com','centermail.net','chammy.info','cheatmail.de','childsavetrust.org','chogmail.com','choicemail1.com','chong-mail.com','chong-mail.net','chong-mail.org','clixser.com','clrmail.com','cmail.com','cmail.net','cmail.org','cock.li','coieo.com','coldemail.info','consumerriot.com','cool.fr.nf','correo.blogos.net','cosmorph.com','courriel.fr.nf','courrieltemporaire.com','crapmail.org','crazymailing.com','cubiclink.com','cumallover.me','curryworld.de','cust.in','cuvox.de','d3p.dk','dacoolest.com','dandikmail.com','dayrep.com','dbunker.com','dcemail.com','deadaddress.com','deadchildren.org','deadfake.cf','deadfake.ga','deadfake.ml','deadfake.tk','deadspam.com','deagot.com','dealja.com','delikkt.de','despam.it','despammed.com','devnullmail.com','dfgh.net','dharmatel.net','dicksinhisan.us','dicksinmyan.us','digitalsanctuary.com','dingbone.com','discard.cf','discard.email','discard.ga','discard.gq','discard.ml','discard.tk','discardmail.com','discardmail.de','disposable.cf','disposable.ga','disposable.ml','disposableaddress.com','disposable-email.ml','disposableemailaddresses.com','disposableinbox.com','dispose.it','disposeamail.com','disposemail.com','dispostable.com','divermail.com','dm.w3internet.co.uk','dm.w3internet.co.ukexample.com','docmail.com','dodgeit.com','dodgit.com','dodgit.org','doiea.com','domozmail.com','donemail.ru','dontreg.com','dontsendmespam.de','dotman.de','dotmsg.com','drdrb.com','drdrb.net','dropcake.de','droplister.com','dropmail.me','dudmail.com','dumpandjunk.com','dump-email.info','dumpmail.de','dumpyemail.com','duskmail.com','e4ward.com','easytrashmail.com','edv.to','ee1.pl','ee2.pl','eelmail.com','einmalmail.de','einrot.com','einrot.de','eintagsmail.de','e-mail.com','email.net','e-mail.org','email60.com','emailage.cf','emailage.ga','emailage.gq','emailage.ml','emailage.tk','emaildienst.de','email-fake.cf','email-fake.ga','email-fake.gq','email-fake.ml','email-fake.tk','emailgo.de','emailias.com','emailigo.de','emailinfive.com','emaillime.com','emailmiser.com','emails.ga','emailsensei.com','emailspam.cf','emailspam.ga','emailspam.gq','emailspam.ml','emailspam.tk','emailtemporanea.com','emailtemporanea.net','emailtemporar.ro','emailtemporario.com.br','emailthe.net','emailtmp.com','emailto.de','emailwarden.com','emailx.at.hm','emailxfer.com','emailz.cf','emailz.ga','emailz.gq','emailz.ml','emeil.in','emeil.ir','emkei.cf','emkei.ga','emkei.gq','emkei.ml','emkei.tk','emz.net','enterto.com','ephemail.net','e-postkasten.com','e-postkasten.de','e-postkasten.eu','e-postkasten.info','ero-tube.org','etranquil.com','etranquil.net','etranquil.org','evopo.com','example.com','explodemail.com','express.net.ua','eyepaste.com','facebook-email.cf','facebook-email.ga','facebook-email.ml','facebookmail.gq','facebookmail.ml','faecesmail.me','fakedemail.com','fakeinbox.cf','fakeinbox.com','fakeinbox.ga','fakeinbox.ml','fakeinbox.tk','fakeinformation.com','fake-mail.cf','fakemail.fr','fake-mail.ga','fake-mail.ml','fakemailgenerator.com','fakemailz.com','fammix.com','fansworldwide.de','fantasymail.de','fastacura.com','fastchevy.com','fastchrysler.com','fastermail.com','fastkawasaki.com','fastmail.fm','fastmazda.com','fastmitsubishi.com','fastnissan.com','fastsubaru.com','fastsuzuki.com','fasttoyota.com','fastyamaha.com','fatflap.com','fdfdsfds.com','fightallspam.com','film-blog.biz','filzmail.com','fivemail.de','fixmail.tk','fizmail.com','fleckens.hu','flurred.com','flyspam.com','fly-ts.de','footard.com','forgetmail.com','fornow.eu','fr33mail.info','frapmail.com','freecoolemail.com','free-email.cf','free-email.ga','freeletter.me','freemail.ms','freemails.cf','freemails.ga','freemails.ml','freundin.ru','friendlymail.co.uk','front14.org','fuckingduh.com','fuckmail.me','fudgerub.com','fux0ringduh.com','fyii.de','garbagemail.org','garliclife.com','garrifulio.mailexpire.com','gawab.com','gehensiemirnichtaufdensack.de','gelitik.in','geschent.biz','get1mail.com','get2mail.fr','getairmail.cf','getairmail.com','getairmail.ga','getairmail.gq','getairmail.ml','getairmail.tk','get-mail.cf','get-mail.ga','get-mail.ml','get-mail.tk','getmails.eu','getonemail.com','getonemail.net','ghosttexter.de','giantmail.de','girlsundertheinfluence.com','gishpuppy.com','gmal.com','gmial.com','gmx.com','goat.si','goemailgo.com','gomail.in','gorillaswithdirtyarmpits.com','gotmail.com','gotmail.net','gotmail.org','gotti.otherinbox.com','gowikibooks.com','gowikicampus.com','gowikicars.com','gowikifilms.com','gowikigames.com','gowikimusic.com','gowikinetwork.com','gowikitravel.com','gowikitv.com','grandmamail.com','grandmasmail.com','great-host.in','greensloth.com','grr.la','gsrv.co.uk','guerillamail.biz','guerillamail.com','guerillamail.net','guerillamail.org','guerillamailblock.com','guerrillamail.biz','guerrillamail.com','guerrillamail.de','guerrillamail.info','guerrillamail.net','guerrillamail.org','guerrillamailblock.com','gustr.com','h.mintemail.com','h8s.org','hacccc.com','haltospam.com','harakirimail.com','hartbot.de','hatespam.org','hat-geld.de','herp.in','hidemail.de','hidzz.com','hmamail.com','hochsitze.com','hooohush.ai','hopemail.biz','horsefucker.org','hotmai.com','hot-mail.cf','hot-mail.ga','hot-mail.gq','hot-mail.ml','hot-mail.tk','hotmial.com','hotpop.com','huajiachem.cn','hulapla.de','humaility.com','hush.ai','hush.com','hushmail.com','hushmail.me','i2pmail.org','ieatspam.eu','ieatspam.info','ieh-mail.de','ignoremail.com','ihateyoualot.info','iheartspam.org','ikbenspamvrij.nl','imails.info','imgof.com','imgv.de','imstations.com','inbax.tk','inbox.si','inbox2.info','inboxalias.com','inboxclean.com','inboxclean.org','inboxdesign.me','inboxed.im','inboxed.pw','inboxstore.me','incognitomail.com','incognitomail.net','incognitomail.org','infocom.zp.ua','insorg-mail.info','instantemailaddress.com','instant-mail.de','iozak.com','ip6.li','ipoo.org','irish2me.com','iroid.com','is.af','iwantmyname.com','iwi.net','jetable.com','jetable.fr.nf','jetable.net','jetable.org','jnxjn.com','jourrapide.com','jsrsolutions.com','junk.to','junk1e.com','junkmail.ga','junkmail.gq','k2-herbal-incenses.com','kasmail.com','kaspop.com','keepmymail.com','killmail.com','killmail.net','kir.ch.tc','klassmaster.com','klassmaster.net','klzlk.com','kmhow.com','kostenlosemailadresse.de','koszmail.pl','kulturbetrieb.info','kurzepost.de','l33r.eu','lackmail.net','lags.us','landmail.co','lastmail.co','lavabit.com','lawlita.com','letthemeatspam.com','lhsdv.com','lifebyfood.com','link2mail.net','linuxmail.so','litedrop.com','llogin.ru','loadby.us','login-email.cf','login-email.ga','login-email.ml','login-email.tk','lol.com','lol.ovpn.to','lolfreak.net','lookugly.com','lopl.co.cc','lortemail.dk','losemymail.com','lovebitco.in','lovemeleaveme.com','loves.dicksinhisan.us','loves.dicksinmyan.us','lr7.us','lr78.com','lroid.com','luckymail.org','lukop.dk','luv2.us','m21.cc','m4ilweb.info','ma1l.bij.pl','maboard.com','mac.hush.com','mail.by','mail.me','mail.mezimages.net','mail.ru','mail.zp.ua','mail114.net','mail1a.de','mail21.cc','mail2rss.org','mail2world.com','mail333.com','mail4trash.com','mailbidon.com','mailbiz.biz','mailblocks.com','mailbucket.org','mailcat.biz','mailcatch.com','mailde.de','mailde.info','maildrop.cc','maildrop.cf','maildrop.ga','maildrop.gq','maildrop.ml','maildu.de','maileater.com','mailed.in','maileimer.de','mailexpire.com','mailfa.tk','mail-filter.com','mailforspam.com','mailfree.ga','mailfree.gq','mailfree.ml','mailfreeonline.com','mailguard.me','mailhazard.com','mailhazard.us','mailhz.me','mailimate.com','mailin8r.com','mailinater.com','mailinator.com','mailinator.gq','mailinator.net','mailinator.org','mailinator.us','mailinator2.com','mailincubator.com','mailismagic.com','mailita.tk','mailjunk.cf','mailjunk.ga','mailjunk.gq','mailjunk.ml','mailjunk.tk','mailme.gq','mailme.ir','mailme.lv','mailme24.com','mailmetrash.com','mailmoat.com','mailms.com','mailnator.com','mailnesia.com','mailnull.com','mailorg.org','mailpick.biz','mailquack.com','mailrock.biz','mailsac.com','mailscrap.com','mailseal.de','mailshell.com','mailsiphon.com','mailslapping.com','mailslite.com','mailtemp.info','mail-temporaire.fr','mailtome.de','mailtothis.com','mailtrash.net','mailtv.net','mailtv.tv','mailwithyou.com','mailzilla.com','mailzilla.org','makemetheking.com','malahov.de','manifestgenerator.com','manybrain.com','mbx.cc','mega.zik.dj','meinspamschutz.de','meltmail.com','messagebeamer.de','mezimages.net','mierdamail.com','migmail.pl','migumail.com','ministry-of-silly-walks.de','mintemail.com','misterpinball.de','mjukglass.nu','mmmmail.com','moakt.com','mobi.web.id','mobileninja.co.uk','moburl.com','moncourrier.fr.nf','monemail.fr.nf','monmail.fr.nf','monumentmail.com','ms9.mailslite.com','msa.minsmail.com','msb.minsmail.com','msg.mailslite.com','mt2009.com','mt2014.com','mt2015.com','muchomail.com','mx0.wwwnew.eu','my10minutemail.com','mycard.net.ua','mycleaninbox.net','myemailboxy.com','mymail-in.net','mynetstore.de','mypacks.net','mypartyclip.de','myphantomemail.com','mysamp.de','myspaceinc.com','myspaceinc.net','myspaceinc.org','myspacepimpedup.com','myspamless.com','mytempemail.com','mytempmail.com','mythrashmail.net','mytrashmail.com','nabuma.com','national.shitposting.agency','naver.com','neomailbox.com','nepwk.com','nervmich.net','nervtmich.net','netmails.com','netmails.net','netzidiot.de','neverbox.com','nevermail.de','nice-4u.com','nigge.rs','nincsmail.hu','nmail.cf','nnh.com','noblepioneer.com','nobugmail.com','nobulk.com','nobuma.com','noclickemail.com','nogmailspam.info','nomail.pw','nomail.xl.cx','nomail2me.com','nomorespamemails.com','nonspam.eu','nonspammer.de','noref.in','nospam.wins.com.br','no-spam.ws','nospam.ze.tc','nospam4.us','nospamfor.us','nospammail.net','nospamthanks.info','notmailinator.com','notsharingmy.info','nowhere.org','nowmymail.com','ntlhelp.net','nullbox.info','nurfuerspam.de','nus.edu.sg','nwldx.com','o2.co.uk','o2.pl','objectmail.com','obobbo.com','odaymail.com','odnorazovoe.ru','ohaaa.de','omail.pro','oneoffemail.com','oneoffmail.com','onewaymail.com','onlatedotcom.info','online.ms','oopi.org','opayq.com','ordinaryamerican.net','otherinbox.com','ourklips.com','outlawspam.com','ovpn.to','owlpic.com','pancakemail.com','paplease.com','pcusers.otherinbox.com','pepbot.com','pfui.ru','phentermine-mortgages-texas-holdem.biz','pimpedupmyspace.com','pjjkp.com','plexolan.de','poczta.onet.pl','politikerclub.de','poofy.org','pookmail.com','postonline.me','powered.name','privacy.net','privatdemail.net','privy-mail.com','privymail.de','privy-mail.de','proxymail.eu','prtnx.com','prtz.eu','punkass.com','put2.net','putthisinyourspamdatabase.com','pwrby.com','qasti.com','qisdo.com','qisoa.com','qoika.com','qq.com','quickinbox.com','quickmail.nl','rcpt.at','rcs.gaggle.net','reallymymail.com','realtyalerts.ca','receiveee.com','recode.me','recursor.net','recyclemail.dk','redchan.it','regbypass.com','regbypass.comsafe-mail.net','rejectmail.com','reliable-mail.com','remail.cf','remail.ga','rhyta.com','rklips.com','rmqkr.net','royal.net','rppkn.com','rtrtr.com','s0ny.net','safe-mail.net','safersignup.de','safetymail.info','safetypost.de','sandelf.de','saynotospams.com','scatmail.com','schafmail.de','schmeissweg.tk','schrott-email.de','secmail.pw','secretemail.de','secure-mail.biz','secure-mail.cc','selfdestructingmail.com','selfdestructingmail.org','sendspamhere.com','senseless-entertainment.com','server.ms','services391.com','sharklasers.com','shieldedmail.com','shieldemail.com','shiftmail.com','shitmail.me','shitmail.org','shitware.nl','shmeriously.com','shortmail.net','shut.name','shut.ws','sibmail.com','sify.com','sina.cn','sina.com','sinnlos-mail.de','siteposter.net','skeefmail.com','sky-ts.de','slapsfromlastnight.com','slaskpost.se','slave-auctions.net','slopsbox.com','slushmail.com','smaakt.naar.gravel','smapfree24.com','smapfree24.de','smapfree24.eu','smapfree24.info','smapfree24.org','smashmail.de','smellfear.com','snakemail.com','sneakemail.com','sneakmail.de','snkmail.com','sofimail.com','sofortmail.de','sofort-mail.de','sogetthis.com','sohu.com','solvemail.info','soodomail.com','soodonims.com','spam.la','spam.su','spam4.me','spamail.de','spamarrest.com','spamavert.com','spam-be-gone.com','spambob.com','spambob.net','spambob.org','spambog.com','spambog.de','spambog.net','spambog.ru','spambooger.com','spambox.info','spambox.irishspringrealty.com','spambox.org','spambox.us','spamcannon.com','spamcannon.net','spamcero.com','spamcon.org','spamcorptastic.com','spamcowboy.com','spamcowboy.net','spamcowboy.org','spamday.com','spamdecoy.net','spamex.com','spamfighter.cf','spamfighter.ga','spamfighter.gq','spamfighter.ml','spamfighter.tk','spamfree.eu','spamfree24.com','spamfree24.de','spamfree24.eu','spamfree24.info','spamfree24.net','spamfree24.org','spamgoes.in','spamgourmet.com','spamgourmet.net','spamgourmet.org','spamherelots.com','spamhereplease.com','spamhole.com','spamify.com','spaminator.de','spamkill.info','spaml.com','spaml.de','spammotel.com','spamobox.com','spamoff.de','spamsalad.in','spamslicer.com','spamspot.com','spamstack.net','spamthis.co.uk','spamthisplease.com','spamtrail.com','spamtroll.net','speed.1s.fr','spoofmail.de','squizzy.de','sry.li','ssoia.com','startkeys.com','stinkefinger.net','stop-my-spam.cf','stop-my-spam.com','stop-my-spam.ga','stop-my-spam.ml','stop-my-spam.tk','stuffmail.de','suioe.com','super-auswahl.de','supergreatmail.com','supermailer.jp','superplatyna.com','superrito.com','superstachel.de','suremail.info','sweetxxx.de','tafmail.com','tagyourself.com','talkinator.com','tapchicuoihoi.com','techemail.com','techgroup.me','teewars.org','teleworm.com','teleworm.us','temp.emeraldwebmail.com','tempail.com','tempalias.com','tempemail.biz','tempemail.co.za','tempemail.com','tempe-mail.com','tempemail.net','tempimbox.com','tempinbox.co.uk','tempinbox.com','tempmail.eu','tempmail.it','temp-mail.org','temp-mail.ru','tempmail2.com','tempmaildemo.com','tempmailer.com','tempmailer.de','tempomail.fr','temporarily.de','temporarioemail.com.br','temporaryemail.net','temporaryemail.us','temporaryforwarding.com','temporaryinbox.com','temporarymailaddress.com','tempthe.net','tempymail.com','tfwno.gf','thanksnospam.info','thankyou2010.com','thc.st','thecloudindex.com','thelimestones.com','thisisnotmyrealemail.com','thismail.net','thrma.com','throam.com','throwawayemailaddress.com','throwawaymail.com','tijdelijkmailadres.nl','tilien.com','tittbit.in','tizi.com','tmail.com','tmailinator.com','toiea.com','tokem.co','toomail.biz','topcoolemail.com','topfreeemail.com','topranklist.de','tormail.net','tormail.org','tradermail.info','trash2009.com','trash2010.com','trash2011.com','trash-amil.com','trashcanmail.com','trashdevil.com','trashdevil.de','trashemail.de','trashinbox.com','trashmail.at','trash-mail.at','trash-mail.cf','trashmail.com','trash-mail.com','trashmail.de','trash-mail.de','trash-mail.ga','trash-mail.gq','trashmail.me','trash-mail.ml','trashmail.net','trashmail.org','trash-mail.tk','trashmail.ws','trashmailer.com','trashymail.com','trashymail.net','trayna.com','trbvm.com','trialmail.de','trickmail.net','trillianpro.com','tryalert.com','turual.com','twinmail.de','tyldd.com','ubismail.net','uggsrock.com','umail.net','upliftnow.com','uplipht.com','uroid.com','us.af','uyhip.com','valemail.net','venompen.com','verticalscope.com','veryrealemail.com','veryrealmail.com','vidchart.com','viditag.com','viewcastmedia.com','viewcastmedia.net','viewcastmedia.org','vipmail.name','vipmail.pw','viralplays.com','vistomail.com','vomoto.com','vpn.st','vsimcard.com','vubby.com','vztc.com','walala.org','walkmail.net','wants.dicksinhisan.us','wants.dicksinmyan.us','wasteland.rfc822.org','watchfull.net','watch-harry-potter.com','webemail.me','webm4il.info','webuser.in','wegwerfadresse.de','wegwerfemail.com','wegwerfemail.de','wegwerf-email.de','weg-werf-email.de','wegwerfemail.net','wegwerf-email.net','wegwerfemail.org','wegwerf-email-addressen.de','wegwerfemailadresse.com','wegwerf-email-adressen.de','wegwerf-emails.de','wegwerfmail.de','wegwerfmail.info','wegwerfmail.net','wegwerfmail.org','wegwerpmailadres.nl','wegwrfmail.de','wegwrfmail.net','wegwrfmail.org','wetrainbayarea.com','wetrainbayarea.org','wh4f.org','whatiaas.com','whatpaas.com','whatsaas.com','whopy.com','whyspam.me','wickmail.net','wilemail.com','willhackforfood.biz','willselfdestruct.com','winemaven.info','wmail.cf','wolfsmail.tk','writeme.us','wronghead.com','wuzup.net','wuzupmail.net','www.e4ward.com','www.gishpuppy.com','www.mailinator.com','wwwnew.eu','x.ip6.li','xagloo.co','xagloo.com','xemaps.com','xents.com','xmail.com','xmaily.com','xoxox.cc','xoxy.net','xxtreamcam.com','xyzfree.net','yandex.com','yanet.me','yapped.net','yeah.net','yep.it','yogamaven.com','yomail.info','yopmail.com','yopmail.fr','yopmail.gq','yopmail.net','youmail.ga','youmailr.com','yourdomain.com','you-spam.com','ypmail.webarnak.fr.eu.org','yuurok.com','yxzx.net','z1p.biz','za.com','zebins.com','zebins.eu','zehnminuten.de','zehnminutenmail.de','zetmail.com','zippymail.info','zoaxe.com','zoemail.com','zoemail.net','zoemail.org','zomg.info');

			$email = ( in_array($domain,$disposables) ? false : $email );
		}

		if( $email ){
			
			//checkdnsrr
			
			$email = ( !$this->has_dns($domain) ? false : $email );
		}

		return $email;
	}
	
	public function has_dns($domain){
		
		if( !empty($domain) ){
		
			if( !isset($this->dnsList[$domain]) ){
				
				$this->dnsList[$domain] = checkdnsrr($domain,'A');
			}
			
			return $this->dnsList[$domain];
		}
		
		return false;
	}
	
	public function get_notification_settings( $field = 'default' ){
		
		// get notification settings
		
		if( is_null($this->notification_settings) ){

			$this->notification_settings = apply_filters('ltple_notification_settings',[]);
		}
		
		// output notification settings
		
		$notification_settings = array();
		
		if( $field == 'all' ){
			
			$notification_settings = $this->notification_settings;
		}
		elseif( !empty($this->notification_settings) ){
			
			foreach($this->notification_settings as $key => $data ){
				
				if( isset($data[$field]) ){
					
					$notification_settings[$key] = $data[$field];
				}
			}
		}
		
		return $notification_settings;
	}

	public function init_email(){

		add_action('ltple_newsletter_unsubscribe_user_channel',function($user_id,$channel){
			
			if( $notify = $this->parent->users->get_user_notification_settings($user_id)){
			
				$notify[$channel] = 'false';
			
				update_user_meta($user_id, 'ltple_notify', $notify);					
			}
			
		},10,2);	
		
		if( !is_admin() ){
			
			if( $this->parent->user->is_admin ){
				
				$this->maxRequests = 500;
			}
		
			if( !empty($_POST['importEmails']) ){
				
				if( $this->parent->user->loggedin ){
				
					$this->bulk_import_users( $_POST['importEmails'] );
				}
			}
		}
	}
	
	public function insert_user($email, $check_exists = true ){

		if( $this->is_email($email) && ( !$check_exists || !email_exists( $email ) ) ){
			
			if( !function_exists('is_plugin_active') ){
				
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}			
			
			if( is_plugin_active('wpforo/wpforo.php') ){
				
				//fix wpforo error
			
				global $wpforo;
				
				$wpforo->current_user_groupid = null;
			}
			
			// get username
			
			$username = strtok($email, '@');
			
			$username = str_replace(array('+','.','-','_'),' ',$username);
			
			$username = ucwords($username);
			
			$username = str_replace(' ','',$username);
			
			$i = '';
			$exists = false;
			
			do{

				if( !$exists ){
					
					$exists = true;
				}
				else{

					$i = intval($i) + 1;
				}
				
			} while( username_exists( $username . $i ) !== false );

			if( $user_id = wp_insert_user( array(
			
				'user_login'	=>  $username . $i,
				'user_pass'		=>  NULL,
				'user_email'	=>  $email,
			)) ){
			
				$user = array(
				
					'id' 	=> $user_id,
					'name' 	=> $username . $i,
					'email' => $email,
				);
				
				return $user;
			}
		}	

		return false;
	}
	
	public function bulk_import_users( $csv ){
		
		// normalize csv
		
		$csv = preg_replace('#\s+#',',',trim($csv));
		
		// get emails
		
		$emails = explode(',',$csv);

		// parse emails
		
		foreach( $emails as $i => $email){
			
			$email = trim( $email );
			
			if( !empty( $email ) ){
			
				if( filter_var($email, FILTER_VALIDATE_EMAIL) ){
					
					if( $user = email_exists( $email ) ){

						$this->imported['already registered'][] = ['id' => $user, 'email' => $email ];
					}					
					else{
						
						if( $user = $this->insert_user($email, false) ){
							
							$this->parent->channels->update_user_channel($user['id'],'User Invitation');
							
							$this->imported['imported'][] = $user;
						}
						else{
							
							$this->imported['errors'][] = $email;
						}
					}
				}
				else{
					
					$this->imported['are invalid'][] = $email;
				}
				
				if( $i == $this->maxRequests){
					
					break;
				}				
			}
		}
		
		do_action('ltple_users_bulk_imported');

		return true;
	}
	
	public function send_subscription_summary( $user, $plan_id = null ){
		
		if( is_numeric($user) ){
			
			$user = get_user_by( 'id', $user );
			
			$user->user_email = $user->data->user_email;
		}
		elseif( is_string($user) ){
			
			$user = get_user_by( 'email', $user );
		}
		
		if( !empty($user->user_email) ){			
		
			if( is_numeric($plan_id) ){
				
				$plan = get_post( $plan_id );
			}
			
			// get user plan info 
			
			$user_plan = $this->parent->plan->get_user_plan_info( $user->ID );
			
			// count users
			
			$result = count_users();
			
			// get company name
			
			$company = ucfirst(get_bloginfo('name'));
			
			// get email title
			
			if( !empty($plan->post_title) ){
				
				$Email_title = 'Thank you for subscribing to ' . $plan->post_title . ' on '.$company.'';
			}
			else{
				
				$Email_title = 'Your '.$company.' subscription summary';
			}
			
			// get thumb
			
			if( !empty($plan->ID) ){
				
				$thumb_url = $this->parent->plan->get_thumb_url($plan->ID);
			}
			else{

				$thumb_url = get_option( $this->parent->_base . 'main_image' );
			}

			// get email message
			
			$editor_url = $this->parent->urls->gallery; 
			
			$user_name = !empty($user->nickname) ? ucfirst($user->nickname) : ucfirst($user->user_nicename);
			
			$message = '<table style="width: 100%; max-width: 100%; min-width: 320px; background-color: #f1f1f1;margin:0;padding:40px 0 45px 0;margin:0 auto;text-align:center;border:0;">';
						
				$message .= '<tr>';
					
					$message .= '<td>';
						
						$message .= '<table style="width: 100%; max-width: 600px; min-width: 320px; background-color: #FFFFFF;border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;-ms-border-radius:5px 5px 0 0;-o-border-radius:5px 5px 0 0;-webkit-border-radius:5px 5px 0 0;text-align:center;border:0;margin:0 auto;font-family: Arial, sans-serif;">';
							
							$message .= '<tr>';
								
								$message .= '<td style="text-align:center;background-color:#ffffff;border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;-ms-border-radius:5px 5px 0 0;-o-border-radius:5px 5px 0 0;-webkit-border-radius:5px 5px 0 0;background-image: url('.$thumb_url.');background-repeat:no-repeat;background-size:100% auto;background-position:top center;overflow:hidden;">';
									
									$message .= '<a href="'.$editor_url.'" target="_blank" title="'.$company.'" style="display:block;width:90%;height:300px;text-align:left;overflow:hidden;font-size:24px;color:#FFFFFF!important;text-decoration:none;font-weight:bold;padding:16px 14px 9px;font-family:Arial, Helvetica, sans-serif;position:reltive;margin:0 auto;">&nbsp;</a>';
									
								$message .= '</td>';
							
							$message .= '</tr>';
							
							$message .= '<tr>';
								
								$message .= '<td style="font-family: Arial, sans-serif;padding:10px 0 15px 0;font-size:19px;color:#888888;font-weight:bold;border-bottom:1px solid #cccccc;text-align:center;background-color:#FFFFFF;">';
									
									$message .= 'Subscription Summary';
									
								$message .= '</td>';
							
							$message .= '</tr>';
							
							$message .= '<tr>';	

								$message .= '<td style="line-height: 25px;font-family: Arial, sans-serif;padding:20px;font-size:15px;color:#666666;text-align:left;font-weight: normal;border:0;background-color:#FFFFFF;">';
									
									$message .= 'Hello '.$user_name.',' . PHP_EOL . PHP_EOL;
										
										if( !empty($plan->post_title) ){
										
											$message .= 'Congratulations, you have successfully subscibed to ' . $plan->post_title . ' on '.$company.'.' . PHP_EOL . PHP_EOL;
				
											$message .= 'We are happy to count you among our users and will be here to help you with any step along the way.'. PHP_EOL . PHP_EOL;
										}
										
								$message .=  '</td>';
											
							$message .= '</tr>';
							
							/*	
							$message .= '<tr>';	

								$message .= '<td style="line-height: 25px;font-family: Arial, sans-serif;padding:10px 20px ;font-size:15px;color:#666666;text-align:left;font-weight: normal;border:0;background-color:#FFFFFF;">';
																						
									$message .= 'Here comes the summary of your current subscription and a list of your features: ' . PHP_EOL;
										
								$message .=  '</td>';
										
							$message .= '</tr>';
							
							$message .= '<tr>';													
										
								$message .= '<td style="background: rgb(248, 248, 248);display:block;padding:20px;margin:20px;text-align:left;border-left: 5px solid #888;">';
									
									$message .='<b>Price</b>: ' . $user_plan['info']['total_price_currency'].$user_plan['info']['total_price_amount'].' / '.$user_plan['info']['total_price_period'] . PHP_EOL;
			
									$message .= PHP_EOL;
									
									if( !empty($user_plan['taxonomies']['layer-type']['terms']) ){
									
										$message .= '<b>Template Types</b>';
										
										$message .= '<ul>';

											foreach( $user_plan['taxonomies']['layer-type']['terms'] as $term ){
												
												if( $term['has_term'] ){
													
													$message .= '<li>'.ucfirst($term['name']).'</li>';
												}
											}
										
										$message .= '</ul>';
									}
									
									if( !empty($user_plan['taxonomies']['layer-range']['terms']) ){
									
										$message .= '<b>Template Ranges</b>';
										
										$message .= '<ul>';

											foreach( $user_plan['taxonomies']['layer-range']['terms'] as $term ){
												
												if( $term['has_term'] ){
													
													$message .= '<li>'.ucfirst($term['name']).'</li>';
												}
											}
										
										$message .= '</ul>';
									}
								
								$message .=  '</td>';
										
							$message .= '</tr>';
							*/

							$message .= '<tr>';	

								$message .= '<td style="font-family: Arial, sans-serif;height:150px;font-size:16px;color:#666666;text-align:center;border:0;background-color:#FFFFFF;">';
																								
									$message .=  '<a style="background: ' . $this->parent->settings->mainColor . ';color: #fff;padding: 17px;text-decoration: none;border-radius: 5px;font-weight: bold;font-size: 20px;" href="'.$editor_url.'">Start Editing </a>' . PHP_EOL . PHP_EOL;

								$message .=  '</td>';
							$message .=  '</tr>';
						$message .=  '</table>';
						
					$message .=  '<td>';
				$message .=  '<tr>';
			$message .=  '</table>';
			
			$message = str_replace(PHP_EOL,'<br/>',$message);
			
			// get sender
			
			$urlparts = parse_url(site_url());
			$domain = $urlparts ['host'];
			
			$sender_email 	= 'please-reply@'.$domain;			
			
			// get email headers
			
			$headers   = [];
			$headers[] = 'From: ' . $company . ' <'.$sender_email.'>';
			//$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html';

			$preMessage = "<html><body><div style='width:700px;padding:5px;margin:auto;font-size:14px;line-height:18px'>" . apply_filters('the_content', $message) . "<div style='clear:both'></div><div style='clear:both'></div></div></body></html>";
			
			if(!wp_mail($user->user_email, $Email_title, $preMessage, $headers)){
				
				global $phpmailer;
				
				wp_mail($this->parent->settings->options->emailSupport, 'Error subscription summary to ' . $user->user_email, print_r($phpmailer->ErrorInfo,true));
				
				var_dump($phpmailer->ErrorInfo);exit;				
			}			
		}
	}
	
	public function get_invitation_form( $type='' ){
		
		$this->invitationForm = '';
		
		// get response message
		
		if( !empty($this->imported) ){
			
			$this->invitationForm .= '<div class="alert alert-info" style="padding:10px;">';
			
				foreach( $this->imported as $label => $data ){
					
					$count = count($data);
					
					if( $count == 1 ){
						
						$this->invitationForm .= $count . ' email ' . $label. '<br/>' ;
					}
					else{
						
						$this->invitationForm .= $count . ' emails ' . $label. '<br/>' ;
					}
				}
			
			$this->invitationForm .='</div>';
		}

		// get company name 
		
		$company = ucfirst(get_bloginfo('name'));
		
		// get default user message
		
		do_action('ltple_get_'.$type.'_message');
		
		if( empty($this->invitationMessage) ){
			
			$this->invitationMessage = 'Hello, ' . PHP_EOL . PHP_EOL;
			
			$this->invitationMessage .= 'I invite you to try ' . $company . ':' . PHP_EOL . PHP_EOL;
			
			$this->invitationMessage .= add_query_arg( array(
			
				'ri' =>	$this->parent->user->refId,
				
			), $this->parent->urls->gallery ) . PHP_EOL . PHP_EOL;
			
			$this->invitationMessage .= 'Yours,' . PHP_EOL;
			$this->invitationMessage .= ucfirst( $this->parent->user->nickname ) . PHP_EOL;
		}		
		
		//output form			
			
		$this->invitationForm .= '<div class="well" style="display:inline-block;width:100%;">';
		
			$this->invitationForm .= '<div class="col-xs-12 col-md-6">';
			
				$this->invitationForm .= '<form action="' . $this->parent->urls->current . '" method="post">';
		
					$this->invitationForm .= '<input type="hidden" name="importType" value="'.$type.'" />';
					
					do_action('ltple_prepend_'.$type.'_form');
		
					$this->invitationForm .= '<h5 style="padding:15px 0 5px 0;font-weight:bold;">CSV list of emails</h5>';
				
					$this->invitationForm .= $this->parent->admin->display_field( array(
					
						'id' 			=> 'importEmails',
						'label'			=> 'Add emails',
						'description'	=> '<i style="font-size:11px;">Copy paste a list of max ' . $this->maxRequests . ' emails separated by comma or line break</i>',
						'placeholder'	=> 'example1@gmail.com' . PHP_EOL . 'example2@yahoo.com',
						'default'		=> ( !empty($_POST['importEmails']) ? $_POST['importEmails'] : ''),
						'type'			=> 'textarea',
						'style'			=> 'width:100%;height:100px;',
					), false, false );
				
					$this->invitationForm .= '<hr/>';
					
					$this->invitationForm .= '<h5 style="padding:15px 0 5px 0;font-weight:bold;">Add custom message</h5>';
					
					$this->invitationForm .= $this->parent->admin->display_field( array(
					
						'id' 			=> 'importMessage',
						'label'			=> 'Add custom message',
						'description'	=> '<i style="font-size:11px;">Use only text and line break, no HTML</i>',
						'placeholder'	=> 'Your custom message',
						'default'		=> ( !empty($_POST['importMessage']) ? $_POST['importMessage'] : $this->invitationMessage),
						'type'			=> 'textarea',
						'style'			=> 'width:100%;height:100px;',
					), false, false );
					
					do_action('ltple_append_invitation_form');
				
					$this->invitationForm .= '<hr/>';
				
					$this->invitationForm .= '<button style="margin-top:10px;" class="btn btn-xs btn-primary pull-right" type="submit">';
						
						$this->invitationForm .= 'Send';
						
					$this->invitationForm .= '</button>';
				
				$this->invitationForm .= '</form>';
			
			$this->invitationForm .= '</div>';
			
			$this->invitationForm .= '<div class="col-xs-12 col-md-6">';
			
				$this->invitationForm .= '<table class="table table-striped table-hover">';
				
					$this->invitationForm .= '<thead>';
						$this->invitationForm .= '<tr>';
							$this->invitationForm .= '<th><b>Information</b></th>';
						$this->invitationForm .= '</tr>';
					$this->invitationForm .= '</thead>';
					
					$this->invitationForm .= '<tbody>';
						$this->invitationForm .= '<tr>';
							$this->invitationForm .= '<td>Copy paste a list of emails separated by comma or line break that you want to invite.</td>';
						$this->invitationForm .= '</tr>';															
					$this->invitationForm .= '</tbody>';
					
				$this->invitationForm .= '</table>';			
			
			$this->invitationForm .= '</div>';
		
		$this->invitationForm .= '</div>';

		return $this->invitationForm;
	}
	
	public function schedule_invitations(){
			
		$response = false;
			
		$importType = '';
			
		if( !empty($_POST['importType']) ){
			
			$importType = sanitize_text_field($_POST['importType']);
		}
		
		//get time limit
		
		$max_execution_time = ini_get('max_execution_time'); 
		
		//remove time limit
		
		set_time_limit(0);

		//schedule_invitations
		
		if( !empty($importType) && method_exists($this->parent->{$importType},'schedule_invitations')){

			$response =  $this->parent->{$importType}->schedule_invitations();
		}
		else{
			
			// get users
					
			$users = array();			
			
			if(!empty($this->imported['imported'])){
				
				$users = $this->imported['imported'];
			}
			
			/*
			if(!empty($this->parent->email->imported['already registered'])){
			
				$users = array_merge($users,$this->parent->email->imported['already registered']);
			}
			*/

			if(!empty($users)){
				
				// get plan thumb
			
				$plan_thumb = get_option( $this->parent->_base . 'main_image' );
				
				// get company name
				
				$company = ucfirst(get_bloginfo('name'));
				
				// make invitations
				
				$m = 0;
				
				foreach($users as $i => $user){
					
					// get plan permalink
				
					$editor_url = add_query_arg( array(
						
						'ri' 	=> $this->parent->user->refId,
						
					), $this->parent->urls->gallery ); 
					
					$can_spam = get_user_meta( $user['id'], 'ltple__can_spam',true);

					if( $can_spam == 'true' ){
					
						//get invitation title
						
						$invitation_title = 'User invitation - ' . ucfirst($this->parent->user->nickname) . ' is inviting you to try ' . $company . ' ';
						
						//check if invitation exists

						if( !$invitation = get_posts(array(
							
							'post_type' 	=> 'email-invitation',
							'author' 		=> $this->parent->user->ID,

							'meta_query' 	=> array(	
								array(
								
									'key' 		=> 'invited_user_email',
									'value' 	=> $user['email'],									
								),
							)
						
						))){

							//get invitation content
							
							$invitation_content = '<table style="width: 100%; max-width: 100%; min-width: 320px; background-color: #f1f1f1;margin:0;padding:40px 0 45px 0;margin:0 auto;text-align:center;border:0;">';
										
								$invitation_content .= '<tr>';
									
									$invitation_content .= '<td>';
										
										$invitation_content .= '<table style="width: 100%; max-width: 600px; min-width: 320px; background-color: #FFFFFF;border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;-ms-border-radius:5px 5px 0 0;-o-border-radius:5px 5px 0 0;-webkit-border-radius:5px 5px 0 0;text-align:center;border:0;margin:0 auto;font-family: Arial, sans-serif;">';
											
											$invitation_content .= '<tr>';
												
												$invitation_content .= '<td style="font-family: Arial, sans-serif;padding:10px 0 15px 0;font-size:19px;color:#888888;font-weight:bold;border-bottom:1px solid #cccccc;text-align:center;background-color:#FFFFFF;">';
													
													$invitation_content .= 'Friendly Invitation';
													
												$invitation_content .= '</td>';
											
											$invitation_content .= '</tr>';
											
											$invitation_content .= '<tr>';	

												$invitation_content .= '<td style="line-height: 25px;font-family: Arial, sans-serif;padding:20px;font-size:15px;color:#666666;text-align:left;font-weight: normal;border:0;background-color:#FFFFFF;">';
													
													$invitation_content .= 'Hello '.$user_name.',' . PHP_EOL . PHP_EOL;
													
													$invitation_content .= ucfirst($this->parent->user->nickname) . ' is currently using <b>' . $company . '</b> and is inviting you to try it!' . PHP_EOL . PHP_EOL;
													
												$invitation_content .=  '</td>';
															
											$invitation_content .= '</tr>';
													
											if( !empty($_POST['importMessage']) ){
											
												$invitation_content .= '<tr>';	

													$invitation_content .= '<td style="line-height: 25px;font-family: Arial, sans-serif;padding:10px 20px ;font-size:15px;color:#666666;text-align:left;font-weight: normal;border:0;background-color:#FFFFFF;">';
																											
														$invitation_content .= 'Additional message from ' . ucfirst($this->parent->user->nickname) . ': ' . PHP_EOL;
															
													$invitation_content .=  '</td>';
															
												$invitation_content .= '</tr>';

												$invitation_content .= '<tr>';													
															
													$invitation_content .= '<td style="background: rgb(248, 248, 248);display:block;padding:20px;margin:20px;text-align:left;border-left: 5px solid #888;">';
															
														$invitation_content .= $_POST['importMessage'];
													
													$invitation_content .=  '</td>';
															
												$invitation_content .= '</tr>';														
											}

											$invitation_content .= '<tr>';	

												$invitation_content .= '<td style="font-family: Arial, sans-serif;height:150px;font-size:16px;color:#666666;text-align:center;border:0;background-color:#FFFFFF;">';
																												
													$invitation_content .=  '<a style="background: ' . $this->parent->settings->mainColor . ';color: #fff;padding: 17px;text-decoration: none;border-radius: 5px;font-weight: bold;font-size: 20px;" href="'.$editor_url.'">Let\'s do it! </a>' . PHP_EOL . PHP_EOL;

												$invitation_content .=  '</td>';
											$invitation_content .=  '</tr>';
										$invitation_content .=  '</table>';
										 
									$invitation_content .=  '<td>';
								$invitation_content .=  '<tr>';
							$invitation_content .=  '</table>';
							
							$invitation_content = str_replace(PHP_EOL,'<br/>',$invitation_content);
							
							//insert invitation
							
							if($invitation_id = wp_insert_post( array(
							
								'post_type'     	=> 'email-invitation',
								'post_title' 		=> $invitation_title,
								'post_content' 		=> $invitation_content,
								'post_status' 		=> 'publish',
								'menu_order' 		=> 0
							))){
								
								update_post_meta($invitation_id,'invited_user_email',$user['email']);
								
								wp_schedule_single_event( ( time() + ( 60 * $m ) ) , 'ltple_send_email_event' , [$invitation_id,$user['email']] );
								
								if ($i % 10 == 0) {
									
									++$m;
								}								
							}
						}
					}
				}
			}				
		}
		
		//reset time limit
		
		set_time_limit($max_execution_time);
		
		return $response;
	}
	
	/**
	 * Main LTPLE_Client_Email Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Email is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Email instance
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