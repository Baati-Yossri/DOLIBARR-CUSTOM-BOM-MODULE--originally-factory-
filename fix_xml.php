<?php
$file = 'c:\dolibarr\www\technoprod\custom\factory\class\factory.class.php';
$content = file_get_contents($file);

$pattern1 = '/function getExportComposition\(\$tblCompositionLine\).*?return \$tmp;\s*\}/s';
$replacement1 = 'function getExportComposition($tblCompositionLine)
	{
		$tmp="<?xml version=\'1.0\' encoding=\'ISO-8859-1\'?>\n";
		$tmp.="<FactoryComposition>\n";
		$tmp.="<FactoryCompositionLines>\n";
		foreach ($tblCompositionLine as $key => $value) {
			$tmp.="\t".\'<FactoryCompositionLine>\'."\n";
			$tmp.="\t \t<productid>".$value[\'id\']."</productid>\n";
			$tmp.="\t \t<nb>".$value[\'nb\']."</nb>\n";
			$tmp.="\t \t<pmp>".$value[\'pmp\']."</pmp>\n";
			$tmp.="\t \t<price>".$value[\'price\']."</price>\n";
			$tmp.="\t \t<globalqty>".$value[\'globalqty\']."</globalqty>\n";
			$tmp.="\t \t<description>".$value[\'description\']."</description>\n";
			$tmp.="\t \t<ordercomponent>".$value[\'ordercomponent\']."</ordercomponent>\n";
			$tmp.="\t \t<fk_entrepot>".$value[\'fk_entrepot\']."</fk_entrepot>\n";
			$tmp.="\t".\'</FactoryCompositionLine>\'."\n";
		}
		$tmp.="</FactoryCompositionLines>\n";
		$tmp.="</FactoryComposition>\n";
		return $tmp;
	}';

$content = preg_replace($pattern1, $replacement1, $content);

$pattern2 = '/\$tblfields=\$arraydata\[\'FactoryCompositionLines\'\];\s*\$tblfields=\$tblfields\[\'FactoryCompositionLine\'\];\s*foreach \(\$tblfields as \$fields\) \{\s*\$this->add_component\(\$this->id, \$fields\[\'productid\'\],\s*\$fields\[\'nb\'\],\s*\$fields\[\'pmp\'\],\s*\$fields\[\'price\'\],\s*\$fields\[\'globalqty\'\],\s*\$fields\[\'description\'\],\s*\$fields\[\'ordercomponent\'\]\s*\);\s*\}/s';

$replacement2 = '$tblfields=$arraydata[\'FactoryCompositionLines\'];
		$tblfields=$tblfields[\'FactoryCompositionLine\'];
		
		if (isset($tblfields[\'productid\'])) { $tblfields = array($tblfields); } // Fix single-element array flattening

		foreach ($tblfields as $fields) {
			$this->add_component($this->id, $fields[\'productid\'],
							$fields[\'nb\'], 
							$fields[\'pmp\'], 
							$fields[\'price\'], 
							$fields[\'globalqty\'], 
							$fields[\'description\'],
							$fields[\'ordercomponent\'],
							$fields[\'fk_entrepot\']
			);
		}';

$content = preg_replace($pattern2, $replacement2, $content);

file_put_contents($file, $content);
echo "Done";
