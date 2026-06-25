<?php
$res = 0;
if (!$res && file_exists("../../main.inc.php")) $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php")) $res = @include("../../../main.inc.php");

require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
dol_include_once('/factory/class/factory.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

$id = GETPOST('id', 'int');
if (!$id) {
    print "Error: No product ID";
    exit;
}

$object = new Product($db);
$object->fetch($id);

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
$pdf->Cell(25, 8, 'Réf', 1, 0, 'C', 1);
$pdf->Cell(85, 8, 'Désignation', 1, 0, 'C', 1);
$pdf->Cell(45, 8, 'Fournisseur', 1, 0, 'C', 1);
$pdf->Cell(20, 8, 'Unité', 1, 0, 'C', 1);
$pdf->Cell(15, 8, 'Besoin', 1, 1, 'C', 1);

// Table Content
$pdf->SetFont('helvetica', '', 9);

$productstatic = new Product($db);

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
    }
    
    // Check if we need a page break
    if ($pdf->GetY() > 270) {
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(25, 8, 'Réf', 1, 0, 'C', 1);
        $pdf->Cell(85, 8, 'Désignation', 1, 0, 'C', 1);
        $pdf->Cell(45, 8, 'Fournisseur', 1, 0, 'C', 1);
        $pdf->Cell(20, 8, 'Unité', 1, 0, 'C', 1);
        $pdf->Cell(15, 8, 'Besoin', 1, 1, 'C', 1);
        $pdf->SetFont('helvetica', '', 9);
    }
    
    // MultiCell handles wrapping for long labels, but we need to align the row.
    // A simple way is to find the maximum height needed for the row.
    $nbLinesLabel = $pdf->getNumLines($productstatic->label, 85);
    $nbLinesFourn = $pdf->getNumLines($fournisseur_name, 45);
    $nbLines = max($nbLinesLabel, $nbLinesFourn);
    $h = 6 * $nbLines;
    
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    
    // Réf
    $pdf->Rect($x, $y, 25, $h);
    $pdf->MultiCell(25, 6, $productstatic->ref, 0, 'L', 0, 0);
    $pdf->SetXY($x + 25, $y);
    
    // Désignation
    $pdf->Rect($x + 25, $y, 85, $h);
    $pdf->MultiCell(85, 6, $productstatic->label, 0, 'L', 0, 0);
    $pdf->SetXY($x + 110, $y);
    
    // Fournisseur
    $pdf->Rect($x + 110, $y, 45, $h);
    $pdf->MultiCell(45, 6, $fournisseur_name, 0, 'L', 0, 0);
    $pdf->SetXY($x + 155, $y);
    
    // Unité
    $pdf->Rect($x + 155, $y, 20, $h);
    $pdf->MultiCell(20, $h, $unit_label, 0, 'C', 0, 0);
    $pdf->SetXY($x + 175, $y);
    
    // Besoin
    $pdf->Rect($x + 175, $y, 15, $h);
    $qty_display = $value['nb'] . ($value['globalqty'] == 1 ? ' G' : '');
    $pdf->MultiCell(15, $h, $qty_display, 0, 'C', 0, 0);
    
    $pdf->SetXY($x, $y + $h);
}

ob_clean(); // clean output buffer to avoid invalid PDF
$pdf->Output('Nomenclature_'.$object->ref.'.pdf', 'I');
?>
