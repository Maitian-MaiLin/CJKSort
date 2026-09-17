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
 * Korean — Hangul Conversion Table
 * Data comes from Unihan_Readings.txt
 * 
 * Automatically generated using maintenance/generateHangul.php
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
	// 仅朝鲜语／韩语
	if ($comp[1] !== 'kHangul') {
		continue;
	}

	// 解码
	$code = hexdec(str_replace('U+', '', $comp[0]));
	$char = uchr($code);

	// 格式化
	$hangul = explode(' ', $comp[2])[0];
	$hangul = explode(':', $hangul)[0];

	$output .= "\t'{$char}' => \"{$hangul}" . '\u{3130}' . "\",\n";
}

$output .= "];\n";
file_put_contents(__DIR__ . '/../includes/ConversionTables/hangul.data.php', $output);
