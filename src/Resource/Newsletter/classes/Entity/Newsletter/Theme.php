<?php

use CeusMedia\Common\Alg\ID;

class Entity_Newsletter_Theme
{
	public string $id;
	public string $title;
	public string $version;
	public string $created;
	public string $modified;
	/** @var ?object{address: string, name: ?string} $sender */
	public ?object $sender		= NULL;
	public ?string $imprint		= NULL;
	public ?string $styles		= NULL;

	/** @var ?object{name: string, email: ?string, company: ?string, url: ?string} $author */
	public ?object $author		= NULL;
	public ?string $license		= NULL;
	public ?string $licenseUrl	= NULL;
	public ?string $description	= NULL;

	public ?string $folder		= NULL;

	public function __construct()
	{
		$this->sender	= (object) [
			'address'	=> NULL,
			'name'		=> NULL,
		];
		$this->author	= (object) [
			'name'		=> NULL,
			'email'		=> NULL,
			'company'	=> NULL,
			'url'		=> NULL,
		];
		$this->id		= ID::uuid();
		$this->created	= date( 'c', time() );
		$this->modified	= date( 'c', time() );
	}
}