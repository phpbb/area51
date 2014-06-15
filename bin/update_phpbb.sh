#!/usr/bin/env bash
set -e

REMOTE=origin
BRANCH=master

HERE=$(dirname "$0")
cd "$HERE/../area51-phpbb3/phpBB"
git fetch "$REMOTE"

if [ `git rev-parse "$BRANCH"` != `git rev-parse "$REMOTE/$BRANCH"` ]
then
	git reset --hard "$REMOTE/$BRANCH"
	../composer.phar install --no-dev
	bin/phpbbcli.php db:migrate
	bin/phpbbcli.php cache:purge
	rm cache/*.{lock,php}
fi
