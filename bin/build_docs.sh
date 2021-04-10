#!/usr/bin/env bash
exec {BASH_XTRACEFD}>&1
set -ex

ROOT=`cd $(dirname "$BASH_SOURCE"); cd ..; pwd`
DOCS="$ROOT/web/docs"
PHPBBREPO="$ROOT/docs-phpbb"
DOCREPO="$ROOT/documentation"

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

# $1 repository directory
# $2 branch name
# $3 target directory
copy_phpbb_docs()
{
    cd $1
	mkdir -p "$3"
	git checkout --force "$2"
	git reset --hard "origin/$2"
	rsync -a --delete 'phpBB/docs/' "$3"
}

# $1 repository directory
# $2 branch name
# $3 target directory
make_sphinx_docs()
{
    cd $1
	mkdir -p "$3"
	git checkout --force "$2"
	git reset --hard "origin/$2"
    cd development
    make html
	rsync -a --delete '_build/html/' "$3"
}

update_repo "$PHPBBREPO" 'https://github.com/phpbb/phpbb.git' "$ROOT/area51-phpbb3"
update_repo "$DOCREPO" 'https://github.com/phpbb/documentation.git'

# Copy phpBB docs directory
copy_phpbb_docs $PHPBBREPO "3.0.x" "$DOCS/30x/"
copy_phpbb_docs $PHPBBREPO "3.1.x" "$DOCS/31x/"
copy_phpbb_docs $PHPBBREPO "3.2.x" "$DOCS/32x/"
copy_phpbb_docs $PHPBBREPO "3.3.x" "$DOCS/33x/"
copy_phpbb_docs $PHPBBREPO "master" "$DOCS/master/"

cd $DOCREPO
# Create documentation and copy master to main directory.
# Sphinx-multiversion does no longer create a copy of the master version
# in the main directory so we have to manually copy the files.
sphinx-multiversion development "$DOCS/dev/"
cp -r "$DOCS/dev/master/*" "$DOCS/dev/"

# Generate API documentation for 3.3.x and master
cd $PHPBBREPO
git checkout 3.3.x
cd phpBB
../composer.phar install
cd ../build
../phpBB/vendor/bin/phing docs-all
rsync -a --delete api/output/ "$DOCS/code/"
