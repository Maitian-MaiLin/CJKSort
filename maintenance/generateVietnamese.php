<?php

if (PHP_SAPI !== 'cli') {
	exit;
}

function uchr($code) {
	return html_entity_decode('&#' . $code . ';', ENT_NOQUOTES, 'UTF-8');
}

$file = file_get_contents(__DIR__ . '/Unihan/Unihan_Readings.txt');
$lines = explode("\n", $file);

$output = <<<EOT
<?php

/**
 * Vietnamese Conversion Table
 * Data comes from Unihan_Readings.txt
 * 
 * Automatically generated using maintenance/generateVietnamese.php
 * Do not modify directly!
 *
 */

return [

EOT;

foreach ($lines as $line) {
	$line = trim($line);
	// 跳过空行
	if (!$line) {
		continue;
	}
	// 跳过注释行
	if ($line[0] === '#') {
		continue;
	}
	$comp = explode("\t", $line);
	// 仅越南语
	if ($comp[1] !== 'kVietnamese') {
		continue;
	}

	// 解码
	$code = hexdec(str_replace('U+', '', $comp[0]));
	$char = uchr($code);

	// 格式化
	$vietnamese = explode(' ', $comp[2])[0];
	$vietnamese = mb_ucfirst($vietnamese);
	$output .= "\t'{$char}' => '{$vietnamese}',\n";
}

$output .= "];\n";
file_put_contents(__DIR__ . '/../includes/ConversionTables/vietnamese.data.php', $output);
