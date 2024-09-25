<?php

class Entity_Mission_Version
{
	public int|string $missionVersionId;
	public int|string $missionId;
	public int|string $userId;
	public int $version;
	public string $content;
	public string $timestamp;
}