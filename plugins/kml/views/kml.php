<?php 

// NOTE: SWATCH Generator required!
//  This code relies on operation of your Ushahidi instance's SWATCH image generator (http://<siteurl>/swatch/?...).  

//=== Option Variables ==
//--- File Options --
$upload_directory = url::base() . "media/uploads/";  // external URL for Ushahidi uploads directory
$kml_filename = "latest.kml";  // filename for exported KML file
$kmz_filename = "latest.kmz";  // filename for exported KMZ file
$kmlFileName = Kohana::config('upload.directory', TRUE) . $kml_filename;  // internal path to KML file in uploads directory
$kmzFileName = Kohana::config('upload.directory', TRUE)  . $kmz_filename;  // internal path to KMZ file in uploads directory
//--- Balloon Options --
$placemark_balloon_width = "340";  // width in pixels (suggest 200 to 500)
$document_balloon_width = "340";  // width in pixels (suggest 200 to 500)
$title_text_color = "black";  // HTML color (works in Google Earth, ignored in Google Maps)
$verified_text_color = "green";  // HTML color (works in Google Earth & Maps)
$description_text_color = "black";  // HTML color (works in Google Earth & Maps)
$location_text_color = "gray";		// HTML color (works in Google Eatth & Maps)
$categories_text_color = "gray";  // HTML color (works in Google Earth & Maps)
$date_text_color = "gray";  // HTML color (works in Google Earth & Maps)
$verified_in_balloons = 1; 	// 1 = show verified status in balloons, 0 = don't show
$media_in_balloons = 1;	// 1 = show media links (if available) in balloons, 0 = don't show
$location_in_balloons = 1;	// 1 = show location in balloons, 0 = don't show
$cats_in_balloons = 1;	// 1 = show categories listing in balloons, 0 = don't show
//--- Structure Options --
$folder_snippets = 0;  // 1 = show description as snippet, 0 = no snippet.
$visibility = 1;  // 1 = placemarks & folders visible on initial load, 0 = not visible
$extended_data = true;		// true = write extended data section in each placemark kml, false = No extended data
$bundle_icons = true;		// true = bundle icon images into KMZ, false = link to images on server
//--- load options array --
$options = array("upload_directory"=>$upload_directory, "kml_filename"=>$kml_filename, "kmz_filename"=>$kmz_filename, "kmlFileName"=>$kmlFileName, "kmzFileName"=>$kmzFileName, "placemark_balloon_width"=>$placemark_balloon_width, "document_balloon_width"=>$document_balloon_width, "title_text_color"=>$title_text_color, "verified_text_color"=>$verified_text_color, "description_text_color"=>$description_text_color, "location_text_color"=>$location_text_color, "categories_text_color"=>$categories_text_color, "date_text_color"=>$date_text_color, "verified_in_balloons"=>$verified_in_balloons, "media_in_balloons"=>$media_in_balloons, "location_in_balloons"=>$location_in_balloons, "cats_in_balloons"=>$cats_in_balloons, "folder_snippets"=>$folder_snippets, "visibility"=>$visibility, "extended_data"=>$extended_data, "bundle_icons"=>$bundle_icons);

//=== Caching Options ==
$cache_secs = 60; 	// seconds during which to serve cached file, after which re-generate on next request
$cache_on = true; 	// true = cache file, false = debug mode: file is re-generated on each request

//=== Logo Details == (image file for in balloons: png/jpg/gif; suggested size: 36 x 36 pixels)
$logo_path = url::base() . "plugins/kml/views/";
$logo_filename = "logo_36x36.png";
$logo_width = 36;
$logo_height = 36;
//--- load logo array --
$logo =  array("path"=>$logo_path, "filename"=>$logo_filename, "width"=>$logo_width, "height"=>$logo_height);

//=== Shared Data Variables and Arrays ==
$kml_styles = ""; 	// KML styles string for all categories
$catID_data = array();  // contains all categories, indexed by their category id
$catID_icons = array(); // contains array of icons for each category, indexed by cat ID and icon type
$cat_to_subcats = array();  // 
$catID_to_incidents = array();  // array with all category IDs, each with array of related incidents

