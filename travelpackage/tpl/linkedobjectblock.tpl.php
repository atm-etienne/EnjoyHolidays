<?php

// Protection to avoid direct call of template
if (empty($conf) || !is_object($conf)) {
	print "Error, template page can't be called as URL";
	exit;
}

print "<!-- BEGIN PHP TEMPLATE enjoyholidays/travelpackage/tpl/linkedobjectblock.tpl.php -->\n";

global $user, $langs;
global $noMoreLinkedObjectBlockAfter;

$linkedObjectBlock = $GLOBALS['linkedObjectBlock'];
$linkedObjectBlock = dol_sort_array($linkedObjectBlock, 'date', 'desc', 0, 0, 1);

$total = 0;
$ilink = 0;
foreach ($linkedObjectBlock as $key => $objectlink) {
	$ilink++;

	$trclass = 'oddeven';
	if ($ilink == count($linkedObjectBlock) && empty($noMoreLinkedObjectBlockAfter) && count($linkedObjectBlock) <= 1) {
		$trclass .= ' liste_sub_total';
	}
	echo '<tr class="'.$trclass.'" >';
	echo '<td class="linkedcol-element tdoverflowmax100">'.$langs->trans("TravelPackage");
	if (!empty($showImportButton) && getDolGlobalString('MAIN_ENABLE_IMPORT_LINKED_OBJECT_LINES')) {
		print '<a class="objectlinked_importbtn" href="'.$objectlink->getNomUrl(0, '', 0, 1).'&amp;action=selectlines" data-element="'.$objectlink->element.'" data-id="'.$objectlink->id.'"  > <i class="fa fa-indent"></i> </a';
	}
	echo '</td>';
	echo '<td class="linkedcol-name tdoverflowmax150" >'.$objectlink->getNomUrl(1).'</td>';
	echo '<td class="linkedcol-ref">'.$objectlink->ref_client.'</td>';
	echo '<td class="linkedcol-date center">'.dol_print_date($objectlink->date_creation, 'day').'</td>';
	echo '<td class="linkedcol-amount right">';
	if ($user->hasRight('enjoyholidays', 'travelpackage', 'read')) {
		$total = $total + $objectlink->amount;
		echo price($objectlink->amount);
	}
	echo '</td>';
	echo '<td class="linkedcol-statut right">'.$objectlink->getLibStatut(3).'</td>';
	echo '<td class="linkedcol-action right">';
	echo '</td>';
	echo "</tr>\n";
}
if (count($linkedObjectBlock) > 1) {
	echo '<tr class="liste_total '.(empty($noMoreLinkedObjectBlockAfter) ? 'liste_sub_total' : '').'">';
	echo '<td>'.$langs->trans("Total").'</td>';
	echo '<td></td>';
	echo '<td class="center"></td>';
	echo '<td class="center"></td>';
	echo '<td class="right">'.price($total).'</td>';
	echo '<td class="right"></td>';
	echo '<td class="right"></td>';
	echo "</tr>\n";
}

echo "<!-- END PHP TEMPLATE -->\n";
