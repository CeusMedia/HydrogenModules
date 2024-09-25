<?php

class Entity_Mission_Change
{
	public int|string $missionChangeId;
	public int|string $missionId;
	public int|string $userId;
	public int $type;
	public string $data;
	public string $timestamp;
}