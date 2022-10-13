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
		__DIR__ . '/Admin',
		__DIR__ . '/Base',
		__DIR__ . '/Catalog',
		__DIR__ . '/Info',
		__DIR__ . '/JS',
		__DIR__ . '/Manage',
		__DIR__ . '/Members',
		__DIR__ . '/Resource',
		__DIR__ . '/Security',
		__DIR__ . '/Server',
		__DIR__ . '/Shop',
		__DIR__ . '/Tool',
		__DIR__ . '/UI',
		__DIR__ . '/Work',
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
