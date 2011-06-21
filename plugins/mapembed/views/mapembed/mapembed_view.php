<?php
/**
 * Mapmbed view page, taken from George, taken from Ushahidi main view page
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Dashboard Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General
 * Public License (LGPL)
 */
?>

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<?php
	    echo "$theme_header";
	?>
	<script type="text/javascript">
		var addthis_config = {
		   ui_click: true
		}
		<?php echo $js . "\n"; ?>
	</script>

</head>
<body>
		<!-- map -->
		<div class="map" id="<?php echo $map_container; ?>" style="height: <?php echo $height?>px; width: <?php echo $width?>px;"></div>
		<div style="clear:both;"></div>
		<div id="mapStatus" style="width: <?php echo $width?>px">
			<div id="mapScale" style="border-right: solid 1px #999"></div>
			<div id="mapMousePosition" style="min-width: 135px;border-right: solid 1px #999;text-align: center"></div>
			<div id="mapProjection" style="border-right: solid 1px #999"></div>
			<div id="mapOutput"></div>
		</div>
		<div style="clear:both;"></div>
		<div class="slider-holder"  style="display: none;padding: 0px 0px 0px 20px; width: <?php echo $width-40?>px;">
			<form action="">
				<input type="hidden" value="0" name="currentCat" id="currentCat">
				<fieldset>
					<label for="startDate">From:</label>
					<select name="startDate" id="startDate"><?php echo $startDate; ?></select>
					<label for="endDate">To:</label>
					<select name="endDate" id="endDate"><?php echo $endDate; ?></select>
				</fieldset>
			</form>
		</div>
		<!-- / map -->

</body>
<?php /* Everyone likes a good hook */ Event::run("mapembed.main_footer"); ?>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-12099357-6']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</html>
