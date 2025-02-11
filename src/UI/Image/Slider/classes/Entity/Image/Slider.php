<?php
declare(strict_types=1);

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Entity;

class Entity_Image_Slider extends Entity
{
	public int|string $sliderId			= 0;
	public int|string $creatorId		= 0;
	public int $status					= Model_Image_Slider::STATUS_HIDDEN;
	public string $title;
	public int $width;
	public int $height;
	public string $path;
	public int $durationShow;
	public int $durationSlide;
	public string $animation;
	public string $easing				= 'linear';
	public bool $randomOrder			= FALSE;
	public bool $showButtons			= FALSE;
	public bool $showDots				= FALSE;
	public bool $showTitle				= FALSE;
	public bool $scaleToFit				= FALSE;
	public int $views					= 0;
	public int $createdAt;
	public ?int $modifiedAt				= NULL;

	public array $slides				= [];

	public function __construct( Dictionary|array $data = [] )
	{
		$this->createdAt	= time();
		parent::__construct( $data );
	}
}