//process_categories($kmlFile, $categories, $catID_icons, $kml_styles, $catID_data, $cat_to_subcats, $options);

//=============================================================================================
// Variables above, Action Functions below
//=============================================================================================

//=== function to write KML header ==
function write_kml_head($kmlFile, $kml_name, $kml_tagline, $options) {
	$kml_head =	"" . 
	"<?xml version='1.0' encoding='UTF-8'?>" . PHP_EOL .
	"<kml xmlns='http://www.opengis.net/kml/2.2' xmlns:gx='http://www.google.com/kml/ext/2.2' xmlns:kml='http://www.opengis.net/kml/2.2' xmlns:atom='http://www.w3.org/2005/Atom'>" . PHP_EOL .
	"	<Document>" . PHP_EOL . 
	"		<name><![CDATA[" . $kml_name . "]]></name>" . PHP_EOL . 
	"		<snippet maxLines='0'></snippet>" . PHP_EOL .
	"		<open>1</open>" . PHP_EOL .
	"		<description>" . PHP_EOL .
	"			<![CDATA[<table width='" . $options["document_balloon_width"] . "' cellpadding='0' cellspacing='0'><tr><td>" . PHP_EOL .
	"			<p><strong>" . $kml_tagline . "</strong></p>" . PHP_EOL .
	"			<a href='" . url::base() . "'>" . url::base() . "</a><br />" . PHP_EOL .
	"			<p>Note: Reports are represented by multiple placemarks if they are in multiple categories.</p>" . PHP_EOL .
	"			<p><font color='" . $options["date_text_color"] . "'><strong>This kml last updated</strong>: " . gmdate("D, d M Y H:i:s") . " GMT</font></p>" . PHP_EOL .
	//"			<p>Static KML file for offline use: <a href='" . $options["upload_directory"] . $options["kmz_filename"] . "'>" . $options["kmz_filename"] . "</a></p>" . PHP_EOL .
	"			<hr />" . PHP_EOL .
	"			<table width='100%' cellpadding='0' cellspacing='0'><tr><td align='left'>" . PHP_EOL .
	"				<img src='" . url::base() . "plugins/kml/views/logo_36x36.png' width='36' height='36' />" . PHP_EOL .
	"			</td><td align='right'>" . PHP_EOL .
	"				<a href='" . url::base() . "'><strong>" . url::base() . "</strong></a><br />" . PHP_EOL .
	"				<a href='" . url::base() . "reports/submit/'>Submit a new report</a>" . PHP_EOL .
	"			</td></tr></table>" . PHP_EOL .
	"			</td></tr></table>]]>" . PHP_EOL .								
	"		</description>" . PHP_EOL .
	"		<styleUrl>#style_top_document</styleUrl>" . PHP_EOL .
	"		<Style id='style_top_document'>" . PHP_EOL .
	"			<ListStyle>" . PHP_EOL .
	"				<ItemIcon>" . PHP_EOL .
	"					<href>" . htmlspecialchars(url::base() . "plugins/kml/views/logo_36x36.png") . "</href>" . PHP_EOL .
	"				</ItemIcon>" . PHP_EOL .
	"				<maxSnippetLines>1</maxSnippetLines>" . PHP_EOL .
	"			</ListStyle>" . PHP_EOL .
	"			<BalloonStyle>" . PHP_EOL .
	"				<text><![CDATA[<html><body><h2><font color='" . $options["title_text_color"] . "'>$[name]</font></h2>$[description]</body></html>]]></text>" . PHP_EOL .
	"			</BalloonStyle>" . PHP_EOL .
	"		</Style>" . PHP_EOL;	
	fwrite($kmlFile, $kml_head);
	return true;
}

