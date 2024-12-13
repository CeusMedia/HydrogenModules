<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image_Slide extends Model
{
	const STATUS_DISABLED	= -1;
	const STATUS_HIDDEN		= 0;
	const STATUS_ENABLED	= 1;
	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_HIDDEN,

	];

	const ANIMATION_FADE	= 'fade';
	const ANIMATION_SLIDE	= 'slide';
	const ANIMATION_RANDOM	= 'random';
	const ANIMATIONS		= [
		self::ANIMATION_FADE,
		self::ANIMATION_SLIDE,
		self::ANIMATION_RANDOM,
	];

	protected string $name			= 'slider_slides';

	protected array $columns		= [
		'sliderSlideId',
		'sliderId',
		'status',
		'source',
		'title',
		'content',
		'link',
		'rank',
		'timestamp',
	];

	protected string $primaryKey	= 'sliderSlideId';

	protected array $indices		= [
		'sliderId',
		'status',
		'source',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Image_Slide::class;
}
