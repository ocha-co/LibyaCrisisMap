<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title><?php echo $site_name; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php echo $header_block; ?>
	<?php
	// Action::header_scripts - Additional Inline Scripts from Plugins
	Event::run('ushahidi_action.header_scripts');
	?>
</head>

<body id="page">


				<!-- mainmenu -->
				<div id="mainmenu" class="clearingfix">
					<ul>
						<?php nav::main_tabs($this_page); ?>
						<li><a href="<?php url::site();?>reports_download">Download</a></li>
					</ul>

				</div>
				<!-- / mainmenu -->
