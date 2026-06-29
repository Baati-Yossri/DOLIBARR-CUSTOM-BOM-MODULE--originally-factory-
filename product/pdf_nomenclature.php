<?php
$res = 0;
if (!$res && file_exists("../../main.inc.php")) $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php")) $res = @include("../../../main.inc.php");

require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
dol_include_once('/factory/class/factory.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

$id = GETPOST('id', 'int');
if (!$id) {
    print "Error: No product ID";
    exit;
}

$object = new Product($db);
$object->fetch($id);
$object->fetch_optionals();

$client_name = '';
$main_client = !empty($object->array_options['options_client']) ? $object->array_options['options_client'] : '';
if (!empty($main_client)) {
    if (is_numeric($main_client)) {
        $soc = new Societe($db);
        if ($soc->fetch($main_client) > 0) {
            $client_name = $soc->nom;
        }
    } else {
        $client_name = $main_client;
    }
}
$client_name = trim($client_name);
$words = explode(' ', $client_name);
if (count($words) > 2) {
    $client_name = $words[0] . ' ' . $words[1];
}

$factory = new Factory($db);
$factory->id = $id;
$factory->get_sousproduits_arbo();
$prods_arbo = $factory->get_arbo_each_prod();

// Dictionary for units
$langs->load("products");
$langs->load("bills");

// Initialize PDF
$pdf = pdf_getInstance('A4');
if (class_exists('TCPDF')) {
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
}
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

// Company Logo
$logo = $conf->mycompany->dir_output.'/logos/'.$conf->global->MAIN_INFO_SOCIETE_LOGO;
if ($conf->global->MAIN_INFO_SOCIETE_LOGO && is_readable($logo)) {
    $pdf->Image($logo, 10, 10, 0, 20);
}

// Title
$pdf->SetY(40);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Nomenclature: '.$object->ref, 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $object->label, 0, 1, 'C');
$pdf->Ln(10);

// Table Header
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(10, 8, 'N°', 1, 0, 'C', 1);
$pdf->Cell(25, 8, 'Réf', 1, 0, 'C', 1);
$pdf->Cell(90, 8, 'Désignation', 1, 0, 'C', 1);
$pdf->Cell(35, 8, 'Fournisseur', 1, 0, 'C', 1);
$pdf->Cell(15, 8, 'Unité', 1, 0, 'C', 1);
$pdf->Cell(15, 8, 'Besoin', 1, 1, 'C', 1);

// Grouping logic
$productstatic = new Product($db);
$grouped_components = array();

foreach ($prods_arbo as $value) {
    $productstatic->fetch($value['id']);
    
    // Fetch Unit
    $unit_label = '';
    if ($productstatic->fk_unit > 0) {
        $sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_units WHERE rowid = ".$productstatic->fk_unit;
        $resql = $db->query($sql);
        if ($resql && $db->num_rows($resql) > 0) {
            $obj = $db->fetch_object($resql);
            $unit_label = $langs->trans($obj->label) != $obj->label ? $langs->trans($obj->label) : $obj->label;
            if ($unit_label == 'SizeUnitm') {
                $unit_label = 'metre';
            }
        }
    }
    
    // Fetch Supplier
    $fournisseur_name = '';
    $sql = "SELECT s.nom FROM ".MAIN_DB_PREFIX."product_fournisseur_price as pfp";
    $sql.= " JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = pfp.fk_soc";
    $sql.= " WHERE pfp.fk_product = ".$value['id'];
    $sql.= " LIMIT 1";
    $resql = $db->query($sql);
    if ($resql && $db->num_rows($resql) > 0) {
        $obj = $db->fetch_object($resql);
        $fournisseur_name = $obj->nom;
        $words = explode(' ', $fournisseur_name);
        if (count($words) > 2) {
            $fournisseur_name = $words[0] . ' ' . $words[1];
        }
    }
    
    $fournisseur_name = trim($fournisseur_name);
    if (empty($fournisseur_name)) $fournisseur_name = 'Non défini';
    
    $grouped_components[$fournisseur_name][] = array(
        'ref' => $productstatic->ref,
        'label' => $productstatic->label,
        'fournisseur' => $fournisseur_name,
        'unit' => $unit_label,
        'qty' => $value['nb'] . ($value['globalqty'] == 1 ? ' G' : '')
    );
}

// Sort groups
$client_group = array();
$other_groups = array();

foreach ($grouped_components as $fourn => $comps) {
    // If supplier matches client name
    if ($client_name != '' && strtolower(trim($fourn)) == strtolower($client_name)) {
        $client_group[$fourn] = $comps;
    } else {
        $other_groups[$fourn] = $comps;
    }
}
ksort($other_groups);
$final_groups = array_merge($client_group, $other_groups);


// Table Content Rendering
$pdf->SetFont('helvetica', '', 9);
$counter = 1;

$is_first_other = true;

foreach ($final_groups as $fourn => $comps) {
    $is_client = ($client_name != '' && strtolower(trim($fourn)) == strtolower($client_name));
    
    if (!$is_client && $is_first_other && count($client_group) > 0) {
        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(10, 8, 'N°', 1, 0, 'C', 1);
            $pdf->Cell(25, 8, 'Réf', 1, 0, 'C', 1);
            $pdf->Cell(90, 8, 'Désignation', 1, 0, 'C', 1);
            $pdf->Cell(35, 8, 'Fournisseur', 1, 0, 'C', 1);
            $pdf->Cell(15, 8, 'Unité', 1, 0, 'C', 1);
            $pdf->Cell(15, 8, 'Besoin', 1, 1, 'C', 1);
            $pdf->SetFont('helvetica', '', 9);
        }
        $pdf->SetFillColor(200, 230, 200);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(190, 8, 'AUTRES FOURNISSEURS', 1, 1, 'C', 1);
        $is_first_other = false;
    }
    
    $pdf->SetFont('helvetica', '', 9);
    
    foreach ($comps as $comp) {
        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(10, 8, 'N°', 1, 0, 'C', 1);
            $pdf->Cell(25, 8, 'Réf', 1, 0, 'C', 1);
            $pdf->Cell(90, 8, 'Désignation', 1, 0, 'C', 1);
            $pdf->Cell(35, 8, 'Fournisseur', 1, 0, 'C', 1);
            $pdf->Cell(15, 8, 'Unité', 1, 0, 'C', 1);
            $pdf->Cell(15, 8, 'Besoin', 1, 1, 'C', 1);
            $pdf->SetFont('helvetica', '', 9);
        }
        
        $nbLinesLabel = $pdf->getNumLines($comp['label'], 90);
        $nbLinesFourn = $pdf->getNumLines($comp['fournisseur'], 35);
        $nbLines = max($nbLinesLabel, $nbLinesFourn);
        $h = 6 * $nbLines;
        
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        // N°
        $pdf->Rect($x, $y, 10, $h);
        $pdf->MultiCell(10, 6, $counter++, 0, 'C', 0, 0);
        $pdf->SetXY($x + 10, $y);
        
        // Réf
        $pdf->Rect($x + 10, $y, 25, $h);
        $pdf->MultiCell(25, 6, $comp['ref'], 0, 'L', 0, 0);
        $pdf->SetXY($x + 35, $y);
        
        // Désignation
        $pdf->Rect($x + 35, $y, 90, $h);
        $pdf->MultiCell(90, 6, $comp['label'], 0, 'L', 0, 0);
        $pdf->SetXY($x + 125, $y);
        
        // Fournisseur
        $pdf->Rect($x + 125, $y, 35, $h);
        $pdf->MultiCell(35, 6, $comp['fournisseur'], 0, 'L', 0, 0);
        $pdf->SetXY($x + 160, $y);
        
        // Unité
        $pdf->Rect($x + 160, $y, 15, $h);
        $pdf->MultiCell(15, $h, $comp['unit'], 0, 'C', 0, 0);
        $pdf->SetXY($x + 175, $y);
        
        // Besoin
        $pdf->Rect($x + 175, $y, 15, $h);
        $pdf->MultiCell(15, $h, $comp['qty'], 0, 'C', 0, 0);
        
        $pdf->SetXY($x, $y + $h);
    }
}

ob_clean(); // clean output buffer to avoid invalid PDF
$pdf->Output('Nomenclature_'.$object->ref.'.pdf', 'I');
?>
