#!/usr/bin/env bash
mode="side-by-side"
basedir=`cd $(dirname "$BASH_SOURCE"); cd ..; pwd`
outdir=`cd "$basedir"; cd ./web/code-changes/; pwd`
datadir=`cd "$basedir"; cd ./data/code-changes/; pwd`
latest="3.1.9"
releases="3.1.0 3.1.1 3.1.2 3.1.3 3.1.4 3.1.5 3.1.6 3.1.7 3.1.7-pl1 3.1.8"

# Set up the file structure
if [ ! -d "$datadir/repo" ];
then
	mkdir "$datadir"/repo
fi

if [ ! -d "$datadir/versions" ];
then
	mkdir "$datadir"/versions
fi

# Clone the repository
if [ ! -d "$datadir/repo/phpBB" ];
then
	git clone git://github.com/phpbb/phpbb3.git "$datadir"/repo
	code=$?
	if [ $code -ne 0 ];
	then
		exit $code
	fi
fi

# Update the repository
cd "$datadir"/repo
rm -f log.txt		# Clean up interrupted runs

git fetch --quiet
git reset --quiet --hard HEAD

# Grab the latest version
if [ ! -d "$datadir/versions/$latest" ];
then
	echo "Checking out latest version"
	git checkout release-$latest --quiet
	mkdir "$datadir"/versions/$latest
	cp -R phpBB/. "$datadir"/versions/$latest
fi

# Copy the older release files
for tag in $releases
do
	if [ ! -d "$datadir/versions/$tag" ];
	then
		echo "Checking out $tag"
		git checkout release-$tag --quiet
		mkdir "$datadir"/versions/$tag
		cp -R phpBB/. "$datadir"/versions/$tag
	fi
done

# Generate the code changes
for tag in $releases
do
	echo "Building code changes for $tag-$latest"
	if [ ! -d "$outdir/$tag/$mode/$latest" ];
	then
		mkdir -p "$outdir"/$tag/$mode/$latest
	fi

	git diff --name-status release-$tag release-$latest > "$datadir"/log.txt
	php "$basedir"/src/code-changes/create_diffs.php "$outdir" "$datadir" $tag $latest side-by-side "$datadir"/log.txt
	rm -f "$datadir"/log.txt
done

echo "Done"
