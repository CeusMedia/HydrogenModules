<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Newsletter_Reader_Group extends Entity
{
	public int|string $newsletterReaderGroupId	= 0;
	public int|string $newsletterReaderId		= 0;
	public int|string $newsletterGroupId		= 0;
	public int $status							= Model_Newsletter_Reader_Group::STATUS_ASSIGNED;
	public int $createdAt						= 0;

}