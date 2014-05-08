#!/usr/bin/env bash
set -e

REMOTE=origin
BRANCH=master

cd area51-phpbb3
git fetch "$REMOTE"
git reset --hard "$REMOTE/$BRANCH"
php -f ./phpBB/bin/phpbbcli.php db:migrate

