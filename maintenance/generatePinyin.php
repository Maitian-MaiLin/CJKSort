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
 * Modern Standard Chinese (Mandarin) — Pinyin Conversion Table
 * Data comes from Unihan_Readings.txt
 * 
 * Automatically generated using maintenance/generatePinyin.php
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
	// 仅官话（现代标准汉语／普通话／国语）
	if ($comp[1] !== 'kMandarin') {
		continue;
	}

	// 解码
	$code = hexdec(str_replace('U+', '', $comp[0]));
	$char = uchr($code);

	// 格式化
	$pinyin = explode(' ', $comp[2])[0];

	if (preg_match('/[āēīōūǖ]/u', $pinyin)) {
		$tone = 1;
	} elseif (preg_match('/[áéíóúǘńḿ]/u', $pinyin)) {
		$tone = 2;
	} elseif (preg_match('/[ǎěǐǒǔǚň]/u', $pinyin)) {
		$tone = 3;
	} elseif (preg_match('/[àèìòùǜǹ]/u', $pinyin)) {
		$tone = 4;
	} else {
		$tone = 5;
	}

	$pinyin = preg_replace('/[āáǎà]/u', 'a', $pinyin);
	$pinyin = preg_replace('/[īíǐì]/u', 'i', $pinyin);
	$pinyin = preg_replace('/[ūúǔù]/u', 'u', $pinyin);
	$pinyin = preg_replace('/[ēéěèê]/u', 'e', $pinyin);
	$pinyin = preg_replace('/[ōóǒò]/u', 'o', $pinyin);
	$pinyin = preg_replace('/[ǖǘǚǜü]/u', 'v', $pinyin);
	$pinyin = preg_replace('/[ňňǹ]/u', 'v', $pinyin);
	$pinyin = preg_replace('/[ḿ]/u', 'v', $pinyin);
	$pinyin = mb_ucfirst($pinyin);

	$output .= "\t'{$char}' => '{$pinyin}{$tone}',\n";
}

$output .= "];\n";
file_put_contents(__DIR__ . '/../includes/ConversionTables/pinyin.data.php', $output);
