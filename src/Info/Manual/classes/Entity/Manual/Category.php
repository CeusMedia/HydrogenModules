<?php
class Entity_Manual_Category
{
	public int|string $manualCategoryId;
	public int|string|NULL $creatorId		= NULL;
	public int $status						= Model_Manual_Category::STATUS_NEW;
	public int $format						= Model_Manual_Category::FORMAT_TEXT;
	public int $version						= 1;
	public ?int $rank						= NULL;
	public string $title;
	public string $content;
	public string $createdAt;
	public ?string $modifiedAt				= NULL;

}