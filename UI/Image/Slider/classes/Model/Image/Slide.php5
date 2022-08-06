<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image_Slide extends Model
{
	protected $name		= 'slider_slides';

	protected $columns	= array(
		'sliderSlideId',
		'sliderId',
		'status',
		'source',
		'title',
		'content',
		'link',
		'rank',
		'timestamp',
	);

	protected $primaryKey	= 'sliderSlideId';

	protected $indices		= array(
		'sliderId',
		'status',
		'source',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
