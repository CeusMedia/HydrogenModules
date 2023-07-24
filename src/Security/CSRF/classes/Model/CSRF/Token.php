<?php
/**
 *	Data model of CSRF tokens.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of CSRF tokens.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_CSRF_Token extends Model
{
	protected string $name			= 'csrf_tokens';

	protected array $columns		= [
		"tokenId",
		"status",
		"token",
		"sessionId",
		"ip",
		"formName",
		"timestamp",
	];

	protected string $primaryKey	= 'tokenId';

	protected array $indices		= [
		"status",
		"token",
		"sessionId",
		"ip",
		"formName",
		"timestamp",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
