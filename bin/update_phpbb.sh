#!/usr/bin/env bash
set -e

cd area51-phpbb3
git fetch origin
git reset --hard origin/master
php -f ./phpBB/bin/phpbbcli.php db:migrate