//=== function to generate StyleMap and Styles for one category's placemarks ==
function generate_style($category, $catID_icons, $options) {
	$kml_style = "" .
	"		<StyleMap id='stylemap_categoryID_" . $category->id . "'>" . PHP_EOL .
	"			<Pair>" . PHP_EOL .
	"				<key>normal</key>" . PHP_EOL .
	"				<styleUrl>#style_categoryID_" . $category->id . "_n</styleUrl>" . PHP_EOL .
	"			</Pair>" . PHP_EOL .
	"			<Pair>" . PHP_EOL .
	"				<key>highlight</key>" . PHP_EOL .
	"				<styleUrl>#style_categoryID_" . $category->id . "_h</styleUrl>" . PHP_EOL .
	"			</Pair>" . PHP_EOL .
	"		</StyleMap>" . PHP_EOL .
	"		<Style id='style_categoryID_" . $category->id . "_n'>" . PHP_EOL .
	"			<IconStyle>" . PHP_EOL .
	"				<scale>1.0</scale>" . PHP_EOL .
	"				<Icon>" . PHP_EOL .
	"					<href>" . $catID_icons[$category->id]["placemark"] . "</href>" . PHP_EOL .
	"				</Icon>" . PHP_EOL .
	"				<hotSpot x='0.5' y='0.5' xunits='fraction' yunits='fraction' />" . PHP_EOL .
	"			</IconStyle>" . PHP_EOL .
	"			<LabelStyle>" . PHP_EOL .
	"				<scale>0</scale>" . PHP_EOL .
	"			</LabelStyle>" . PHP_EOL .
	"			<BalloonStyle>" . PHP_EOL .
	"				<text><![CDATA[<html><body><h2><font color='" . $options["title_text_color"] . "'>$[name]</font></h2>$[description]</body></html>]]></text>" . PHP_EOL .
	"			</BalloonStyle>" . PHP_EOL .
	"		</Style>" . PHP_EOL .
	"		<Style id='style_categoryID_" . $category->id . "_h'>" . PHP_EOL .
	"			<IconStyle>" . PHP_EOL .
	"				<scale>1.2</scale>" . PHP_EOL .		
	"				<Icon>" . PHP_EOL .
	"					<href>" . $catID_icons[$category->id]["placemark"] . "</href>" . PHP_EOL .
	"				</Icon>" . PHP_EOL .
	"				<hotSpot x='0.5' y='0.5' xunits='fraction' yunits='fraction' />" . PHP_EOL .
	"			</IconStyle>" . PHP_EOL .
	"			<LabelStyle>" . PHP_EOL .
	"				<scale>1</scale>" . PHP_EOL .
	"			</LabelStyle>" . PHP_EOL .
	"			<BalloonStyle>" . PHP_EOL .
	"				<text><![CDATA[<html><body><h2><font color='" . $options["title_text_color"] . "'>$[name]</font></h2>$[description]</body></html>]]></text>" . PHP_EOL .
	"			</BalloonStyle>" . PHP_EOL .
	"		</Style>" . PHP_EOL;
	return $kml_style;
}

//=== function to generate Style for a folder for one category ==
function generate_folder_style($category, $catID_icons, $options) {
	$kml_style = "" .
	"		<Style id='style_categoryID_" . $category->id . "_folder'>" . PHP_EOL .
	"			<ListStyle>" . PHP_EOL .
	"				<ItemIcon>" . PHP_EOL .
	"					<href>" . $catID_icons[$category->id]["folder"] . "</href>" . PHP_EOL .
	"				</ItemIcon>" . PHP_EOL .
	"				<maxSnippetLines>" . $options["folder_snippets"] . "</maxSnippetLines>" . PHP_EOL .
	"			</ListStyle>" . PHP_EOL .
	"		</Style>" . PHP_EOL;
	return $kml_style;
}

