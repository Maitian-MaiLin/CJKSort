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
 * Japanese — Katakana Conversion Table
 * Data comes from Unihan_Readings.txt
 *
 * Automatically generated using maintenance/generateKatakana.php
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
	// 仅日语
	if ($comp[1] !== 'kJapanese') {
		continue;
	}


	// 解码
	$code = hexdec(str_replace('U+', '', $comp[0]));
	$char = uchr($code);

	// 格式化
	$words = preg_split('/[\s\x{3000}]+/u', $comp[2], -1, PREG_SPLIT_NO_EMPTY);
	// 返回平假名
	foreach ($words as $word) {
        if (preg_match('/^\p{Katakana}+$/u', $word)) {
            $kana = $word;
			break;
        }
    }
	// 不返回片假名
	if ($kana === '') {
    	continue;
	}

	$output .= "\t'{$char}' => \"{$kana}" . '\u{3040}' . "\",\n";
	$kana = '';
}

$output .= "];\n";
file_put_contents(__DIR__ . '/../includes/ConversionTables/katakana.data.php', $output);
