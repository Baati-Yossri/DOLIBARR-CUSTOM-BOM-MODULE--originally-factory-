<?php
$file = 'c:\dolibarr\www\technoprod\custom\factory\class\factory.class.php';
$content = file_get_contents($file);

$pattern = '/if \(\$option == \'index\'\) \{.*?\} else \{\s*\$lien = \'<a href="\'.DOL_URL_ROOT.\'\/product\/fiche\.php\?id=\'.\$id.\'">\';\s*\$lienfin=\'<\/a>\';\s*\}/s';

$replacement = '$lien = \'<a href="\'.DOL_URL_ROOT.\'/product/card.php?id=\'.$id.\'">\';
		$lienfin=\'</a>\';';

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $content);
echo "Done";
