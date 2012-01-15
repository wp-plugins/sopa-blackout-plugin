<?php
if ( ! isset( $sopa_opts ) ) {
	include_once( '../../../wp-load.php' );
	$sopa_opts = get_sopa_options();
}
$continue_link = '<br /> <a style="background: #9e0918; color: #fff;" href="';
if ( empty( $sopa_opts['site_link'] ) ) { 
	$continue_link .= get_bloginfo( 'siteurl' ); 
} else { 
	$continue_link .= get_permalink( $sopa_opts['site_link'] );
}
$continue_link .= '">' . __( 'Continue to site.' ) . '</a>';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo plugins_url( 'supplemental/reset.css', __FILE__ ) ?>" /> 
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo plugins_url( 'supplemental/grid.css', __FILE__ ) ?>" /> 
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo plugins_url( 'supplemental/style.css', __FILE__ ) ?>" /> 
		<link rel="shortcut icon" href="/favicon.ico" />
		<title>Stop SOPA!</title>
	</head>
	<body>
		<div class="container_12">
			<h1 class="grid_12 img"><a href="http://downagainstsopa.com"><img src="<?php echo plugins_url( 'images/header.png', __FILE__ ) ?>" alt="<?php _e( 'Down Against SOPA' ) ?>" /></a><img src="<?php echo plugins_url( 'images/header2.png', __FILE__ ) ?>" alt="This Website is Down Today" /><a href="http://wordpress.org/extend/plugins/sopa-blackout-plugin/"><img src="<?php echo plugins_url( 'images/header3.png', __FILE__ ) ?>" alt="<?php _e( 'Get the WordPress Plugin' ) ?>" /></a></h1>
			<div class="clear"></div>

			<p class="grid_8 white img"><img src="<?php echo plugins_url( 'images/capitol.jpg', __FILE__ ) ?>" alt="" /></p>
			<p class="grid_6 white censor"><span><?php _e( 'If Congress passes SOPA, the Internet will be permanently censored.' ) ?></span><br /><a class="cta" href="http://downagainstsopa.com/takeaction.php"><span><?php _e( 'Take Action Now' ) ?></span></a></p>
			<p class="grid_4 banner"><?php printf( __( 'Congress is about to vote on the Stop Online Piracy Act (SOPA). If passed, this bill will allow the United States government to censor the Internet. <br /><a href="%s">Join the fight against it!</a>' ), 'http://downagainstsopa.com/takeaction.php' ) ?>
<?php 
/* If just the home page should redirect and either the cookie is turned on or the site link is something other than the home page, show the Continue to site link */ 
if ( empty( $sopa_opts['no_cookie'] ) ) { 
	echo $continue_link; 
} else if ( empty( $sopa_opts['all_pages'] ) && ! empty( $sopa_opts['site_link'] ) ) {
	echo $continue_link;
}
?></p>
			<div class="clear"></div>
			<div class="border_2"></div>
			<div class="grid_12 trio">	
				<div class="grid_4 alpha">
					<h2><?php _e( 'Misdirected Legislation' ) ?></h2>
					<p><?php printf( __( 'Although SOPA advocates claim it targets foreign pirates, <a href="%s">the Electronic Frontier Foundation reports</a> that &ldquo;broad definitions and vague language&rdquo; allow the bill to shut down legitimate US websites without due process. Among these sites: Etsy, Flickr, and Vimeo.' ), 'https://www.eff.org/deeplinks/2011/11/whats-blacklist-three-sites-sopa-could-put-risk' ) ?></p>
				</div>
				<div class="grid_4">
					<h2><?php _e( 'Uninformed Support' ) ?></h2>
					<p><?php printf( __( 'SOPA supporters in Congress actively avoided feedback from <a href="%s">public interest groups</a>, Internet <a href="%s">investors</a> and professionals, <a href="%s">technology companies</a>, and <a href="%s">independent artists</a>. They were too busy listening to lobbyists to hear the widespread outrage over the bill&rsquo;s many flaws.' ), 'https://action.eff.org/o/9042/p/dia/action/public/?action_KEY=8173', 'http://www.businessinsider.com/paul-graham-just-exiled-these-big-companies-from-y-combinator-demo-days-demo-days-2011-12', 'http://www.tumblr.com/protect-the-net', 'http://filmfwd.com/2011/12/no-protection-filmmaker-against-sopa/' ) ?></p>     
				</div>
				<div class="grid_4 omega">
					<h2><?php _e( 'What You Can Do' ) ?></h2>
					<p><?php printf( __( 'It is crucial that we demonstrate our opposition. Let Congress know you oppose SOPA. <a href="%s">Look up your senators&rsquo; numbers</a> and call them. Or <a href="%s">send a message</a> announcing your opposition. Working together, we can protect your First Ammendment rights.' ), 'https://www.senate.gov/general/contact_information/senators_cfm.cfm', 'https://www.senate.gov/general/contact_information/senators_cfm.cfm' ) ?></p>     
				</div>
			</div>
			<div class="clear"></div>
<?php
if ( ! isset( $sopa_opts ) || 1 != $sopa_opts['backlinks'] ) {
?>
			<p class="grid_12 footer"><a href="http://downagainstsopa.com"><?php _e( 'Down Against Sopa' ) ?></a> <?php _e( 'sponsored by' ) ?> <a href="http://www.staycuriousmyfriends.com/">bwb</a> &middot; <?php _e( 'Copyright' ) ?> &copy; 2012 <a href="http://ctidd.com">Chris Tidd</a>; <a href="https://www.gnu.org/copyleft/gpl.html"><?php _e( 'code released under GPLv3' ) ?></a> <?php _e( 'unless otherwise licensed.' ) ?><br /><?php _e( 'WordPress Development by' ) ?> <a href="http://ten-321.com/">Ten-321 Enterprises</a></p>
<?php
}
?>
		</div>
		<img src="<?php echo plugins_url( 'images/cta_hover.png', __FILE__ ) ?>" alt="" style="display: none;" />
	</body>
</html>
