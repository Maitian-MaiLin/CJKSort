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
 * Contonese — Jyutping Conversion Table
 * Data comes from Unihan_Readings.txt
 * 
 * Automatically generated using maintenance/generateJyutping.php
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
	// 仅粤语（广东话）
	if ($comp[1] !== 'kCantonese') {
		continue;
	}

	// 解码
	$code = hexdec(str_replace('U+', '', $comp[0]));
	$char = uchr($code);

	// 格式化
	$jyutping = explode(' ', $comp[2])[0];
	$jyutping = mb_ucfirst($jyutping);
	$output .= "\t'{$char}' => '{$jyutping}',\n";
}

$output .= "];\n";
file_put_contents(__DIR__ . '/../includes/ConversionTables/jyutping.data.php', $output);
