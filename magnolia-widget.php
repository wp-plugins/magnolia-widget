<?php
/*
Plugin Name: Ma.gnolia Widget
Plugin URI: http://www.zenofshen.com/magnolia-widget/
Description: A WordPress widget that displays Ma.gnolia bookmarks in yummy valid semantic XHTML code by parsing XML.
Version: 1.1
Author: Paul Shen
Author URI: http://www.zenofshen.com
License: GPL (http://www.gnu.org/copyleft/gpl.html)
Notes: Requires at least PHP 5. Options for the widget are username, number of bookmarks, and whether or not to show link to your Ma.gnolia page. Template functions are provided for customizability.
*/

/* Version 1.0 - initial release
 * January 23, 2008
 *
 * Version 1.1 - fixed bug in default template (previously used $pubDate instead of $timestamp)
 * February 19, 2008
 */

/* The HTML code that is displayed before the list of bookmarks. */
function topmagnoliawrapper_template() {
?>
	<li id="magnolia-sidebar" class="widget widget_magnolia">
		<h3 class="widgettitle">Ma.gnolia</h3>
		<ul id="margnolia-list">
<?php
}

/* The HTML code that is displayed for each bookmark.
 * Comes with the following variables
 * $username
 * $link
 * $title
 * $description
 * $timestamp - the UNIX timestamp of the update
 */
function magnoliabookmark_template($username, $link, $title, $description, $timestamp) {
?>
			<li class="magnolia">
				<a href="<?php echo $link; ?>" title="<?php echo $title; ?>" class="magnolia"><?php echo $title; ?></a>
				<div class="magnolia-description"><?php echo $description; ?></div>
				<div class="magnolia-meta"><?php echo date('F j \a\t g:i a', strtotime($timestamp)); ?></div></li>
			</li>
<?php
}

/* The HTML code that is displayed after the list of bookmarks. */
function bottommagnoliawrapper_template($showLink) {
?>
			<?php if ($showLink) { ?>
			<li id="magnolia-link"><a href="http://ma.gnolia.com/people/<?php echo $username; ?>" title="<?php echo $username; ?>'s Bookmarks on Ma.gnolia::Browse through the sites and pages that I have added to my bookmark list on Ma.gnolia. If you're a member, add me as a contact!">View more bookmarks on Ma.gnolia</a></li>
			<?php } ?>
		</ul>
	</li>
<?php
}

function widget_magnolia() {
	if (!$options = get_option('magnolia'))
		$options = array('username' => '', 'numBookmarks' => 3, 'showLink' => true);
	
	$username = $options['username'];
	
	$url = 'http://ma.gnolia.com/rss/lite/people/' . $username;
	
	$response = file_get_contents($url);
	
	// Create the SimpleXML object
	$bookmarks = simplexml_load_string($response);
	
	$count = 0;
	
	topmagnoliawrapper_template();
	
	foreach ($bookmarks->channel->item as $bookmark) {
		if (++$count > $options['numBookmarks'])
			break;
			
		$desc = html_entity_decode($bookmark->description);
		magnoliabookmark_template($username, $bookmark->link, $bookmark->title, $desc, $bookmark->pubDate);
	}

	bottommagnoliawrapper_template($options['showLink']);
}


function widget_magnolia_options() {
	if (!$options = get_option('magnolia'))
		$options = array('collection' => '', 'numBookmarks' => 3, 'showLink' => true);
	
	if ($_POST['magnolia-submit']) {
		$options = array('username' => $_POST['magnolia-username'], 'numBookmarks' => $_POST['magnolia-numBookmarks'], 'showLink' => $_POST['magnolia-showLink']);
		update_option('magnolia', $options);
	}
?>
	<p>Username: <input type="text" id="magnolia-username" name="magnolia-username" value="<?php echo $options['username']; ?>" /></p>
	<p>Number Bookmarks: <input type="text" id="magnolia-numBookmarks" name="magnolia-numBookmarks" value="<?php echo $options['numBookmarks']; ?>" /></p>
	<p>Show Link: <input type="checkbox" id="magnolia-showLink" name="magnolia-showLink" value="1" <?php if ($options['showLink']) echo "CHECKED"; ?> /></p>
	<input type="hidden" id="magnolia-submit" name="magnolia-submit" value="1" />
<?php
}

function magnolia_init() {
	register_sidebar_widget(__('ma.gnolia'), 'widget_magnolia');
	register_widget_control(__('ma.gnolia'), 'widget_magnolia_options', 200, 200);
}

add_action('plugins_loaded', 'magnolia_init');
?>
