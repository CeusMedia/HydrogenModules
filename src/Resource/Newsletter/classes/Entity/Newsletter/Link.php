<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Newsletter_Link extends Entity
{
	public int|string $newsletterLinkId		= 0;
	public string $url						= '';
	public string $title					= '';
}