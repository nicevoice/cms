<?php
/**
* @version $Id: admin.categories.php,v 1.3 2004/10/02 17:10:48 mibi Exp $
* @package Mambo_4.5.1
* @copyright (C) 2000 - 2004 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

require_once( $mainframe->getPath( 'admin_html' ) );

// get parameters from the URL or submitted form
$section = mosGetParam( $_REQUEST, 'section', '' );
$cid = mosGetParam( $_REQUEST, 'cid', array(0) );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case "new":
		editCategory( 0, $section );
		break;

	case "edit":
		editCategory( intval( $cid[0] ) );
		break;

	case "moveselect":
		moveCategorySelect( $option, $cid, $section );
		break;

	case "movesave":
		moveCategorySave( $option, $cid, $section );
		break;

	case "copyselect":
		copyCategorySelect( $option, $cid, $section );
		break;

	case "copysave":
		copyCategorySave( $option, $cid, $section );
		break;

	case "save":
		saveCategory();
		break;

	case "remove":
		removeCategories( $section, $cid );
		break;

	case "publish":
		publishCategories( $section, $id, $cid, 1 );
		break;

	case "unpublish":
		publishCategories( $section, $id, $cid, 0 );
		break;

	case "cancel":
		cancelCategory();
		break;

	case "orderup":
		orderCategory( $cid[0], -1 );
		break;

	case "orderdown":
		orderCategory( $cid[0], 1 );
		break;

	case "accesspublic":
		accessMenu( $cid[0], 0, $option, $section );
		break;

	case "accessregistered":
		accessMenu( $cid[0], 1, $option, $section );
		break;

	case "accessspecial":
		accessMenu( $cid[0], 2, $option, $section );
		break;

	case "menulink":
		menuLink( $option );
		break;

	default:
		showCategories( $section, $option );
		break;
}

/**
* Compiles a list of categories for a section
* @param string The name of the category section
* @param string The name of the current user
*/
function showCategories( $section, $option ) {
	global $database, $my, $mainframe, $mosConfig_list_limit;
    global $adminLanguage;
	$sectionid = $mainframe->getUserStateFromRequest( "sectionid{$option}{$section}", 'sectionid', 0 );
	$limit = $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{$section}limitstart", 'limitstart', 0 );

	$section_name = '';
	if (intval( $section ) > 0) {
		$table = 'content';

		$database->setQuery( "SELECT name FROM #__sections WHERE id='$section'" );
		$section_name = $database->loadResult();
		$section_name .= ' Section';
		$where = "\n WHERE c.section='$section'";
		$type = 'content';
	} else if (strpos( $section, 'com_' ) === 0) {
		$table = substr( $section, 4 );

		$database->setQuery( "SELECT name FROM #__components WHERE link='option=$section'" );
		$section_name = $database->loadResult();
		$where = "\n WHERE c.section='$section'";
		$type = 'other';
	} else {
		$table = $section;
		$where = "\n WHERE c.section='$section'";
		$type = 'other';
	}

	$content_add = '';
	$content_join = '';
	$order = "\n ORDER BY c.ordering, c.name";

	// get the total number of records
	$database->setQuery( "SELECT count(*) FROM #__categories WHERE section='$section'" );
	$total = $database->loadResult();

	// allows for viweing of all content categories
	if ( $section == 'content' ) {
		$table = 'content';
		$content_add = "\n , z.title AS section_name";
		$content_join = "\n LEFT JOIN #__sections AS z ON z.id = c.section";
		//$where = "\n WHERE s1.catid = c.id";
		$where = "\n WHERE c.section NOT LIKE '%com_%'";
		$order = "\n ORDER BY c.section, c.ordering, c.name";
		$section_name = 'All Content';
		// get the total number of records
		$database->setQuery( "SELECT count(*) FROM #__categories INNER JOIN #__sections AS s ON s.id = section" );
		$total = $database->loadResult();
		$type = 'content';
	}

	// used by filter
	if ( $sectionid > 0 ) {
		$filter = "\n AND c.section = '$sectionid'";
	} else {
		$filter = '';
	}

	require_once( $GLOBALS['mosConfig_absolute_path'] . '/administrator/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	$query = "SELECT  c.*, c.checked_out as checked_out_contact_category, g.name AS groupname, u.name AS editor,"
	. "COUNT(DISTINCT s2.checked_out) AS checked_out"
	. $content_add
	. "\n FROM #__categories AS c"
	. "\n LEFT JOIN #__users AS u ON u.id = c.checked_out"
	. "\n LEFT JOIN #__groups AS g ON g.id = c.access"
	//. "\n LEFT JOIN #__$table AS s1 ON s1.catid = c.id"
	. "\n LEFT JOIN #__$table AS s2 ON s2.catid = c.id AND s2.checked_out > 0"
	. $content_join
	. $where
	. $filter
	. "\n AND c.published != -2"
	. "\n GROUP BY c.id"
	. $order
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit"
	;
	$database->setQuery( $query );

	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	$count = count( $rows );
	// number of Active Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__content AS a"
		. "\n WHERE a.catid = ". $rows[$i]->id
		. "\n AND a.state <> '-2'"
		;
		$database->setQuery( $query );
		$active = $database->loadResult();		
		$rows[$i]->active = $active;
	}
	// number of Trashed Items
	for ( $i = 0; $i < $count; $i++ ) {
		$query = "SELECT COUNT( a.id )"
		. "\n FROM #__content AS a"
		. "\n WHERE a.catid = ". $rows[$i]->id
		. "\n AND a.state = '-2'"
		;
		$database->setQuery( $query );
		$trash = $database->loadResult();		
		$rows[$i]->trash = $trash;
	}

	// get list of sections for dropdown filter
	$javascript = 'onchange="document.adminForm.submit();"';
	$lists['sectionid']			= mosAdminMenus::SelectSection( 'sectionid', $sectionid, $javascript );

	categories_html::show( $rows, $section, $section_name, $my->id, $pageNav, $lists, $type );
}

