<?php

namespace CJKSort;
use Parser;
class CJKSORT {

	// 注册 {{CJKSORT}}
	public static function onParserFirstCallInit( Parser $parser ): void {
		$parser->setFunctionHook(
			'cjksort', [ self::class, 'render' ], Parser::SFH_NO_HASH
		);
	}

	// {{CJKSORT:<排序键>|<规则>}}
	public static function render(
		Parser $parser,
		$sortKey = '',
		$rule = ''
	): string {
		$sortKey = trim( $sortKey );
		$rule = trim( $rule );
		if ( $sortKey === '' ) return '';

		// 没有指定规则使用 $wgCategoryCollation
		if ( $rule === '' ) {
			global $wgCategoryCollation;
			$rule = is_string( $wgCategoryCollation )
				? strtok( $wgCategoryCollation, ':' )
				: '';
		} else {
			$rule = strtok( $rule, ':' );
		}

		// 处理用户排序键
		$sortKey = self::processSortKey( $sortKey, $rule );
		// 直接设置{{DEFAULTSORT}}得了，造什么轮子（划掉）
		$parser->getOutput()->setPageProperty( 'defaultsort', $sortKey );
		// {{CJKSORT}}不需要页面输出
		return '';
	}

	// 对自定义排序键的分词处理
	private static function processSortKey(
		string $input,
		string $rule
	): string {
		$result = [];
		$length = strlen( $input );
		$i = 0;
		while ( $i < $length ) {
			// 方括号包裹内容保持原样
			$chr = mb_substr( $input, $i, 1, 'UTF-8' );
			if ( $chr === '[' ) {
				$close = strpos( $input, ']', $i + 1, 'UTF-8');
				if ( $close !== false ) {
					$result[] = mb_substr( $input, $i, $close - $i + 1, 'UTF-8');
					$i = $close + 1;
					continue;
				}
			}

			// 空格作为分词符不加入最终结果
			if ( preg_match( '/^\s$/u', $chr ) ) {
				$i++;
				continue;
			}

			while ( $i < $length ) {
				$chr = mb_substr( $input, $i, 1, 'UTF-8' );
				// 空格结束
				if ( preg_match( '/^\s$/u', $chr ) ) {
					break;
				}
				// 方括号结束
				if ( $chr === '[' ) {
					break;
				}
				$word .= $chr;
				$i++;
			}

			if ( $word !== '' ) {
				$result[] = self::processWord( $word, $rule );
			}

			// 如果当前是空格下一轮跳过它
			if ( $i < $length ) {
				$chr = mb_substr( $input, $i, 1, 'UTF-8' );
				if ( preg_match( '/^\s$/u', $chr ) ) { $i++; }
			}
		}

		return implode( '', $result );
	}

	// 对自定义排序键的格式化处理
	private static function processWord(
		string $word,
		string $rule
	): string {
		global $wgCJKSortWithTone;
		// 去声调
		if ( $wgCJKSortWithTone == false && (
			$rule === 'pinyin' ||
			$rule === 'jyutping' ||
			$rule === 'zhuyin'
		) ) { $word = preg_replace( '/\d+/', '', $word ); }

		// 假名
		if (
			$rule === 'hiragana' ||
			$rule === 'katakana'
		) { return $word . "\u{3040}"; }

		// 谚文
		if ( $rule === 'hangul' ) {
			return $word . "\u{3130}";
		}

		// 注音
		if ( $rule === 'zhuyin' ) {
			if ( $wgCJKSortWithTone == false ) {
				$word = preg_replace( '/\d+/', '', $word );
				return $word . "\u{3100}";
			}
			return preg_replace( '/(\d+)/', "\u{3100}$1", $word );
		}

		// 首字母大写
		$word = ucfirst( $word );
		return $word;
	}
}