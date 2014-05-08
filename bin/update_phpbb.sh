#!/usr/bin/env bash
set -e

REMOTE=origin
BRANCH=master

cd area51-phpbb3
git fetch "$REMOTE"

if [ `git rev-parse "$BRANCH"` != `git rev-parse "$REMOTE/$BRANCH"` ]
then
	git reset --hard "$REMOTE/$BRANCH"
	php -f ./phpBB/bin/phpbbcli.php db:migrate
fi
