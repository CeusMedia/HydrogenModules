PERMS_DIR	= 755
PERMS_FILE	= 644

#PERMS_DIR	= 775
#PERMS_FILE	= 755

set-rights:
#	@sudo find . -type d -print0 | xargs -0 xargs chmod ${PERMS_DIR}
#	@sudo find . -type f -print0 | xargs -0 xargs chmod ${PERMS_FILE}
	@sudo find . -type d -print0 | xargs -0 chmod ${PERMS_DIR}
	@sudo find . -type f -print0 | xargs -0 chmod ${PERMS_FILE}

dev-show-old-hooks:
	@echo "- Hooks to extract:"
	@find . -type f | grep xml | xargs grep hook | grep CDATA

dev-test-units:
	@vendor/bin/phpunit

dev-test-syntax:
	@./migrate.php OldStructure::testSyntax

dev-test-syntax-parallel:
	@./vendor/bin/parallel-lint . -e php5 -j 10 --colors --exclude test --exclude vendor --exclude */templates/*

dev-show-work:
	@./migrate.php OldStructure::findFilesWhichNeedWork

dev-migrate-to-new-structure:
	@./migrate.php NewStructure::removePhpVersionInClassFileName
	@./migrate.php NewStructure::createLinksToOldStructure

dev-phpstan:
	@vendor/bin/phpstan analyse --configuration phpstan.neon --xdebug || true

dev-phpstan-save-baseline:
	@vendor/bin/phpstan analyse --configuration phpstan.neon --generate-baseline phpstan-baseline.neon || true
