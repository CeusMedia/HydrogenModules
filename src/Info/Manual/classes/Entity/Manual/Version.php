<?php
class Entity_Manual_Version
{
	public int|string $manualVersionId;
	public int|string $userId;
	public int|string $objectId;
	public int $type					= Model_Manual_Version::TYPE_PAGE;
	public int $version					= 1;
	public string $object;
	public string $timestamp;
}