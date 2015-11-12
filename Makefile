set-rights:
	@find . -type d -print0 | xargs -0 xargs chmod 775
	@find . -type f -print0 | xargs -0 xargs chmod 755
