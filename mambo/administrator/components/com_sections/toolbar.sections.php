<?php
/**
* @version $Id: toolbar.sections.php,v 1.7 2004/08/26 05:20:48 rcastley Exp $
* @package Mambo_4.5.1
* @copyright (C) 2000 - 2004 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

require_once( $mainframe->getPath( 'toolbar_html' ) );

switch ($task){
	case "new":
	case "edit":
		TOOLBAR_sections::_EDIT();
		break;

	case "copyselect":
		TOOLBAR_sections::_COPY();
		break;

	default:
		TOOLBAR_sections::_DEFAULT();
		break;
}
?>