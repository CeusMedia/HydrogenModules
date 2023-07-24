<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php56\Rector\FuncCall\PowToExpRector;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php73\Rector\BooleanOr\IsCountableRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php73\Rector\FuncCall\RegexDashEscapeRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
	$rectorConfig->paths([
		__DIR__ . '/src',
//		__DIR__ . '/src/Admin',
//		__DIR__ . '/src/App',
//		__DIR__ . '/src/Base',
//		__DIR__ . '/src/Catalog',
//		__DIR__ . '/src/Info',
//		__DIR__ . '/src/JS',
//		__DIR__ . '/src/Manage',
//		__DIR__ . '/src/Members',
//		__DIR__ . '/src/Resource',
//		__DIR__ . '/src/Security',
//		__DIR__ . '/src/Server',
//		__DIR__ . '/src/Shop',
//		__DIR__ . '/src/Tool',
//		__DIR__ . '/src/UI',
//		__DIR__ . '/src/Work',
	]);

	// register a single rule
//	$rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

	// define sets of rules
	$rectorConfig->sets([
		LevelSetList::UP_TO_PHP_73
	]);

	$skipFolders	= [];
	$skipFiles		= [];
	$skipRules		= [
		// Set 5.4
		LongArrayToShortArrayRector::class,
		// Set 5.5
		StringClassNameToClassConstantRector::class,
		// Set 5.6
		PowToExpRector::class,
		//	# inspired by level in psalm - https://github.com/vimeo/psalm/blob/82e0bcafac723fdf5007a31a7ae74af1736c9f6f/tests/FileManipulationTest.php#L1063
		AddDefaultValueForUndefinedVariableRector::class,
		// Set 7.1
		CountOnNullRector::class,
		// Set 7.3
		JsonThrowOnErrorRector::class,
		IsCountableRector::class,
		RegexDashEscapeRector::class,
	];
	$rectorConfig->skip(array_merge($skipFolders, $skipFiles, $skipRules));
};
