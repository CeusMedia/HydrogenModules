<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image_Slider extends Model
{
	protected string $name		= 'sliders';

	protected array $columns	= array(
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
	);

	protected string $primaryKey	= 'sliderId';

	protected array $indices		= array(
		'creatorId',
		'status',
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