//=== function to write Folder header for one category ==
function write_folder_head($kmlFile, $category, $options) {
	// check if category description is same as category title, or is blank
	if ($category->category_title == $category->category_description || $category->category_description == "") {
		// if so, make snippet blank
		$category_snippet = "<snippet maxLines='0'></snippet>";
	}
	else {
		// if not, make snippet contain category description
		$category_snippet = "<snippet maxLines='1'>" . $category->category_description . "</snippet>";
	}

	$kml_folder_head = "" . 
	"		<Folder id='folder_categoryID_" . $category->id . "'>" . PHP_EOL .
	"			<name><![CDATA[" . $category->category_title . "]]></name>" . PHP_EOL .
	"			" . $category_snippet . PHP_EOL .
	"			<visibility>" . $options["visibility"] . "</visibility>" . PHP_EOL .
	"			<open>0</open>" . PHP_EOL .
	"			<styleUrl>#style_categoryID_" . $category->id . "_folder</styleUrl>" . PHP_EOL;
	fwrite($kmlFile, $kml_folder_head);
	return true;
}

//=== function to write placemark for one item ==
//function write_placemark($kmlFile, $item, $cat_id, $categories_string, $logo, $options) {
function write_placemark($kmlFile, $item, $cat_id, $catID_data, $catID_icons, $logo, $options) {

	// Populate verified string (if option is set and item is verified)
	$verified_string = "";
	if ($options["verified_in_balloons"] == 1 && $item->incident_verified == 1) {
		$verified_string = "" . 
		"					<font color='" . $options["verified_text_color"] . "'><strong>Verified</strong></font><br />" . PHP_EOL;
	}
	// Populate media link strings (if option is set)
	$media_string = "";
	$item_media = get_item_media($item);
	if($options["media_in_balloons"] == 1 && strlen($item_media["media_string"]) != 0) {
		$media_string = "					" . $item_media["media_string"] . "<br />" . PHP_EOL;
	}
	// Populate location string (if option is set)
	$location_string = "";
	if ($options["location_in_balloons"] == 1) {
		$location_string = "					<font color='" . $options["location_text_color"] . "'><strong>Location:</strong> " . htmlspecialchars($item->location->location_name) . "</font><br />" . PHP_EOL;
	}
	// Populate categories strings (if option is set)
	$categories_string = "";
	$categories_html = "";
	if($options["cats_in_balloons"] == 1) {
		$categories_string = generate_categories_string($item, $catID_data, $catID_icons, $options);
		$categories_html = "					<font color='" . $options["categories_text_color"] . "'>" . $categories_string . "</font>" . PHP_EOL;
	}

	
	$kml_placemark = "" .
	"			<Placemark id='placemark_report_" . $item->id . "_cat_" . $cat_id . "'>" . PHP_EOL .
	"				<name><![CDATA[" . htmlspecialchars($item->incident_title) . "]]></name>" . PHP_EOL .
	"				<snippet maxLines='0'></snippet>" . PHP_EOL .
	"				<visibility>" . $options["visibility"] . "</visibility>" . PHP_EOL .
	"				<styleUrl>#stylemap_categoryID_" . $cat_id . "</styleUrl>" . PHP_EOL .
	"				<description>" . PHP_EOL .
	"					<![CDATA[<table width='" . $options["placemark_balloon_width"] . "' cellpadding='0' cellspacing='0'><tr><td>" . PHP_EOL .
						$verified_string .
	"					<font color='" . $options["description_text_color"] . "'>" . htmlspecialchars(text::limit_words($item->incident_description, 50, "...")) . "</font><br />" . PHP_EOL .
						$media_string .
						$location_string .
						$categories_html .
	"					<table width='100%' cellpadding='0' cellspacing='0'><tr><td align='left'>" . PHP_EOL .
	"						<font color='" . $options["date_text_color"] . "'><strong>Submitted:</strong> " . $item->incident_date . "</font>" . PHP_EOL .
	"					</td><td align='right'>" . PHP_EOL .
	"						<a href='" . url::base().'reports/view/'.$item->id . "'>More Information</a>" . PHP_EOL .
	"					</td></tr></table>" . PHP_EOL .
	"					<hr />" . PHP_EOL .
	"					<table width='100%' cellpadding='0' cellspacing='0'><tr><td align='left'>" . PHP_EOL .
	"						<img src='" . $logo["path"] . $logo["filename"] . "' width='" . $logo["width"] . "' height='" . $logo["height"] . "' />" . PHP_EOL .
	"					</td><td align='right'>" . PHP_EOL .
	"						<a href='" . url::base() . "'><strong>" . url::base() . "</strong></a><br />" . PHP_EOL .
	"						<a href='" . url::base() . "reports/submit/'>Submit a new report</a>" . PHP_EOL .
	"					</td></tr></table>" . PHP_EOL .
	"					</td></tr></table>]]>" . PHP_EOL .
	"				</description>" . PHP_EOL .
					generate_extended_data($item, $cat_id, $categories_string, $options) .
	"				<Point>" . PHP_EOL .
	"					<coordinates>" . $item->location->longitude . "," . $item->location->latitude . "</coordinates>" . PHP_EOL .
	"				</Point>" . PHP_EOL .
	"			</Placemark>" . PHP_EOL;
	fwrite($kmlFile, $kml_placemark);
	return true;
}

