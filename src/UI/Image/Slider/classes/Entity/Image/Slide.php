<?php
declare(strict_types=1);

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Entity;

class Entity_Image_Slide extends Entity
{
	public int|string $sliderSlideId	= 0;
	public int|string $sliderId			= 0;
	public int $status					= Model_Image_Slide::STATUS_HIDDEN;
	public string $source;
	public ?string $title				= NULL;
	public ?string $content				= NULL;
	public ?string $link				= NULL;
	public int $rank					= 0;
	public int $timestamp;

	public function __construct( Dictionary|array $data = [] )
	{
		$this->timestamp	= time();
		parent::__construct( $data );
	}
}