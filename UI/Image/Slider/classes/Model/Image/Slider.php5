<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image_Slider extends Model
{
	protected $name		= 'sliders';

	protected $columns	= array(
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

	protected $primaryKey	= 'sliderId';

	protected $indices		= array(
		'creatorId',
		'status',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
