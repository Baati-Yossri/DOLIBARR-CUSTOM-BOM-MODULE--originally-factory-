<?php
$file = 'c:\dolibarr\www\technoprod\custom\factory\class\factory.class.php';
$content = file_get_contents($file);

$pattern = '/foreach \(\$tblfields as \$fields\) \{\s*\$this->add_component\(\$this->id, \$fields\[\'productid\'\],\s*\$fields\[\'nb\'\],\s*\$fields\[\'pmp\'\],\s*\$fields\[\'price\'\],\s*\$fields\[\'globalqty\'\],\s*\$fields\[\'description\'\],\s*\$fields\[\'ordercomponent\'\],\s*\$fields\[\'fk_entrepot\'\]\s*\);\s*\}/s';

$replacement = 'foreach ($tblfields as $fields) {
			// Prevent array conversion errors from empty XML tags
			foreach($fields as $k => $v) {
				if (is_array($v)) $fields[$k] = "";
			}
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

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $content);
echo "Done";