/**
* Compiles information to add or edit a category
* @param string The name of the category section
* @param integer The unique id of the category to edit (0 if new)
* @param string The name of the current user
*/
function editCategory( $uid=0, $section='' ) {
	global $database, $my, $adminLanguage;
	global $mosConfig_absolute_path, $mosConfig_live_site;

	$type = mosGetParam( $_REQUEST, 'type', '' );
	$redirect = mosGetParam( $_POST, 'section', '' );;

	$row = new mosCategory( $database );
	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->checked_out && $row->checked_out <> $my->id) {
		mosRedirect( 'index2.php?option=categories&section='. $row->section, $adminLanguage->A_COMP_CATEG_MESSAGE ." ". $row->title ." ". $adminLanguage->A_COMP_CATEG_MESSAGE2 );
	}

	if ($uid) {
		// existing record
		$row->checkout( $my->id );
		// code for Link Menu
		if ( $row->section > 0 ) {
			$query = "SELECT *"
			. "\n FROM #__menu"
			. "\n WHERE componentid = ". $row->id
			. "\n AND ( type = 'content_archive_category' OR type = 'content_blog_category' OR type = 'content_category' )"
			;
			$database->setQuery( $query );
			$menus = $database->loadObjectList();
			$count = count( $menus );
			for( $i = 0; $i < $count; $i++ ) {
				switch ( $menus[$i]->type ) {
					case 'content_category':
						$menus[$i]->type = $adminLanguage->A_COMP_CATEG_TABLE ;
						break;
			
					case 'content_blog_category':
						$menus[$i]->type = $adminLanguage->A_COMP_CATEG_BLOG ;
						break;
			
					case 'content_archive_category':
						$menus[$i]->type = $adminLanguage->A_COMP_CATEG_BLOG_ARCHIVE ;
						break;
				}
			}
		} else {
			$menus = array();
		}
	} else {
		// new record
		$row->section = $section;
		$row->published = 1;
		$menus = NULL;
	}

	// make order list
	$order = array();
	$database->setQuery( "SELECT COUNT(*) FROM #__categories WHERE section='$row->section'" );
	$max = intval( $database->loadResult() ) + 1;

	for ($i=1; $i < $max; $i++) {
		$order[] = mosHTML::makeOption( $i );
	}

	// build the html select list for sections
	if ( $section == 'content' ) {
		$query = "SELECT s.id AS value, s.title AS text"
		. "\n FROM #__sections AS s"
		. "\n ORDER BY s.ordering"
		;
		$database->setQuery( $query );
		$sections = $database->loadObjectList();
		$lists['section'] = mosHTML::selectList( $sections, 'section', 'class="inputbox" size="1"', 'value', 'text' );;
	} else {
		if ( $type == 'other' ) {
			$section_name = 'N/A';
		} else {
			$temp = new mosSection( $database );
			$temp->load( $row->section );
			$section_name = $temp->name;
		}
		$lists['section'] = '<input type="hidden" name="section" value="'. $row->section .'" />'. $section_name;
	}
	
	// build the html select list for category types
	$types[] = mosHTML::makeOption( '', $adminLanguage->A_COMP_CATEG_SELECT_TYPE );
	$types[] = mosHTML::makeOption( 'content_category', $adminLanguage->A_COMP_CATEG_TABLE );
	$types[] = mosHTML::makeOption( 'content_blog_category', $adminLanguage->A_COMP_CATEG_BLOG );
	$types[] = mosHTML::makeOption( 'content_archive_category', $adminLanguage->A_COMP_CATEG_BLOG_ARCHIVE );
	$lists['link_type'] 		= mosHTML::selectList( $types, 'link_type', 'class="inputbox" size="1"', 'value', 'text' );;

	// build the html select list for ordering
	$query = "SELECT ordering AS value, title AS text"
	. "\n FROM #__categories"
	. "\n WHERE section = '$row->section'"
	. "\n ORDER BY ordering"
	;
	$lists['ordering'] 			= mosAdminMenus::SpecificOrdering( $row, $uid, $query ); 

	// build the select list for the image positions
	$active =  ( $row->image_position ? $row->image_position : 'left' );
	$lists['image_position'] 	= mosAdminMenus::Positions( 'image_position', $active, NULL, 0, 0 );
	// Imagelist
	$lists['image'] 			= mosAdminMenus::Images( 'image', $row->image );
	// build the html select list for the group access
	$lists['access'] 			= mosAdminMenus::Access( $row );
	// build the html radio buttons for published
	$lists['published'] 		= mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );
	// build the html select list for menu selection
	$lists['menuselect']		= mosAdminMenus::MenuSelect( );

 	categories_html::edit( $row, $section, $lists, $redirect, $menus );
}