//=== function to generate Extended Data section (if enabled in options) ==
function generate_extended_data($item, $cat_id, $categories_string, $options) {
	$kml_extended_data = "";
	if ($options["extended_data"]) {
		$kml_extended_data = "" .
		"				<ExtendedData>" . PHP_EOL .
		"					<Data name='report_id'><value><![CDATA[" . $item->id . "]]></value></Data>" . PHP_EOL .
		"					<Data name='report_title'><value><![CDATA[" . htmlspecialchars($item->incident_title) . "]]></value></Data>" . PHP_EOL .
		"					<Data name='report_description'><value><![CDATA[" . htmlspecialchars(text::limit_words($item->incident_description, 50, "...")) . "]]></value></Data>" . PHP_EOL .
		"					<Data name='report_date'><value><![CDATA[" . $item->incident_date . "]]></value></Data>" . PHP_EOL .
		"					<Data name='report_url'><value><![CDATA[" . url::base() . "reports/view/" . $item->id . "]]></value></Data>" . PHP_EOL .
		"					<Data name='report_category'><value><![CDATA[" . $cat_id . "]]></value></Data>" . PHP_EOL .
		"					<Data name='report_categories_string'><value><![CDATA[" . $categories_string . "]]></value></Data>" . PHP_EOL .
		"					<Data name='report_location_string'><value><![CDATA[" . $item->location->location_name . "]]></value></Data>" . PHP_EOL .
		"					<Data name='report_latitude'><value><![CDATA[" . $item->location->latitude . "]]></value></Data>" . PHP_EOL .
		"					<Data name='report_longitude'><value><![CDATA[" . $item->location->longitude . "]]></value></Data>" . PHP_EOL .
		"				</ExtendedData>" . PHP_EOL;
	}
	return $kml_extended_data;
}

//=== function to write Folder footer for one category ==
function write_folder_foot($kmlFile) {
	$kml_folder_foot = "" . 
	"		</Folder>" . PHP_EOL;
	fwrite($kmlFile, $kml_folder_foot);
	return true;
}

//=== function to write KML footer ==
function write_kml_foot($kmlFile) {
	$kml_foot = "" .
	"	</Document>" . PHP_EOL .
	"</kml>" . PHP_EOL;
	fwrite($kmlFile, $kml_foot);
	return true;
}

