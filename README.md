# CJKSort 1.0.0

This extension is a derivative work based on [this fork version](https://github.com/wiki-gg-oss/mediawiki-extensions-PinyinSort) (wiki.gg) of 
[PinyinSort](https://github.com/nbdd0121/MW-PinyinSort).

Provides multiple methods for sorting Chinese characters for categories. Provide these following `<collation-name>` s:

- `hiragana` Hiragana (kun'yomi) — Japanese
- `katakana` Katakana (on'yomi) — Japanese
- `hangul` Hangul — Korean
- `jyutping` Jyutping — Contonese
- `pinyin` Pinyin — Modern Standard Chinese (Mandarin)
- `zhuyin` Zhuyin (Bopomofo) — Modern Standard Chinese (Mandarin)
- `kangxi` Kangxi Radicals
- `vietnamese` Vietnamese (beta? I haven't figured out yet how to use it with `uca-vi`)

## Install

- Clone the respository, rename it to `CJKSort` and copy to extensions folder.
- Add `wfLoadExtension('CJKSort')` ; to your LocalSettings.php
- Add `$wgCategoryCollation = '<collation-name>';` to your LocalSettings.php to activate PinyinSort.
  - You need to run `updateCollation.php` as an post-requisite for changing collation.
- You are done!

## Configuration
- `$wgCJKSortAutoFallback` — For example, when using `hiragana`, chinese characters without kun'yomi will be sorted by on'yomi.
- `$wgCJKSortWithTone` — For example, when using `pinyin`, the character "中" will be sorted as "Zhong1" rather than "Zhong".
- `$wgCJKSortWithNumericSorting` — Implement [numeric sorting](https://www.mediawiki.org/wiki/Manual:$wgCategoryCollation#Numeric_sorting) of MediaWiki in CJKSort.
- `$wgCJKSortWithNumericHanChr` — Sort basic numeric Chinese characters in 0 to 9. And before all other Chinese characters.
  - Also compatible with `$wgCJKSortWithNumeric` that under the "0~9" header.
- `$wgCJKSortnNoPrefix` — Automatically strip prefixes.
  - For example, "Subproject:PageA" will be transformed to "PageA" during collation process.

You need to run `updateCollation.php --force` as an post-requisite for changing configuration.

## License

- Code licensed under 2-Clause BSD License.
  - Text files of Unihan licensed under Unicode License, version 3; See <https://www.unicode.org/Public/UCD/latest/ucd/Unihan.zip> for details.
