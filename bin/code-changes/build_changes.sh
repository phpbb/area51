#!/usr/bin/env bash
mode="side-by-side"
basedir=`pwd`
outdir="../../web/code-changes/"
outdir_abs=`cd $outdir; pwd`
latest="3.0.10"
releases="3.0.0 3.0.1 3.0.2 3.0.3 3.0.4 3.0.5 3.0.6 3.0.7 3.0.7-PL1 3.0.8 3.0.9"

if [ ! -d "$basedir/repo/phpBB" ];
then
	git clone git://github.com/phpbb/phpbb3.git "$basedir"/repo
	code=$?
	if [ $code -ne 0 ];
	then
		exit $code
	fi
fi

# Update the repository
cd "$basedir"/repo
git fetch --quiet
git reset --quiet --hard HEAD

# Grab the latest version
if [ ! -d "$basedir/versions/$latest" ];
then
	echo "Checking out latest version"
	git checkout release-$latest --quiet
	mkdir "$basedir"/versions/$latest
	cp -R phpBB/. "$basedir"/versions/$latest
fi

# Copy the older release files
for tag in $releases
do
	if [ ! -d "$basedir/versions/$tag" ];
	then
		echo "Checking out $tag"
		git checkout release-$tag --quiet
		mkdir "$basedir"/versions/$tag
		cp -R phpBB/. "$basedir"/versions/$tag
	fi
done

# Generate the code changes
for tag in $releases
do
	if [ ! -d "$basedir/$tag/$mode/$latest" ];
	then
		echo "Building code changes for $tag-$latest"
		mkdir -p "$outdir_abs"/$tag/$mode/$latest
		git diff --name-status release-$tag release-$latest > log.txt
		cd ..
		php "$basedir"/create_diffs.php "$outdir" $tag $latest side-by-side "$basedir"/repo/log.txt
		cd "$basedir"/repo
		rm log.txt
	fi
done

echo "Done"
