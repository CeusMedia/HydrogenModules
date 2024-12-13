<?php

class Entity_Image_Slide
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

	public static function fromArray( array $array): self
	{
		$instance	= new self();
		foreach( $array as $key => $value )
			if( property_exists( $instance, $key ) )
				$instance->{$key} = $value;
		return $instance;
	}

	public function __construct()
	{
		$this->timestamp	= time();
	}
}