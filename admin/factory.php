<?php
/* Copyright (C) 2014-2019		Charlene BENKE		<charlie@patas-monkey.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	  \file	   htdocs/factory/admin/factory.php
 *		\ingroup	factory
 *		\brief	  Page to setup factory module
 */

$res=0;
if (! $res && file_exists("../../main.inc.php")) 
	$res=@include("../../main.inc.php");		// For root directory
if (! $res && file_exists("../../../main.inc.php")) 
	$res=@include("../../../main.inc.php");	// For "custom" directory

require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";

dol_include_once('/factory/class/factory.class.php');
dol_include_once('/factory/core/lib/factory.lib.php');

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

$langs->load("factory@factory");
$langs->load("admin");
$langs->load("errors");

if (! $user->admin) accessforbidden();

$action = GETPOST('action', 'alpha');
$value = GETPOST('value', 'alpha');

$componentprice=GETPOST('componentprice', 'alpha');
$componentpriceservice=GETPOST('componentpriceservice', 'alpha');

/*
 * Actions
 */

if ($action == 'setdefaultother') {
	// save the setting
	$res = dolibarr_set_const(
					$db, "FACTORY_CATEGORIE_ROOT", GETPOST('root_categ', 'int'), 'chaine', 0, '', $conf->entity
	);
	if (! $res > 0) $error++;
	$res = dolibarr_set_const(
					$db, "FACTORY_CATEGORIE_CATALOG", GETPOST('catalog_categ', 'int'), 'chaine', 0, '', $conf->entity
	);
	if (! $res > 0) $error++;
	$res = dolibarr_set_const(
					$db, "FACTORY_CATEGORIE_VARIANT", GETPOST('variant_categ', 'int'), 'chaine', 0, '', $conf->entity
	);
	if (! $res > 0) $error++;

	if (! $error)
		$mesg = "<font class='ok'>".$langs->trans("SetupSaved")."</font>";
	else
		$mesg = "<font class='error'>".$langs->trans("Error")."</font>";
}


if ($action == 'componentprice') {
	dolibarr_set_const($db, "FACTORY_COMPONENT_BUYINGPRICE", $componentprice, 'chaine', 0, '', $conf->entity);
	dolibarr_set_const(
					$db, "FACTORY_COMPONENT_BUYINGPRICESERVICE", $componentpriceservice, 
					'chaine', 0, '', $conf->entity
	);
}
$componentprice = $conf->global->FACTORY_COMPONENT_BUYINGPRICE;
$componentpriceservice = $conf->global->FACTORY_COMPONENT_BUYINGPRICESERVICE;

if ($action == 'getinfofromextrafield')
	dolibarr_set_const($db, "factory_extrafieldsNameInfo", GETPOST('extrafieldsName'), 'chaine', 0, '', $conf->entity);

/*
 * View
 */

$dirmodels= $conf->modules_parts['models'];

$page_name = $langs->trans('FactorySetup')." FREE VERSION - ".$langs->trans('GeneralSetup');
$tab = $langs->trans("Factory");

llxHeader("", $page_name, 'EN:Factory_Configuration|FR:Configuration_module_Factory|ES:Configuracion_Factory');

$form=new Form($db);
$htmlother=new FormOther($db);

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($page_name, $linkback, 'title_setup');

$head = factory_admin_prepare_head();
dol_fiche_head($head, 'setup', $tab, 0, 'factory@factory');

print_titre($langs->trans("ComponentsBuyingPrice"));
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="componentprice">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width=80% >'.$langs->trans("Description").'</td>';
print '<td width=20% colspan=2>'.$langs->trans("Status").'</td>';
print '</tr>'."\n";
$var = true;

$tblArraychoice = array(
				"pmpprice" => $langs->trans("UsePMPPrice"),
				"costprice" => $langs->trans("UseCostPrice")
);

if (! empty($conf->fournisseur->enabled)) {
	$tblArraychoice = array_merge(
					$tblArraychoice, 
					array("fournishless" => $langs->trans("UseFournishPriceLess"))
	);
	$tblArraychoice = array_merge(
					$tblArraychoice, 
					array("fournishmore" => $langs->trans("UseFournishPriceMore"))
	);
}

if ($componentprice == '')
	$componentprice ='pmpprice'; // on prend le pmp par défaut (toujours là lui...)
if ($componentpriceservice == '')
	$componentpriceservice ='costprice'; // on prend le costprice par défaut 