/**
* Saves the catefory after an edit form submit
* @param string The name of the category section
*/
function saveCategory() {
	global $database;

	$redirect = mosGetParam( $_POST, 'redirect', '' );

	$row = new mosCategory( $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$row->updateOrder( "section='$row->section'" );

	if ($oldtitle = mosGetParam( $_POST, 'oldtitle', null )) {
		if ($oldtitle != $row->title) {
			$database->setQuery( "UPDATE #__menu SET name='$row->title' WHERE name='$oldtitle' AND type='content_category'" );
			$database->query();
		}
	}

	// Update Section Count
	if ($row->section != "com_weblinks") {
		$query = "UPDATE #__sections SET count=count+1"
		. "\n WHERE id = '$row->section'"
		;
		$database->setQuery( $query );
	}

	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}
	mosRedirect( 'index2.php?option=com_categories&section='. $redirect );
}

/**
* Deletes one or more categories from the categories table
* @param string The name of the category section
* @param array An array of unique category id numbers
*/
function removeCategories( $section, $cid ) {
	global $database;
    global $adminLanguage;
	if (count( $cid ) < 1) {
		echo "<script> alert('".$adminLanguage->A_COMP_CATEG_DELETE."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	//Get Section ID prior to removing Category, in order to update counts
	$database->setQuery( "SELECT section FROM #__categories WHERE id IN ($cids)" );
	$secid = $database->loadResult();

	if (intval( $section ) > 0) {
		$table = 'content';
	} else if (strpos( $section, 'com_' ) === 0) {
		$table = substr( $section, 4 );
	} else {
		$table = $section;
	}
	
	$query = "SELECT c.id, c.name, COUNT(s.catid) AS numcat"
	. "\n FROM #__categories AS c"
	. "\n LEFT JOIN #__$table AS s ON s.catid=c.id"
	. "\n WHERE c.id IN ($cids)"
	. "\n GROUP BY c.id"
	;
	$database->setQuery( $query );

	if (!($rows = $database->loadObjectList())) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
	}

	$err = array();
	$cid = array();
	foreach ($rows as $row) {
		if ($row->numcat == 0) {
			$cid[] = $row->id;
		} else {
			$err[] = $row->name;
		}
	}

	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$database->setQuery( "DELETE FROM #__categories WHERE id IN ($cids)" );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}

	if (count( $err )) {
		$cids = implode( "\', \'", $err );
		$msg = $adminLanguage->A_COMP_CATEG_CATEG_S.": ". $cids ." ".$adminLanguage->A_COMP_CATEG_CANNOT_REMOVE;
		mosRedirect( 'index2.php?option=com_categories&section='. $section .'&mosmsg='. $msg );
	}

	mosRedirect( 'index2.php?option=com_categories&section='. $section );
}

