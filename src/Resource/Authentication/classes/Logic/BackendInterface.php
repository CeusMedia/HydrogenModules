<?php

interface Logic_Authentication_BackendInterface
{
	public function checkPassword( int|string $userId, string $password ): bool;
	public function clearCurrentUser(): void;
	public function getCurrentRole( bool $strict = TRUE ): ?object;
	public function getCurrentRoleId( bool $strict = TRUE ): int|string|NULL;
	public function getCurrentUser( bool $strict = TRUE, bool $withRole = FALSE ): ?object;
	public function getCurrentUserId( bool $strict = TRUE ): int|string|NULL;
	public function isAuthenticated(): bool;
	public function isIdentified(): bool;
	public function isCurrentUserId( int|string $userId): bool;
	public function noteUserActivity(): self;
	public function setAuthenticatedUser( object $user ): self;
	public function setIdentifiedUser( object $user ): self;
}