//=== function to generate media string for inclusion in placemark balloon ==
function get_item_media($item) {
	$item_media = array();
	$item_media_string = "";
	$close_table = false;
	$previous_item = false;
	foreach ($item->media as $media) {
		switch ($media->media_type) {
			case 1:
				$item_media["image"] = $media->media_link;
				$item_media["image_medium"] = $media->media_medium;
				$item_media["image_thumb"] = $media->media_thumb;
				if ($previous_item) $item_media_string .= "&nbsp;&nbsp;";
				$item_media_string .= "<table width='100%'><tr><td align='left'><img src='" . url::base() . "media/uploads/" . $media->media_thumb . "' /></td><td align='left' width='100%'>";
				$item_media_string .= "<a href='" . url::base() . "media/uploads/" . $media->media_link . "'>Image</a>";
				$close_table = true;
				$previous_item = true;
				break;
			case 2:
				$item_media["video"] = $media->media_link;
				if ($previous_item) $item_media_string .= "&nbsp;&nbsp;";
				$item_media_string .= "<a href='" . $media->media_link . "'>Video</a>";
				$previous_item = true;
				break;
			case 3:
				$item_media["audio"] = $media->media_link;
				if ($previous_item) $item_media_string .= "&nbsp;&nbsp;";
				$item_media_string .= "<a href='" . $media->media_link . "'>Audio</a>";
				$previous_item = true;
				break;
			case 4:
				$item_media["news"] = $media->media_link;
				if ($previous_item) $item_media_string .= "&nbsp;&nbsp;";
				$item_media_string .= "<a href='" . $media->media_link . "'>News Source</a>";
				$previous_item = true;
				break;
			case 5:
				$item_media["podcast"] = $media->media_link;
				if ($previous_item) $item_media_string .= "&nbsp;&nbsp;";
				$item_media_string .= "<a href='" . $media->media_link . "'>Podcast</a>";
				$previous_item = true;
				break;
		}
	}
	if ($close_table) $item_media_string .= "</td></tr></table>";
	$item_media["media_string"] = $item_media_string;
	return $item_media;
}

//=== function to generate Categories String for inclusion in placemark balloon ==
function generate_categories_string($item, $catID_data, $catID_icons, $options) {
	$categories_string = "";

	// Count visible categories
	$catCount = 0;
	foreach ($item->category as $item_category) {
		if($item_category->category_visible == 1) {
			$catCount++;
		}
	}
	
	// Check if there's no category defined
	if ($catCount == 0) {
		$categories_string = "<strong>No Category Selected</strong>";
	}
	// check if there's only one category
	elseif ($catCount == 1) {
		// Iterate through categories 
		foreach ($item->category as $item_category) {
			// Check that it's a visible category
			if ($item_category->category_visible == 1) {
				// If it's a top level category, write simple categories string
				if ($item_category->parent_id == 0) {
					$categories_string = "<strong>Category:</strong> <img src='" . $catID_icons[$item_category->id]["cat_string"] . "' width='10' height='10' /> " . $item_category->category_title;
				}
				// If it's a sub category, write categories string with parent and sub
				else {
					$categories_string = "<strong>Category:</strong>";
					$categories_string .= "<br /><img src='" . $catID_icons[$item_category->parent_id]["cat_string"] . "' width='10' height='10' /> " . $catID_data[$item_category->parent_id]->category_title;
					$categories_string .= "<br />&nbsp;&nbsp;&nbsp;<img src='" . $catID_icons[$item_category->id]["cat_string"] . "' width='10' height='10' /> " . $item_category->category_title;
				}
			}
		}
	}
	// If more than one category, then iterate to write parent and sub categories as needed
	else {
		$categories_string = "<strong>Categories (" . $catCount . "):</strong> ";
		$previous_parentcat_id = -1;
		foreach ($item->category as $item_category) {
			// Check that it's a visible category
			if ($item_category->category_visible == 1) {
				
				if ($item_category->parent_id == 0) {
					$categories_string .= "<br /> <img src='" . $catID_icons[$item_category->id]["cat_string"] . "' width='10' height='10' /> " . $item_category->category_title;
				}
				else {
					// If it's the first sub with this parent category, write parent and sub categories
					if ($previous_parentcat_id != $item_category->parent_id) {
						$categories_string .= "<br /> <img src='" . $catID_icons[$item_category->parent_id]["cat_string"] . "' width='10' height='10' /> " . $catID_data[$item_category->parent_id]->category_title;
						$categories_string .= "<br />&nbsp;&nbsp;&nbsp;<img src='" . $catID_icons[$item_category->id]["cat_string"] . "' width='10' height='10' /> " . $item_category->category_title;
					}
					// otherwise, only write new sub category
					else {
						$categories_string .= "<br />&nbsp;&nbsp;&nbsp;<img src='" . $catID_icons[$item_category->id]["cat_string"] . "' width='10' height='10' /> " . $item_category->category_title;
					}
					// update previous parent id to track for next category
					$previous_parentcat_id = $item_category->parent_id;	
				}
			}
		}
	}
	return $categories_string;
}

