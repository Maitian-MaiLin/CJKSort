<?php

namespace CJKSort;
use Collation;
class HanChrCollation extends Collation {
	private ?array $conversionTable = null;
	private ?string $rangeSeparator = null;
	
	// 可用映射表
	private const CONVERSION_TABLES = [
		'pinyin'   => ['pinyin'],
		'zhuyin'   => ['zhuyin'],
		'hiragana' => ['hiragana', 'katakana'],
		'katakana' => ['katakana', 'hiragana'],
		'hangul'   => ['hangul'],
		'jyutping' => ['jyutping', 'pinyin'],
		'kangxi'   => ['kangxi'],
		'vietnamese' => ['vietnamese'],
	];

	// 获取键名
	protected function getCollationKey(): string {
		global $wgCategoryCollation;
		$collationKey = is_string( $wgCategoryCollation ) ? $wgCategoryCollation : '';
		$collationKey = strtok($collationKey, ':');
		return $collationKey;
	}

	// 获取映射表名单
	protected function getCollationTables(): array {
		$collationTables = self::CONVERSION_TABLES[$this->getCollationKey()] ?? [];
		global $wgCJKSortAutoFallback;
		if ($wgCJKSortAutoFallback == false) {
			$collationTables = array_slice( $collationTables, 0, 1);
		};
		global $wgCJKSortWithNumericHanChr;
		if ($wgCJKSortWithNumericHanChr == true) {
			array_unshift($collationTables, 'numeric');
		};
		return $collationTables;
	}

	// 加载完整映射表
	protected function loadConversionTable(): array {
		if ( $this->conversionTable === null ) {
			$this->conversionTable = [];
			foreach ( $this->getCollationTables() as $type ) {
				$file = __DIR__ . "/ConversionTables/{$type}.data.php";
				if ( is_file( $file ) ) {
					$this->conversionTable += require $file;
				}
			}
		}
		return $this->conversionTable;
	}

	// 页面指定规则加载的映射表
	protected function loadConversionTableForRule( string $rule ): array {
		$tables = $this->getCollationTablesFromRule( $rule );
		$conversionTable = [];
		foreach ( $tables as $type ) {
			$file = __DIR__ . "/ConversionTables/{$type}.data.php";
			if ( is_file( $file ) ) {
				$conversionTable += require $file;
			}
		}

		return $conversionTable;
	}

	// 将汉字转换成排序键
	public function convertHanChr2SortKey( $string ): string {
		$convTable = $this->loadConversionTable();
		global $wgCJKSortWithTone;
		$withTone = $wgCJKSortWithTone;
		$chrs = mb_str_split( $string, 1, 'UTF-8' );

		$chrs = array_map( static function ( $chr ) use ( $convTable, $withTone ) {
			// 表外字符
			if ( mb_ord( $chr, 'UTF-8' ) < 0x3000 || !array_key_exists( $chr, $convTable ) ) {
				return $chr;
			}
			// 表内字符
			$sortKey = $convTable[$chr];
			// 是否去除声调
			global $wgCJKSortWithTone;
			if ( !$wgCJKSortWithTone) { 
				$sortKey = rtrim( $sortKey, '0123456789' );
			}
			return $sortKey;
		}, $chrs );

		return implode( '', $chrs );
	}

	// 设置排序键
	public function getSortKey( $string ) {
		global $wgCJKSortNoPrefix;
		if ( $wgCJKSortNoPrefix == true ) {
			$string = $this->preprocessPrefix( $string );
		}

		$key = ucfirst( $this->convertHanChr2SortKey( $string ) );

		global $wgCJKSortWithNumericSorting;
		if ( $wgCJKSortWithNumericSorting == true ) {
			$key = $this->getNumericSortKey( $key );
		}

		if ($key === $string) {
			return trim( $string );
		} else {
			return trim( "$key $string" );
		}
	}

	// 设置索引键
	public function getFirstLetter( $string ) {
		global $wgCJKSortNoPrefix;
		if ( $wgCJKSortNoPrefix == true ) {
			$string = $this->preprocessPrefix( $string );
		}

		$firstChar = mb_substr( $string, 0, 1, 'UTF-8' );
		$sort = $this->convertHanChr2SortKey( $firstChar );

		global $wgCJKSortWithNumericSorting;
		if ( $wgCJKSortWithNumericSorting && preg_match( '/^\d/', $sort ) ) {
			return wfMessage( 'category-header-numerals' )
				->numParams( 0, 9 )
				->text();
		}

		return ucfirst( mb_substr( $sort, 0, 1, 'UTF-8' ) );
	}

	// 前导自然数排序
	protected function getNumericSortKey( string $string ): string {
		global $wgCJKSortWithNumericHanChr;
		if ( $wgCJKSortWithNumericHanChr == true ) {
			return preg_replace_callback( '/\d[\d#]*/', static function ( $matches ) {
				$number = ltrim( $matches[0], '0' );
				$len = strlen( $number );
				$prefix = chr( intdiv( $len, 256 ) ) . chr( $len % 256 );
				return '0' . $prefix . $number;
			}, $string );
		} else {
			return preg_replace_callback( '/\d+/', static function ( $matches ) {
				$number = ltrim( $matches[0], '0' );
				$len = strlen( $number );
				$prefix = chr( intdiv( $len, 256 ) ) . chr( $len % 256 );
				return '0' . $prefix . $number;
			}, $string );
		} 
	}

	// 排序键去除前缀
	private function preprocessPrefix( $string ) {
		if ( strpos( $string, "\n" ) !== false ) {
			return $string;
		}

		$parts = explode( ':', $string, 2 );

		if ( !( $parts[1] ?? null ) ) {
			return $string;
		}

		return "{$parts[1]}\n$string";
	}
}
