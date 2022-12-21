<?php
/**
 *	Data model of CSRF tokens.
 *	@category		none
 *	@package		none
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of CSRF tokens.
 *	@category		none
 *	@package		none
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_CSRF_Token extends Model
{
	protected string $name		= 'csrf_tokens';

	protected array $columns	= array(
		"tokenId",
		"status",
		"token",
		"sessionId",
		"ip",
		"formName",
		"timestamp",
	);

	protected string $primaryKey	= 'tokenId';

	protected array $indices		= array(
		"status",
		"token",
		"sessionId",
		"ip",
		"formName",
		"timestamp",
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
