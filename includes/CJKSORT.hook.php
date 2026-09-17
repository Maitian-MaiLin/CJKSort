<?php

namespace CJKSort;
class CJKSORTHook {
	public static function onCollationFactoryRegister( $factory ): void {
		$factory->register(
			'han', HanChrCollation::class
		);
	}
}