$tblArraychoiceservice = $tblArraychoice;
unset($tblArraychoiceservice['pmpprice']);

print '<tr>';
print '<td>'.$langs->trans("InfoComponentsBuyingPrice").'</td>';
print '<td>';
print $form->selectarray("componentprice", $tblArraychoice, $componentprice, 0);
print '</td>';
print '</tr>';
print '<tr>';
print '<td>'.$langs->trans("InfoServiceBuyingPrice").'</td>';
print '<td>';
print $form->selectarray("componentpriceservice", $tblArraychoiceservice, $componentpriceservice, 0);
print '</td>';
print '</tr>';
print '<tr>';	
print '<td colspan=2 align="right">';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</td>';

print '</table>';
print '</form>';

print_titre($langs->trans("FactoryDeclinationFeature"));
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setdefaultother">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="200px">'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td width=20% nowrap>'.$langs->trans("Value").'</td>';
print '</tr>'."\n";
$var = true;
if (! empty($conf->categorie->enabled)) {
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("RootCategorie").'</td>';
	print '<td>'.$langs->trans("InfoRootCategorie").'</td>';
	print '<td nowrap>';
	print $htmlother->select_categories(0, $conf->global->FACTORY_CATEGORIE_ROOT, 'root_categ', 1);
	print '</td></tr>'."\n";

	$var = !$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("VariantCategorie").'</td>';
	print '<td>'.$langs->trans("InfoVariantCategorie").'</td>';
	print '<td nowrap>';
	print $htmlother->select_categories(0, $conf->global->FACTORY_CATEGORIE_VARIANT, 'variant_categ', 1);
	print '</td></tr>'."\n";

	$var = !$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("CatalogCategorie").'</td>';
	print '<td>'.$langs->trans("InfoCatalogCategorie").'</td>';
	print '<td nowrap>';
	print $htmlother->select_categories(0, $conf->global->FACTORY_CATEGORIE_CATALOG, 'catalog_categ', 1);
	print '</td></tr>'."\n";

	$var = !$var;
	print '<tr '.$bc[$var].'>';
	print '<td colspan=3 align= center><input type=submit value='.$langs->trans("Save").'></td>';
	print '</tr>'."\n";
} else {
	print '<tr '.$bc[$var].'>';
	print '<td colspan=3>'.$langs->trans("ThisFeatureNeedToActivateCategorieModule").'</td>';
	print "</tr>\n";
}
print '</table>';
print '</form>';

print '<br>';

/*
 *  Infos pour le support
 */
print '<br>';
libxml_use_internal_errors(true);
$sxe = simplexml_load_string(nl2br(file_get_contents('../changelog.xml')));
if ($sxe === false) {
	echo "Erreur lors du chargement du XML\n";
	foreach (libxml_get_errors() as $error) 
		print $error->message;
	exit;
} else
	$tblversions=$sxe->Version;

$currentversion = $tblversions[count($tblversions)-1];

print '<table class="noborder" width="100%">'."\n";
print '<tr class="liste_titre">'."\n";
print '<td width=20%>'.$langs->trans("SupportModuleInformation").' Factory Free Version</td>'."\n";
print '<td>'.$langs->trans("Value").'</td>'."\n";
print "</tr>\n";
print '<tr '.$bc[false].'><td >'.$langs->trans("DolibarrVersion").'</td><td>'.DOL_VERSION.'</td></tr>'."\n";
print '<tr '.$bc[true].'><td >'.$langs->trans("ModuleVersion").'</td>';
print '<td>'.$currentversion->attributes()->Number." (".$currentversion->attributes()->MonthVersion.')</td></tr>'."\n";
print '<tr '.$bc[false].'><td >'.$langs->trans("PHPVersion").'</td><td>'.version_php().'</td></tr>'."\n";
print '<tr '.$bc[true].'><td >'.$langs->trans("DatabaseVersion").'</td>';
print '<td>'.$db::LABEL." ".$db->getVersion().'</td></tr>'."\n";
print '<tr '.$bc[false].'><td >'.$langs->trans("WebServerVersion").'</td>';
print '<td>'.$_SERVER["SERVER_SOFTWARE"].'</td></tr>'."\n";
print '<tr>'."\n";
print '<td colspan="2">'.$langs->trans("SupportModuleInformationDesc").'</td></tr>'."\n";
print "</table>\n";

dol_htmloutput_mesg($mesg);

llxFooter();
$db->close();