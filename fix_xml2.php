<?php
$file = 'c:\dolibarr\www\technoprod\custom\factory\class\factory.class.php';
$content = file_get_contents($file);

$pattern = '/if \(\$sxe === false\) \{\s*echo "Erreur lors du chargement du XML\\\\n";\s*foreach \(libxml_get_errors\(\) as \$error\) \{\s*echo "\\\\t", \$error->message;\s*\}\s*\}/';
$replacement = 'if ($sxe === false) {
  			echo "Erreur lors du chargement du XML\n";
  			foreach (libxml_get_errors() as $error) {
  				echo "\t", $error->message;
  			}
			return -1;
  		}';

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $content);
echo "Done";
