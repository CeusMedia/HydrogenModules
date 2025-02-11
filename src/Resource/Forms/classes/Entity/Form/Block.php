<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Form_Block extends Entity
{
	public int|string $blockId;
	public string $title;
	public string $identifier;
	public string $content			= '';
}