//=== function to zip KML into KMZ file
function create_kmz($kmlFileName, $kmzFileName){

	kohana::log('info', "generating kmz file");
	$zip = new ZipArchive();

	if ($zip->open("$kmzFileName", ZIPARCHIVE::CREATE)!==TRUE) {
		kohana::log('error', "cannot open kmz file");
		echo("cannot open <". $kmzFileName .">\n");
	}

	kohana::log('info', "adding kml to kmz file");
	$zip->addFile($kmlFileName, "doc.kml");
	//$zip->addFile("plugins/kml2/views/circle_border.png", "files/circle_border.png");
	$zip->close();
	kohana::log('info', "closed kmz file");
	return $zip;
}


//=============================================================================================
// Action Functions above, Logic Functions below
//=============================================================================================

//=== Function to Process Categories ==
//    ...(to build icons array, generate kml styles, and build data arrays for categories and subcat mapping)
function process_categories($kmlFile, $categories, &$catID_icons, &$kml_styles, &$catID_data, &$cat_to_subcats, $options) {
	// Iterate through categories...
	foreach ($categories as $cat) {
		// Write array of catIDs to icons
		$cat_icons = array();
		// if category image is set, then construct icon URLs using image and add to relevant array
		if(isset($cat->category_image)) {
			// if so, use it for the icon
			$cat_icons["placemark"] = htmlspecialchars(url::base().'media/uploads/'.$cat->category_image);
			$cat_icons["folder"] = htmlspecialchars(url::base().'media/uploads/'.$cat->category_image);
			$cat_icons["cat_string"] = htmlspecialchars(url::base().'media/uploads/'.$cat->category_image_thumb);
		}
		// otherwise, construct icons using swatch generator
		else {
			// otherwise, use a color swatch
			$cat_icons["placemark"] = htmlspecialchars(url::base().'swatch/?t=cir&c='.$cat->category_color.'&b=000000&w=32&h=32');
			$cat_icons["folder"] = htmlspecialchars(url::base().'swatch/?t=rec&c='.$cat->category_color.'&b=000000&w=32&h=32');
			$cat_icons["cat_string"] = htmlspecialchars(url::base().'swatch/?t=rec&c='.$cat->category_color.'&b=000000&w=10&h=10');
		}
		$catID_icons[$cat->id] = $cat_icons;
		
		// Write KML styles for category's placemarks and folder
		$kml_styles .= generate_style($cat, $catID_icons, $options);
		$kml_styles .= generate_folder_style($cat, $catID_icons, $options);
		
		// Generate/Fill category data array (for easy cat data retrieval by cat id)
		$catID_data[$cat->id] = $cat;
		
		//== Generate & Fill cat to subcat mapping array (index = top-level cat id, value array contains subcats)
		// check if top-level category
		if($cat->parent_id == 0) {
			// if first time with this top-level category
			if(!isset($cat_to_subcats[$cat->id])) {
				// initialize mapping: write blank array for top-level category
				$cat_to_subcats[$cat->id] = array();
			}
		}
		// if not top-level category
		else {
			// if first time with this cat's parent category
			if(!isset($cat_to_subcats[$cat->parent_id])) {
				// initialize mapping: write blank array for subcategory
				$cat_to_subcats[$cat->parent_id] = array();
			}
			// this is a sub-category, map it to its parent category
			array_push($cat_to_subcats[$cat->parent_id], $cat);
		}
	}
	return true;
}


