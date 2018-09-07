PERMS_DIR	= 755
PERMS_FILE	= 644

#PERMS_DIR	= 775
#PERMS_FILE	= 755

set-rights:
#	@sudo find . -type d -print0 | xargs -0 xargs chmod ${PERMS_DIR}
#	@sudo find . -type f -print0 | xargs -0 xargs chmod ${PERMS_FILE}
	@sudo find . -type d -print0 | xargs -0 chmod ${PERMS_DIR}
	@sudo find . -type f -print0 | xargs -0 chmod ${PERMS_FILE}

