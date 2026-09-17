<?php

if (PHP_SAPI !== 'cli') {
	exit;
}

function uchr($code) {
	return html_entity_decode('&#' . $code . ';', ENT_NOQUOTES, 'UTF-8');
}

$file = file_get_contents(__DIR__ . '/Unihan/Unihan_Readings.txt');
$lines = explode("\n", $file);

$fisrtConvert = [
	// 舌尖元音音节
	'zhi' => 'ㄓ', 'chi' => 'ㄔ', 'shi' => 'ㄕ', 'ri'  => 'ㄖ',
	'zi'  => 'ㄗ', 'ci'  => 'ㄘ', 'si'  => 'ㄙ',
	// ㄩ行
	'yong' => 'ㄩㄥ',
	'yun' => 'ㄩㄝ',
	'yuan' => 'ㄩㄢ',
	'yue' => 'ㄩㄝ',	
	// ㄨ行
	'wong' => 'ㄨㄥ',
	'weng' => 'ㄨㄥ',
	'wang' => 'ㄨㄤ',
	'wen' => 'ㄨㄣ',
	'wan' => 'ㄨㄢ',
	'wei' => 'ㄨㄟ',
	'wai' => 'ㄨㄞ',
	'wo' => 'ㄨㄛ',
	'wa' => 'ㄨㄚ',
	// ㄨ行
	'ying' => 'ㄧㄥ',
	'yang' => 'ㄧㄤ',	
	'yin' => 'ㄧㄣ',
	'yan' => 'ㄧㄢ',
	'you' => 'ㄧㄡ',
	'yao' => 'ㄧㄠ',
	'ye' => 'ㄧㄝ',
	'ya' => 'ㄧㄚ',
	// 介音原型
	'yi' => 'ㄧ',
	'wu' => 'ㄨ',
	'yu' => 'ㄩ',
];


$secondConvert = [
	// ㄩ行
	'iong' => 'ㄩㄥ',
	'vn' => 'ㄩㄝ',
	'van' => 'ㄩㄢ',
	've' => 'ㄩㄝ',	
	// ㄨ行
	'ong' => 'ㄨㄥ',
	'ueng' => 'ㄨㄥ',
	'uang' => 'ㄨㄤ',
	'uen' => 'ㄨㄣ',
	'uan' => 'ㄨㄢ',
	'ui' => 'ㄨㄟ',
	'uai' => 'ㄨㄞ',
	'uo' => 'ㄨㄛ',
	'ua' => 'ㄨㄚ',
	// ㄨ行
	'ing' => 'ㄧㄥ',
	'iang' => 'ㄧㄤ',	
	'in' => 'ㄧㄣ',
	'ian' => 'ㄧㄢ',
	'iu' => 'ㄧㄡ',
	'iao' => 'ㄧㄠ',
	'ie' => 'ㄧㄝ',
	'ia' => 'ㄧㄚ',
	// 韵尾（无介音）
	'er' => 'ㄦ',
	'eng' => 'ㄥ',
	'ang' => 'ㄤ',
	'en' => 'ㄣ',
	'an' => 'ㄢ',
	'ou' => 'ㄡ',
	'ao' => 'ㄠ',
	'ei' => 'ㄟ',
	'ai' => 'ㄞ',
	'ê' => 'ㄝ',
	'e' => 'ㄜ',
	'o' => 'ㄛ',
	'a' => 'ㄚ',
	// 介音原型
	'i' => 'ㄧ',
	'u' => 'ㄨ',
	'v' => 'ㄩ',
	// 杂项
	'ng' => 'ㄫ',
];

$thirdConvert = [
	// 声母
	'b'  => 'ㄅ', 'p'  => 'ㄆ', 'm'  => 'ㄇ', 'f'  => 'ㄈ',
	'd'  => 'ㄉ', 't'  => 'ㄊ', 'n'  => 'ㄋ', 'l'  => 'ㄌ',
	'g'  => 'ㄍ', 'k'  => 'ㄎ', 'h'  => 'ㄏ',
	'j'  => 'ㄐ', 'q'  => 'ㄑ', 'x'  => 'ㄒ',
	'zh' => 'ㄓ', 'ch' => 'ㄔ', 'sh' => 'ㄕ', 'r'  => 'ㄖ',
	'z'  => 'ㄗ', 'c'  => 'ㄘ', 's'  => 'ㄙ',
];

$output = <<<EOT
<?php

/**
 * Modern Standard Chinese (Mandarin) — Zhuyin (Bopomofo) Conversion Table
 * Data comes from Unihan_Readings.txt
 * 
 * Automatically generated using maintenance/generateZhuyin.php
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
		$tone = '1';
	} elseif (preg_match('/[áéíóúǘńḿ]/u', $pinyin)) {
		$tone = '2';
	} elseif (preg_match('/[ǎěǐǒǔǚň]/u', $pinyin)) {
		$tone = '3';
	} elseif (preg_match('/[àèìòùǜǹ]/u', $pinyin)) {
		$tone = '4';
	} else {
		$tone = '5';
	}

	$pinyin = preg_replace('/[āáǎà]/u', 'a', $pinyin);
	$pinyin = preg_replace('/[īíǐì]/u', 'i', $pinyin);
	$pinyin = preg_replace('/[ūúǔù]/u', 'u', $pinyin);
	$pinyin = preg_replace('/[ēéěè]/u', 'e', $pinyin);
	$pinyin = preg_replace('/[ōóǒò]/u', 'o', $pinyin);
	$pinyin = preg_replace('/[ǖǘǚǜü]/u', 'v', $pinyin);
	$pinyin = preg_replace('/[ňňǹ]/u', 'n', $pinyin);
	$pinyin = preg_replace('/[ḿ]/u', 'm', $pinyin);

	$zhuyin = strtr($pinyin, $fisrtConvert);
	$zhuyin = strtr($zhuyin, $secondConvert);
	$zhuyin = strtr($zhuyin, $thirdConvert);

	$output .= "\t'{$char}' => \"{$zhuyin}" . '\u{3100}'. "{$tone}\",\n";
}

$output .= "];\n";
file_put_contents(__DIR__ . '/../includes/ConversionTables/zhuyin.data.php', $output);
