#!/usr/bin/env bash
set -ex

ROOT=`cd $(dirname "$BASH_SOURCE"); cd ..; pwd`
DOCS="$ROOT/web/docs"
REPO="$ROOT/docs-phpbb"

# $1 local directory
# $2 repo URL
# $3 reference repository
update_repo()
{
	if [ -d "$1" ]
	then
		cd "$1"
		git remote update
	else
		if [ -d "$3/.git" ]
		then
			CLONEARGS="--reference $3"
		else
			CLONEARGS=""
		fi

		git clone "$2" "$1" $CLONEARGS
		cd "$1"
		git config branch.autosetuprebase always
	fi
}

# $1 branch name
# $2 target directory
copy_phpbb_docs()
{
	mkdir -p "$2"
	git checkout --force "$1"
	git reset --hard "origin/$1"
	rsync -a --delete 'phpBB/docs/' "$2"
}

update_repo "$REPO" 'https://github.com/phpbb/phpbb.git' "$ROOT/area51-phpbb3"

# Copy phpBB docs directory
copy_phpbb_docs develop-olympus "$DOCS/30x/"
copy_phpbb_docs develop-ascraeus "$DOCS/31x/"

# Generate API documentation
cd phpBB
../composer.phar install
cd ../build
../phpBB/vendor/bin/phing docs-all
rsync -a --delete api/output/ "$DOCS/code/"