/**
* Publishes or Unpublishes one or more categories
* @param string The name of the category section
* @param integer A unique category id (passed from an edit form)
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The name of the current user
*/
function publishCategories( $section, $categoryid=null, $cid=null, $publish=1 ) {
	global $database, $my;
    global $adminLanguage;
	if (!is_array( $cid )) {
		$cid = array();
	}
	if ($categoryid) {
		$cid[] = $categoryid;
	}

	if (count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script> alert('".$adminLanguage->A_COMP_CATEG_SELECT." ".$action."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__categories SET published='$publish'"
	. "\nWHERE id IN ($cids) AND (checked_out=0 OR (checked_out='$my->id'))"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new mosCategory( $database );
		$row->checkin( $cid[0] );
	}

	mosRedirect( 'index2.php?option=com_categories&section='. $section );
}

/**
* Cancels an edit operation
* @param string The name of the category section
* @param integer A unique category id
*/
function cancelCategory() {
	global $database;

	$redirect = mosGetParam( $_POST, 'redirect', '' );

	$row = new mosCategory( $database );
	$row->bind( $_POST );
	$row->checkin();
	mosRedirect( 'index2.php?option=com_categories&section='. $redirect );
}

/**
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderCategory( $uid, $inc ) {
	global $database;

	$row = new mosCategory( $database );
	$row->load( $uid );
	$row->move( $inc, "section='$row->section'" );
	mosRedirect( 'index2.php?option=com_categories&section='. $row->section );
}

/**
* Form for moving item(s) to a specific menu
*/
function moveCategorySelect( $option, $cid, $sectionOld ) {
	global $database;
    global $adminLanguage;
	$redirect = mosGetParam( $_POST, 'section', 'content' );;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('".$adminLanguage->A_COMP_CATEG_ITEM_MOVE."'); window.history.go(-1);</script>\n";
		exit;
	}

	## query to list selected categories
	$cids = implode( ',', $cid );
	$query = "SELECT a.name, a.section FROM #__categories AS a WHERE a.id IN ( ". $cids ." )";
	$database->setQuery( $query );
	$items = $database->loadObjectList();
	
	## query to list items from categories
	$query = "SELECT a.title FROM #__content AS a WHERE a.catid IN ( ". $cids ." ) ORDER BY a.catid, a.title";
	$database->setQuery( $query );
	$contents = $database->loadObjectList();
	
	## query to choose section to move to
	$query = "SELECT a.name AS `text`, a.id AS `value` FROM #__sections AS a WHERE a.published = '1' ORDER BY a.name";
	$database->setQuery( $query );
	$sections = $database->loadObjectList();
	
	// build the html select list
	$SectionList = mosHTML::selectList( $sections, 'sectionmove', 'class="inputbox" size="10"', 'value', 'text', null );

	categories_html::moveCategorySelect( $option, $cid, $SectionList, $items, $sectionOld, $contents, $redirect );
}


/**
* Save the item(s) to the menu selected
*/
function moveCategorySave( $option, $cid, $sectionOld ) {
	global $database, $my;
    global $adminLanguage;
	$sectionMove = mosGetParam( $_REQUEST, 'sectionmove', '' );

	$cids = implode( ',', $cid );
	$total = count( $cid );

	$query = 	"UPDATE #__categories SET section = '". $sectionMove ."' "
	. "WHERE id IN ( ". $cids ." )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('". $database->getErrorMsg() ."'); window.history.go(-1); </script>\n";
		exit();
	}
	$query = 	"UPDATE #__content SET sectionid = '". $sectionMove ."' "
	. "WHERE catid IN ( ". $cids ." )"
	;
	$database->setQuery( $query );
	if ( !$database->query() ) {
		echo "<script> alert('". $database->getErrorMsg() ."'); window.history.go(-1); </script>\n";
		exit();
	}
	$sectionNew = new mosSection ( $database );
	$sectionNew->load( $sectionMove );

	$msg = $total ." ".$adminLanguage->A_COMP_CATEG_MOVED_TO." ".$sectionNew->name;
	mosRedirect( 'index2.php?option=com_categories&section='. $sectionOld .'&mosmsg='. $msg );
}

