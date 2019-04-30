#!/usr/bin/env bash
set -e

REMOTE=origin
BRANCH=master

HERE=$(dirname "$0")
cd "$HERE/../area51-phpbb3/phpBB"
git fetch "$REMOTE"

if [ `git rev-parse "$BRANCH"` != `git rev-parse "$REMOTE/$BRANCH"` ] && [ `git rev-parse "$BRANCH"` != beee215bc2d28159b37d5e24e37598fd3d03edbd ]
then
	git reset --hard "$REMOTE/$BRANCH"
	../composer.phar install --no-dev --optimize-autoloader
	bin/phpbbcli.php --safe-mode db:migrate
	bin/phpbbcli.php --safe-mode cache:purge
	rm cache/production/*.{lock,php}
	rm -r cache/production/twig/
fi