//=== Function with folder and placemark generation logic
function write_kml_data($kmlFile, $items, $catID_to_incidents, $cat_to_subcats, $catID_data, $catID_icons, $logo, $options) {

	//=== Iterate through incidents (build arrays of incidents in each category)
	foreach($items as $incident) {
		// for each category (and subcategory) that this incident belongs to (they are all in one array):
		foreach($incident->category as $cat) {
			// Check that it's a visible category
			if ($cat->category_visible == '1') {

				// Check if it's the first time we've seen this category
				if(!isset($catID_to_incidents[$cat->id])) {
					// if so, initialize mapping: write blank array for category
					$catID_to_incidents[$cat->id] = array();
				}
				// add incident to array of incidents for the category ID
				array_push($catID_to_incidents[$cat->id], $incident);
			}
		} 
	}
	
	//=== Iterate through top-level categories  (make array of subcategories)
	foreach ($cat_to_subcats as $cat_id => $subcats) {

		// For each top-level category, write folder header
		write_folder_head($kmlFile, $catID_data[$cat_id], $options);
		// Iterate through subcategories (if any) for that top-level category
		foreach ($subcats as $subcat) {
			// For each subcategory, write folder header
			write_folder_head($kmlFile, $subcat, $options);

			// If this subcategory has one or more incidents tagged with it...
			if(isset($catID_to_incidents[$subcat->id])) {
				// then iterate through incidents (if any) attached to that cat ID
				foreach ($catID_to_incidents[$subcat->id] as $item) {
					// write incident/item's placemark
					write_placemark($kmlFile, $item, $subcat->id, $catID_data, $catID_icons, $logo, $options);
				} 
			}
			// Write folder footer for the sub category
			write_folder_foot($kmlFile);
		}
		// If the parent category has one or more incidents tagged with it...
		if(isset($catID_to_incidents[$cat_id])) {
			// then iterate through incidents attached to that cat ID
			foreach($catID_to_incidents[$cat_id] as $item) {
				// write incident/item's placemark
				write_placemark($kmlFile, $item, $cat_id, $catID_data, $catID_icons, $logo, $options);
			} 
		}
		// Write folder footer for top-level category
		write_folder_foot($kmlFile);
	}
}


//=============================================================================================
// Generate and Write KML and KMZ files to uploads directory
//=============================================================================================

// TOD: Add something to check if new incidents have come in and re-create files only if needed?

// If file was modified in last X=$cache_time seconds (and debug mode off), don't bother re-generating
if (file_exists($kmzFileName) && (time() - filemtime($kmzFileName) < $cache_secs) && $cache_on) {
//if (file_exists($kmzFileName) && (time() - filemtime($kmzFileName) < $cache_secs) && $debug_cache_off = false) {
	kohana::log('info', "returning cached kmz");
}
else {
	// older, so generate a new one	
	kohana::log('info', "generating new kml and kmz files");
	$kmlFile = fopen($kmlFileName, "w");
	if (flock($kmlFile, LOCK_EX)) { // do an exclusive lock
		kohana::log('info', "Got lock on $kmlFileName");
		
		process_categories($kmlFile, $categories, $catID_icons, $kml_styles, $catID_data, $cat_to_subcats, $options);
		
		write_kml_head($kmlFile, $kml_name, $kml_tagline, $options);
		
		fwrite($kmlFile, $kml_styles);
		
		write_kml_data($kmlFile, $items, $catID_to_incidents, $cat_to_subcats, $catID_data, $catID_icons, $logo, $options);
		
		write_kml_foot($kmlFile);
		
		flock($kmlFile, LOCK_UN); // release the lock
		fclose($kmlFile);
		kohana::log('info', " ...locked and closed $kmlFileName");

		$kmz = create_kmz($kmlFileName, $kmzFileName);
	} else {
		kohana::log('error', "Couldn't lock $kmlFileName");
	}
}


//=== Read file out to user ==
//readfile($kmlFileName);
readfile($kmzFileName);


//=== TODO ===================
// change some more options from 1/0 to true/false (where applicable)
// add code to bundle icons in KMZ if option is set
// add option and code to bundle media image thumbnials into KMZ
// Check GMaps functionality
// check on category image transparency??

//=== Later...
// only write new if new incidents added
// Scheduler??
// Generate Legend?  
// Internationalization?
// Auto limits on items retrieved/shown?
// Option for radio buttons?
// Option selection page or file?
// Add to API
// Make x.kml and x.kmz and x_nl.kml available as files
// GeoSiteMap!
// Check reliability and future plans for Swatch generator

?>
