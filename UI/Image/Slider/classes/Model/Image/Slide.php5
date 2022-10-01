<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image_Slide extends Model
{
	protected string $name		= 'slider_slides';

	protected array $columns	= array(
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

	protected string $primaryKey	= 'sliderSlideId';

	protected array $indices		= array(
		'sliderId',
		'status',
		'source',
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
