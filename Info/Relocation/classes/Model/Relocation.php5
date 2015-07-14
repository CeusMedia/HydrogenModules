<?php
class Model_Relocation extends CMF_Hydrogen_Model{
	protected $name		= 'relocations';
	protected $columns	= array(
		"relocationId",
		"creatorId",
		"status",
		"title",
		"url",
		"views",
		"createdAt",
		"usedAt",
	);
	protected $primaryKey	= 'relocationId';
	protected $indices		= array(
		"status",
		"url",
		"createdAt",
		"usedAt",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
