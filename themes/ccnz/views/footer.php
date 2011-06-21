	</div>
	<!-- / wrapper -->
	
	<!-- footer -->
	<div id="footer" class="clearingfix">
 
		<div id="underfooter"></div>
				
		<!-- footer content -->
		<div class="rapidxwpr floatholder">
 
			<!-- footer credits -->
			<div class="footer-credits">
				Powered by the &nbsp;<a href="http://www.ushahidi.com/"><img src="<?php echo url::base(); ?>/media/img/footer-logo.png" alt="Ushahidi" style="vertical-align:middle" /></a>&nbsp; Platform
			</div>
			<!-- / footer credits -->
		
			<!-- footer menu -->
			<div class="footermenu">
				<ul class="clearingfix">
					<li><a class="item1" href="<?php echo url::site(); ?>"><?php echo Kohana::lang('ui_main.home'); ?></a></li>
					<li><a href="<?php echo url::site()."reports/submit"; ?>"><?php echo Kohana::lang('ui_main.submit'); ?></a></li>
					<li><a href="<?php echo url::site()."alerts"; ?>"><?php echo Kohana::lang('ui_main.alerts'); ?></a></li>
					<li><a href="<?php echo url::site()."contact"; ?>"><?php echo Kohana::lang('ui_main.contact'); ?></a></li>
					<?php 
					
					// BJH - Hard-coded adding a couple pages to the footer (ugly)
					// Custom Pages
					$links = "";
					
					$pages = ORM::factory('page')->where('page_active', '1')->find_all();
					foreach ($pages as $page)
					{
						// Exclude Sources, Analysis and Blog, Twitter & RSS tabs
						if (in_array($page->id, array(1,2,3,5)))
						{
							$links .= "<li><a href=\"".url::site()."page/index/".$page->id."\" ";
						 	$links .= ">".$page->page_tab."</a></li>";
						}
					}

					echo $links;
					
					// Action::nav_main_bottom - Add items to the bottom links
					Event::run('ushahidi_action.nav_main_bottom');
					?>
				</ul>
				<?php if($site_copyright_statement != '') { ?>
      		<p><?php echo $site_copyright_statement; ?></p>
      	<?php } ?>
			</div>
			<!-- / footer menu -->

      
			<h2 class="feedback_title" style="clear:both">
				<a href="http://feedback.ushahidi.com/fillsurvey.php?sid=2"><?php echo Kohana::lang('ui_main.feedback'); ?></a>
			</h2>

 
		</div>
		<!-- / footer content -->
 		<!-- Share this button -->
 			
 			<!-- AddThis Button BEGIN -->
			<div id="share-tb-wrp" class="no-js">
				<a href="#" id="share-btn">Share&nbsp;This&nbsp;+</a>
				<ul id="share-lst">
					<li><a class="addthis_button_facebook_like" fb:like:layout="button_count"></a></li>
					<li><a class="addthis_button_tweet"></a></li>
					<li><a class="addthis_counter addthis_pill_style"></a></li>
				</ul>			
			</div>
		<!-- AddThis Button END -->
	<!--	<div id="embed-pnl"> 
			<h2>Copy:</h2>
			<input type="text"  value="<iframe src='<?php echo url::base(); ?>' width='100%' height='100%'><p>Your browser does not support iframes.</p></iframe>" />
		</div>
 			
 	-->		
 		<!-- /Share This button -->
	</div>
	<!-- / footer -->
	
	  <!--[if lt IE 7 ]>
	    <script src="js/libs/dd_belatedpng.js"></script>
	    <script> DD_belatedPNG.fix('img, .png_bg'); </script>
	  <![endif]-->

		<?php echo $ushahidi_stats; ?>
		<?php echo $google_analytics; ?>

		<!-- Task Scheduler -->
		<!--<img src="<?php echo url::base(); ?>media/img/spacer.gif" alt="" height="1" width="1" border="0" onload="runScheduler(this)" />-->

		<?php
		// Action::main_footer - Add items before the </body> tag
		Event::run('ushahidi_action.main_footer');
		?>

	</body>
		
		
			<script type="text/javascript" src="<?php echo url::base()."themes/".Kohana::config("settings.site_style")."/media/js/plugins/jquery.colorbox-min.js"; ?>"></script>
		
		<script type="text/javascript" src="<?php echo url::base()."themes/".Kohana::config("settings.site_style")."/media/js/core.js"; ?>"></script>
		
		<script type="text/javascript">var addthis_config = {"data_track_clickback":true};</script>
		<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4d73a5be25a220fc"></script>
		
		
		
	</html>
