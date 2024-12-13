<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image_Slider extends Model
{
	const STATUS_DISABLED	= -1;
	const STATUS_HIDDEN		= 0;
	const STATUS_ENABLED	= 1;
	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_HIDDEN,

	];

	protected string $name			= 'sliders';

	protected array $columns		= [
		'sliderId',
		'creatorId',
		'status',
		'title',
		'width',
		'height',
		'path',
		'durationShow',
		'durationSlide',
		'animation',
		'easing',
		'randomOrder',
		'showButtons',
		'showDots',
		'showTitle',
		'scaleToFit',
		'views',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'sliderId';

	protected array $indices		= [
		'creatorId',
		'status',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Image_Slider::class;
}
