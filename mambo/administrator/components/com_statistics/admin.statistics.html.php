<?php
/**
* @version $Id: admin.statistics.html.php,v 1.2 2004/10/11 03:36:33 dappa Exp $
* @package Mambo_4.5.1
* @copyright (C) 2000 - 2004 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

/**
* @package Mambo_4.5.1
*/
class HTML_statistics {
	function show( &$browsers, &$platforms, $tldomains, $bstats, $pstats, $dstats, $sorts, $option ) {
	    global $mosConfig_live_site, $adminLanguage;

		$tab = mosGetParam( $_REQUEST, 'tab', 'tab1' );
		$width = 400;	// width of 100%
		$tabs = new mosTabs(0);
		?>
<style type="text/css">
.bar_1{ background-color: #8D1B1B; border: 2px ridge #B22222; }
.bar_2{ background-color: #6740E1; border: 2px ridge #4169E1; }
.bar_3{ background-color: #8D8D8D; border: 2px ridge #D2D2D2; }
.bar_4{ background-color: #CC8500; border: 2px ridge #FFA500; }
.bar_5{ background-color: #5B781E; border: 2px ridge #6B8E23; }
</style>
		<table class="adminheading">
		<tr>
			<th class="browser"><?php echo $adminLanguage->A_COMP_STAT_OS;?></th>
		</tr>
		</table>
	<form action="index2.php" method="post" name="adminForm">
	<?php
 $tabs->startPane("statsPane");
 $tabs->startTab($adminLanguage->A_COMP_STAT_BR_PAGE, "browsers-page" );
?>
	<table class="adminlist">
	<tr>
		<th align="left">&nbsp;<?php echo $adminLanguage->A_COMP_STAT_BROWSER;?>&nbsp;<?php echo $sorts['b_agent'];?></th>
		<th>&nbsp;</th>
		<th width="100" align="left">% <?php echo $sorts['b_hits'];?></th>
		<th width="100" align="left"><?php echo $adminLanguage->A_COMP_NB;?></th>
	</tr>
		<?php
		$c = 1;
		if (is_array($browsers) && count($browsers) > 0) {
			$k = 0;
			foreach ($browsers as $b) {
				$f = $bstats->totalhits > 0 ? $b->hits / $bstats->totalhits : 0;
				$w = $width * $f;
			?>
		<tr class="row<?php echo $k;?>">
			<td width="200" align="left">
				&nbsp;<?php echo $b->agent; ?>&nbsp;
			</td>
			<td align="left" width="<?php echo $width+10;?>">
				<div align="left">&nbsp;<img src="<?php echo $mosConfig_live_site; ?>/components/com_poll/images/blank.png" class="bar_<?php echo $c; ?>" height="6" width="<?php echo $w; ?>"></div>
			</td>
			<td align="left">
				<?php printf( "%.2f%%", $f * 100 );?>
			</td>
			<td align="left">
				<?php echo $b->hits;?>
			</td>
		</tr>
		<?php
		$c = $c % 5 + 1;
		$k = 1 - $k;
			}
		}
	?>
	<tr>
		<th colspan="4">&nbsp;</th>
	</tr>
	</table>
	<?php
 $tabs->endTab();
 $tabs->startTab($adminLanguage->A_COMP_STAT_OS_PAGE, "os-page" );
?>
	<table class="adminlist">
	<tr>
		<th align="left">&nbsp;<?php echo $adminLanguage->A_COMP_STAT_OP_SYST;?>&nbsp;<?php echo $sorts['o_agent'];?></th>
		<th>&nbsp;</th>
		<th width="100" align="left">% <?php echo $sorts['o_hits'];?></th>
		<th width="100" align="left"><?php echo $adminLanguage->A_COMP_NB;?></th>
	</tr>
		<?php
		$c = 1;
		if (is_array($platforms) && count($platforms) > 0) {
			$k = 0;
			foreach ($platforms as $p) {
				$f = $pstats->totalhits > 0 ? $p->hits / $pstats->totalhits : 0;
				$w = $width * $f;
		?>
		<tr class="row<?php echo $k;?>">
			<td width="200" align="left">
				&nbsp;<?php echo $p->agent; ?>&nbsp;
			</td>
			<td align="left" width="<?php echo $width+10;?>">
				<div align="left">&nbsp;<img src="<?php echo $mosConfig_live_site; ?>/components/com_poll/images/blank.png" class="bar_<?php echo $c; ?>" height="6" width="<?php echo $w; ?>"></div>
			</td>
			<td align="left">
				<?php printf( "%.2f%%", $f * 100 );?>
			</td>
			<td align="left">
				<?php echo $p->hits;?>
			</td>
		</tr>
		<?php
		$c = $c % 5 + 1;
		$k = 1 - $k;
			}
		}
	?>
	<tr>
		<th colspan="4">&nbsp;</th>
	</tr>
	</table>
		<?php
 $tabs->endTab();
 $tabs->startTab($adminLanguage->A_COMP_STAT_URL_PAGE, "domain-page" );
?>
	<table class="adminlist">
	<tr>
		<th align="left">&nbsp;<?php echo $adminLanguage->A_COMP_STAT_URL;?>&nbsp;<?php echo $sorts['d_agent'];?></th>
		<th>&nbsp;</th>
		<th width="100" align="left">% <?php echo $sorts['d_hits'];?></th>
		<th width="100" align="left"><?php echo $adminLanguage->A_COMP_NB;?></th>
	</tr>
		<?php
		$c = 1;
		if (is_array($tldomains) && count($tldomains) > 0) {
			$k = 0;
			foreach ($tldomains as $b) {
				$f = $dstats->totalhits > 0 ? $b->hits / $dstats->totalhits : 0;
				$w = $width * $f;
		?>
		<tr class="row<?php echo $k;?>">
			<td width="200" align="left">
				&nbsp;<?php echo $b->agent; ?>&nbsp;
			</td>
			<td align="left" width="<?php echo $width+10;?>">
				<div align="left">&nbsp;<img src="<?php echo $mosConfig_live_site; ?>/components/com_poll/images/blank.png" class="bar_<?php echo $c; ?>" height="6" width="<?php echo $w; ?>"></div>
			</td>
			<td align="left">
				<?php printf( "%.2f%%", $f * 100 );?>
			</td>
			<td align="left">
				<?php echo $b->hits;?>
			</td>
		</tr>
		<?php
		$c = $c % 5 + 1;
		$k = 1 - $k;
			}
		}
	?>
	<tr>
		<th colspan="4">&nbsp;</th>
	</tr>
	</table>
		<?php
 $tabs->endTab();
 $tabs->endPane();
?>
  	<input type="hidden" name="option" value="<?php echo $option;?>" />
	<input type="hidden" name="tab" value="<?php echo $tab;?>" />
	</form>
	
	<?php
	}

	function pageImpressions( &$rows, $pageNav, $option, $task ) {
		global $adminLanguage;
        ?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminheading">
		<tr>
			<th width="100%" class="impressions"><?php echo $adminLanguage->A_COMP_STAT_IMPR;?></th>
		</tr>
	</table>
	<form action="index2.php" method="post" name="adminForm">
	<table class="adminlist">
		<tr>
			<th style="text-align:right"><?php echo $adminLanguage->A_COMP_NB;?></th>
			<th class="title"><?php echo $adminLanguage->A_COMP_TITLE;?></th>
			<th align="center" nowrap="nowrap"><?php echo $adminLanguage->A_COMP_STAT_PG_IMPR;?></th>
			</tr>
		<?php
		$i = $pageNav->limitstart;
		$k = 0;
		foreach ($rows as $row) {
		?>
		<tr class="row<?php echo $k;?>">
			<td align="right">
				<?php echo ++$i; ?>
			</td>
			<td align="left">
				&nbsp;<?php echo $row->title." (".$row->created.")"; ?>&nbsp;
			</td>
			<td align="center">
				<?php echo $row->hits; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
		}
		?>
	</table>
	<?php echo $pageNav->getListFooter(); ?>
  	<input type="hidden" name="option" value="<?php echo $option;?>" />
  	<input type="hidden" name="task" value="<?php echo $task;?>" />
	</form>
		<?php
	}

	function showSearches( &$rows, $pageNav, $option, $task ) {
		global $mainframe, $adminLanguage;
	?>
	<form action="index2.php" method="post" name="adminForm">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminheading">
		<tr>
			<th width="100%" class="searchtext">
				<?php echo $adminLanguage->A_COMP_STAT_SCH_ENG;?> :
				<span class="componentheading"><?php echo $adminLanguage->A_COMP_STAT_LOG_IS;?> :
		<?php echo $mainframe->getCfg( 'enable_log_searches' ) ? '<b><font color="green">'. $adminLanguage->A_COMP_STAT_ENABLED .'</font></b>' : '<b><font color="red">'. $adminLanguage->A_COMP_STAT_DISABLED .'</font></b>' ?>
				</span>
			</th>
		</tr>
	</table>
	<table class="adminlist">
		<tr>
			<th style="text-align:right"><?php echo $adminLanguage->A_COMP_NB;?></th>
			<th class="title"><?php echo $adminLanguage->A_COMP_STAT_SCH_TEXT;?></th>
			<th nowrap="nowrap"><?php echo $adminLanguage->A_COMP_STAT_T_REQ;?></th>
			<th nowrap="nowrap"><?php echo $adminLanguage->A_COMP_STAT_R_RETURN;?></th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n = count($rows); $i < $n; $i++) {
			$row =& $rows[$i];
		?>
		<tr class="row<?php echo $k;?>">
			<td align="right">
				<?php echo $i+1+$pageNav->limitstart; ?>
			</td>
			<td align="left"><?php echo $row->search_term;?></td>
			<td align="center"><?php echo $row->hits; ?></td>
			<td align="center"><?php echo $row->returns; ?></td>
		</tr>
		<?php
		$k = 1 - $k;
		}
		?>
	</table>
	<?php echo $pageNav->getListFooter(); ?>
  	<input type="hidden" name="option" value="<?php echo $option;?>" />
  	<input type="hidden" name="task" value="<?php echo $task;?>" />
	</form>
<?php
	}
}
?>
