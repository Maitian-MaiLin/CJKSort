<?php

namespace CJKSort;
use MediaWiki\Collation\Hook\Collation__factoryHook;
class CollationFactoryHandler implements Collation__factoryHook {

	public function onCollation__factory(
		$collationName,
		&$collationObject
	) {
		if ( in_array(
			$collationName,
			[
				'pinyin',
				'zhuyin',
				'hiragana',
				'katakana',
				'jyutping',
				'hangul',
				'kangxi',
				'vietnamese',
			],
			true
		) ) {
			$collationObject = new HanChrCollation();
		}

		return true;
	}
}