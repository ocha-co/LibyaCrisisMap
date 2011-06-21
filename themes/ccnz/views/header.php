<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">  

<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title><?php echo $site_name; ?></title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<?php echo $header_block; ?>
	
	<?php
	// Action::header_scripts - Additional Inline Scripts from Plugins
	Event::run('ushahidi_action.header_scripts');
	?>
  <link href="http://fonts.googleapis.com/css?family=Droid+Sans:regular,bold" rel="stylesheet">	
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

</head>

<body id="page">
	<!-- wrapper -->
	<div class="rapidxwpr floatholder">

		<!-- header -->
		<div id="header">

			<!-- searchbox -->
			<div id="searchbox">
				<ul><li>SEARCH</li></ul>
			<!-- searchform -->
				<?php echo $search; ?>
				<!-- / searchform -->

			</div>
			<!-- / searchbox -->
			
			<!-- logo -->
			<div id="logo">
				<h1><a href="/"><?php echo $site_name; ?></a></h1>
				<span><?php echo $site_tagline; ?></span>
				<a href="http://ochaonline.un.org" target="_blank"><img src="/themes/ccnz/images/unocha_hor_sm.png"></a>
			</div>
			<!-- / logo -->
			
			<!-- submit incident -->
			<?php // echo $submit_btn; ?>
			<!-- / submit incident -->
			
		</div>
		<!-- / header -->

		<!-- main body -->
		<div id="middle">
			<div class="background layoutleft">

				<!-- mainmenu -->
				<div id="mainmenu" class="clearingfix">
					<ul>
						<?php
						
					$menu = "";
					$dontshow = array();

					// Home
					if( ! in_array('home',$dontshow))
					{
						$menu .= "<li><a href=\"".url::site()."main\" ";
						$menu .= ($this_page == 'home') ? " class=\"active\"" : "";
					 	$menu .= ">".Kohana::lang('ui_main.home')."</a></li>";
					 }
					
					// BJH - Output menu then fire the event to add Big Map, then continue
					echo $menu;
					$menu = "";

					Event::run('ushahidi_action.nav_main_top', $this_page);

					// Reports List
					if( ! in_array('reports',$dontshow))
					{
						$menu .= "<li><a href=\"".url::site()."reports\" ";
						$menu .= ($this_page == 'reports') ? " class=\"active\"" : "";
					 	$menu .= ">".Kohana::lang('ui_main.reports')."</a></li>";
					 }

					// Reports Submit
					if( ! in_array('reports_submit',$dontshow))
					{
						if (Kohana::config('settings.allow_reports'))
						{
							$menu .= "<li><a href=\"".url::site()."reports/submit\" ";
							$menu .= ($this_page == 'reports_submit') ? " class=\"active\"":"";
						 	$menu .= ">".Kohana::lang('ui_main.submit')."</a></li>";
						}
					}

					// Alerts
					if( ! in_array('alerts',$dontshow))
					{
						$menu .= "<li><a href=\"".url::site()."alerts\" ";
						$menu .= ($this_page == 'alerts') ? " class=\"active\"" : "";
					 	$menu .= ">".Kohana::lang('ui_main.alerts')."</a></li>";
					 }

					// Contacts
					if( ! in_array('contact',$dontshow))
					{
						if (Kohana::config('settings.site_contact_page'))
						{
							$menu .= "<li><a href=\"".url::site()."contact\" ";
							$menu .= ($this_page == 'contact') ? " class=\"active\"" : "";
						 	$menu .= ">".Kohana::lang('ui_main.contact')."</a></li>";	
						}
					}

					// Custom Pages
					$pages = ORM::factory('page')->where('page_active', '1')->find_all();
					foreach ($pages as $page)
					{
						// Exclude Sources, Analysis and Blog, Twitter & RSS tabs
						if (!in_array($page->id, array(1,2,3,5)))
						{
							$menu .= "<li><a href=\"".url::site()."page/index/".$page->id."\" ";
							$menu .= ($this_page == 'page_'.$page->id) ? " class=\"active\"" : "";
						 	$menu .= ">".$page->page_tab."</a></li>";
						}
					}
					
					// GC - hard coding reports_download

					$menu .= "<li><a href=\"".url::site()."reports_download\">Download Reports</a>";

					echo $menu;

					// Action::nav_admin_reports - Add items to the admin reports navigation tabs						
						
						?>
					</ul>

				</div>
				<!-- / mainmenu -->
