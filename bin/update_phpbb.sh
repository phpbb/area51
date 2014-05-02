#!/usr/bin/env bash

cd area51-phpbb3
git fetch origin
git reset --hard origin/master
php -f ./phpBB/bin/phpbbcli.php db:migrate