/**
* Form for copying item(s) to a specific menu
*/
function copyCategorySelect( $option, $cid, $sectionOld ) {
	global $database;
    global $adminLanguage;
	$redirect = mosGetParam( $_POST, 'section', 'content' );;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('".$adminLanguage->A_COMP_CATEG_ITEM_MOVE."'); window.history.go(-1);</script>\n";
		exit;
	}

	## query to list selected categories
	$cids = implode( ',', $cid );
	$query = "SELECT a.name, a.section FROM #__categories AS a WHERE a.id IN ( ". $cids ." )";
	$database->setQuery( $query );
	$items = $database->loadObjectList();
	
	## query to list items from categories
	$query = "SELECT a.title, a.id FROM #__content AS a WHERE a.catid IN ( ". $cids ." ) ORDER BY a.catid, a.title";
	$database->setQuery( $query );
	$contents = $database->loadObjectList();
	
	## query to choose section to move to
	$query = "SELECT a.name AS `text`, a.id AS `value` FROM #__sections AS a WHERE a.published = '1' ORDER BY a.name";
	$database->setQuery( $query );
	$sections = $database->loadObjectList();
	
	// build the html select list
	$SectionList = mosHTML::selectList( $sections, 'sectionmove', 'class="inputbox" size="10"', 'value', 'text', null );

	categories_html::copyCategorySelect( $option, $cid, $SectionList, $items, $sectionOld, $contents, $redirect );
}


/**
* Save the item(s) to the menu selected
*/
function copyCategorySave( $option, $cid, $sectionOld ) {
	global $database, $my;
    global $adminLanguage;
	$sectionMove = mosGetParam( $_REQUEST, 'sectionmove', '' );
	$contentid = mosGetParam( $_REQUEST, 'item', '' );
	$total = count( $contentid  );

	$category = new mosCategory ( $database );
	foreach( $cid as $id ) {
		$category->load( $id );
		$category->id = NULL;
		$category->title = $adminLanguage->A_COMP_CATEG_COPY_OF." ".$category->title;
		$category->name = $adminLanguage->A_COMP_CATEG_COPY_OF." ".$category->name;
		$category->section = $sectionMove;
		if (!$category->check()) {
			echo "<script> alert('".$category->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
	
		if (!$category->store()) {
			echo "<script> alert('".$category->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$category->checkin();
		// stores original catid
		$newcatids[]["old"] = $id;
		// pulls new catid
		$newcatids[]["new"] = $category->id;
	}

	$content = new mosContent ( $database );
	foreach( $contentid as $id) {
		$content->load( $id );
		$content->id = NULL;
		$content->sectionid = $sectionMove;
		$content->hits = 0;
		foreach( $newcatids as $newcatid ) {
			if ( $content->catid == $newcatid["old"] ) {
				$content->catid = $newcatid["new"];
			}
		}
		if (!$content->check()) {
			echo "<script> alert('".$content->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
	
		if (!$content->store()) {
			echo "<script> alert('".$content->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$content->checkin();
	}

	$sectionNew = new mosSection ( $database );
	$sectionNew->load( $sectionMove );

	$msg = $total ." ".$adminLanguage->A_COMP_CATEG_COPIED_TO." ".$sectionNew->name;
	mosRedirect( 'index2.php?option=com_categories&section='. $sectionOld .'&mosmsg='. $msg );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $option, $section ) {
	global $database;

	$row = new mosCategory( $database );
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	mosRedirect( 'index2.php?option=com_categories&section='. $section );
}

function menuLink( $option ) {
	global $database;
    global $adminLanguage;
	$category = new mosCategory( $database );
	$category->bind( $_POST );
	$category->checkin();

	$redirect		= mosGetParam( $_POST, 'redirect', '' );
	$menu 		= mosGetParam( $_POST, 'menuselect', '' );
	$name 		= mosGetParam( $_POST, 'link_name', '' );
	$id 			= mosGetParam( $_POST, 'id', '' );
	$sectionid	= mosGetParam( $_POST, 'sectionid', '' );
	$type 		= mosGetParam( $_POST, 'link_type', '' );
	switch ( $type ) {
		case 'content_category':
			$link 	= 'index.php?option=com_content&task=category&sectionid='. $sectionid .'&id='. $id;
			$menutype	= 'Category Table';
			break;

		case 'content_blog_category':
			$link = 'index.php?option=com_content&task=blogcategory&id='. $id;
			$menutype	= 'Category Blog';
			break;

		case 'content_archive_category':
			$link = 'index.php?option=com_content&task=archivecategory&id='. $id;
			$menutype	= 'Category Blog Archive';
			break;
	}

	$row 				= new mosMenu( $database );
	$row->menutype 		= $menu;
	$row->name 			= $name;
	$row->type 			= $type;
	$row->published		= 1;
	$row->componentid		= $id;
	$row->link			= $link;
	$row->ordering			= 9999;

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();
	$row->updateOrder( "section='". $sectionid ."'" );

	$msg = $name .' ( '. $menutype .' ) in menu: '. $menu .' successfully created';
	mosRedirect( 'index2.php?option='. $option .'&section='. $redirect,  $msg );

}


?>
