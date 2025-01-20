<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Form_Mail extends Entity
{
	public int|string $mailId;
	public int $roleType			= Model_Form_Mail::ROLE_TYPE_NONE;
	public string $identifier;
	public int $format				= Model_Form_Mail::FORMAT_TEXT;
	public string $subject;
	public string $title;
	public string $content;
}
