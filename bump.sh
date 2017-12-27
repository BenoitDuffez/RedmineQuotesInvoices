#!/bin/bash
#
# version bump script
# Will not do anything but will just say the next tag and the changelog from last tag
# the first argument, if present, will be used as the new version tag name (format: v\d+\.\d+)
#

if [ ! -L .git/hooks/prepare-commit-msg ]; then
    echo "You don't have the prepare-commit-msg client-side git hook."
    echo "    This hook will prepare the commit message automatically when merging."
    echo
    echo "    To install it:"
    echo "      ln -s ../../prepare-commit-msg .git/hooks/"
fi

# Check we're on master
if ! grep -q master <<<"$(git branch | grep ^*)"; then
    {
        echo "You need to merge your development branches to master before bumping the version."
        echo "Use:"
        echo
        echo "  git checkout master && git pull && git merge --no-ff $(git branch | grep ^* | awk '{print $NF}') && $0 $@"
        echo
        echo "Then run this script again"
    } >&2
    exit 1
fi

# Update repo and build tag names
git fetch >/dev/null
lastTag=$(git tag --list | sort -t. -k 1,1n -k 2,2n -k 3,3n -k 4,4n | tail -1)
if [ "$1" == "" ]; then
    nextTag=$(echo "$lastTag" | awk -F. 'BEGIN{OFS="."}{$NF=($NF+1); print $0}')
else
    nextTag="$1"
fi
if grep -vqP 'v\d+\.\d+' <<<"$nextTag"; then
    echo "The version name must start with a v" >&2
    exit 1
fi

# Print tags and changelog
changelog=$(git log --pretty=format:'  * [%an] %s' HEAD...${lastTag} | grep -v 'Merge branch')
echo "Previous version: $lastTag"
echo "New version: $nextTag"
echo "Changelog: $changelog"

# Provide help to install next tag
echo "If all looks good, create a tag and push it:"
echo
echo "  git push && git tag -a $nextTag -m '$(echo $changelog | sed "s/'/\\'/g")' && git push --tags && git checkout develop"
echo

