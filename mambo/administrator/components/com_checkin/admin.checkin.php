<?php
/**
* @version $Id: admin.checkin.php,v 1.1 2004/10/01 02:38:53 mibi Exp $
* @package Mambo_4.5.1
* @copyright (C) 2000 - 2004 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

if (!$acl->acl_check( 'administration', 'config', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', _NOT_AUTH );
}
?>
<table class="adminheading">
<tr>
<th class="checkin"><?php echo $adminLanguage->A_COMP_CHECK_TITLE;?></th>
</tr>
</table>
<table class="adminform">
<tr>
	<th class="title"><?php echo $adminLanguage->A_COMP_CHECK_DB_T;?></th>
	<th class="title"><?php echo $adminLanguage->A_COMP_CHECK_NB_ITEMS;?></th>
	<th class="title"><?php echo $adminLanguage->A_COMP_CHECK_IN;?></th>
	<th class="title">&nbsp;</th>
</tr>
<?php
$lt = mysql_list_tables($mosConfig_db);
$k = 0;
$dbprefix = $mainframe->getCfg( 'mosConfig_dbprefix' );
while (list($tn) = mysql_fetch_array( $lt )) {
	// make sure we get the right tables based on prefix
	if (!preg_match( "/^".$dbprefix."/i", $tn )) {
		continue;
	}
	$lf = mysql_list_fields($mosConfig_db, "$tn");
	$nf = mysql_num_fields($lf);

	$foundCO = false;
	$foundCOT = false;
	$foundE = false;
	for ($i = 0; $i < $nf; $i++) {
		$fname = mysql_field_name($lf, $i);
		if ( $fname == 'checked_out') {
			$foundCO = true;
		} else if ( $fname == 'checked_out_time') {
			$foundCOT = true;
		} else if ( $fname == 'editor') {
			$foundE = true;
		}
	}

	if ($foundCO && $foundCOT) {
		if ($foundE) {
			$database->setQuery( "SELECT checked_out, editor FROM $tn WHERE checked_out > 0" );
		} else {
			$database->setQuery( "SELECT checked_out FROM $tn WHERE checked_out > 0" );
		}
		$res = $database->query();
		$num = $database->getNumRows( $res );

		if ($foundE) {
			$database->setQuery( "UPDATE $tn SET checked_out=0, checked_out_time='00:00:00', editor=NULL WHERE checked_out > 0" );
		} else {
			$database->setQuery( "UPDATE $tn SET checked_out=0, checked_out_time='0000-00-00 00:00:00' WHERE checked_out > 0" );
		}
		$res = $database->query();

		if ($res == 1) {
			if ($num > 0) {
				echo "<tr class=\"row$k\">";
				echo "\n	<td width=\"350\">". $adminLanguage->A_COMP_CHECK_TABLE ." - $tn</td>";
				echo "\n	<td width=\"150\">". $adminLanguage->A_COMP_CHECK_IN ." <b>$num</b> ". $adminLanguage->A_COMP_ITEMS ."</td>";
				echo "\n	<td width=\"100\" align=\"center\"><img src=\"images/tick.png\" border=\"0\" alt=\"tick\" /></td>";
				echo "\n	<td>&nbsp;</td>";
				echo "\n</tr>";
			} else {
				echo "<tr class=\"row$k\">";
				echo "\n	<td width=\"350\">". $adminLanguage->A_COMP_CHECK_TABLE ." - $tn</td>";
				echo "\n	<td width=\"150\">". $adminLanguage->A_COMP_CHECK_IN ." <b>$num</b> ". $adminLanguage->A_COMP_ITEMS ."</td>";
				echo "\n	<td width=\"100\">&nbsp;</td>";
				echo "\n	<td>&nbsp;</td>";
				echo "\n</tr>";
			}
			$k = 1 - $k;
		}
	}
}
?>
<tr>
	<td colspan="4"><b><?php echo $adminLanguage->A_COMP_CHECK_DONE;?></b></td>
</tr>
</table>
