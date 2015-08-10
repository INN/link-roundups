#!/bin/bash
RELEASE_DIR=release
SVN_PATH=$RELEASE_DIR/svn
SVN_REPO=https://plugins.svn.wordpress.org/link-roundups/

# which files go into the release?
GLOBIGNORE=*
WHITELIST=(LICENSE.txt package.json Gruntfile.js link-roundups.php contributing.md README.txt README.md css/* inc/* js/* lang/* less/* templates/* vendor/*)
BLACKLIST=(mkdocs.yml phpunit.xml requirements.txt docs/* node_modules/* tests/*)

# check the state of this git repo
REMOTES=`git ls-remote --quiet`
CURRENT=`git rev-parse HEAD`
IS_MASTER=`echo "$REMOTES" | grep "refs/heads/master" | grep $CURRENT | awk '{print $2;}'`
IS_TAG=`echo "$REMOTES" | grep "refs/tags" | grep $CURRENT | awk '{print $2;}'`
if [[ $IS_MASTER == "" && $IS_TAG == "" ]]
then
  echo "WOH! Bad release state for git repo!"
  echo "Make sure you've checked out a git-tag or latest-master before releasing."
  exit 1
fi

# make sure we know what we're doing
WHICH_TEXT="[master] and [$(echo $IS_TAG | sed -e 's/^refs\/tags\///')]"
if [[ $IS_MASTER == "" ]]; then WHICH_TEXT="[$(echo $IS_TAG | sed -e 's/^refs\/tags\///')]"; fi
if [[ $IS_TAG == "" ]]; then WHICH_TEXT="[master]"; fi
read -p "Really release plugin from $WHICH_TEXT? " -n 1 -r
echo ""
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
  echo "(no changes made)"
  echo ""
  exit 0
fi

# init and update svn repo
if [[ ! -d $SVN_PATH ]]
then
  echo " - checking out svn repo"
  OUT=`mkdir -p $SVN_PATH && svn checkout $SVN_REPO $SVN_PATH`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
else
  echo " - updating svn repo"
  OUT=`cd $SVN_PATH && svn update`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
fi

# (2) build zip
echo " - zipping up release/wp-release.zip"
OUT=`rm -f release/wp-release.zip`
OUT=`zip -r release/wp-release.zip . --include ${WHITELIST[@]} --exclude ${BLACKLIST[@]} -q`
if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

# (3) optionally write to /svn/trunk
if [[ $IS_MASTER != "" ]]
then
  TRUNK_PATH=$SVN_PATH/trunk

  # overwrite with unzip
  echo " - writing to $TRUNK_PATH"
  OUT=`rm -rf $TRUNK_PATH && unzip -o release/wp-release.zip -d $TRUNK_PATH`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # stage all changes (adds and removes)
  OUT=`cd $TRUNK_PATH && svn st | grep '^\?' | awk '{print \$2}' | xargs svn add` # add all
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  OUT=`cd $TRUNK_PATH && svn st | grep '^\!' | awk '{print \$2}' | xargs svn rm` # remove all
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # make sure something has changed besides the autoloader hashes
  CHANGES=`cd $TRUNK_PATH && svn st | grep -v 'autoload\(_real\)\?\.php'`
  if [[ $CHANGES == "" ]]
  then
    echo "   no changes to commit for trunk"
    OUT=`cd $TRUNK_PATH && svn revert --recursive .`
    if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  else
    echo " - committing $TRUNK_PATH (slow) ..."
    OUT=`cd $TRUNK_PATH && svn commit -m "update trunk to git $CURRENT"`
    if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  fi
fi

# (4) optionally write to /svn/tags/0.0.0
if [[ $IS_TAG != "" ]]
then
  WP_TAG=`echo $IS_TAG | sed -e 's/^refs\/tags\///' -e 's/^v//'`
  TAG_PATH=$SVN_PATH/tags/$WP_TAG

  # overwrite with unzip
  echo " - writing to $TAG_PATH"
  OUT=`rm -rf $TAG_PATH && unzip -o release/wp-release.zip -d $TAG_PATH`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # set the version
  echo " - updating plugin.php version to $WP_TAG"
  OUT=`sed "s/\* Version:.*$/* Version: $WP_TAG/" $TAG_PATH/plugin.php > plugin.php.new && mv plugin.php.new $TAG_PATH/plugin.php`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # stage all changes (adds and removes)
  OUT=`cd $SVN_PATH/tags && svn st | grep '^\?' | awk '{print \$2}' | xargs svn add` # add all
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  OUT=`cd $SVN_PATH/tags && svn st | grep '^\!' | awk '{print \$2}' | xargs svn rm` # remove all
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # make sure something has changed besides the autoloader hashes
  CHANGES=`cd $SVN_PATH/tags && svn st | grep -v 'autoload\(_real\)\?\.php'`
  if [[ $CHANGES == "" ]]
  then
    echo "   no changes to commit for $TAG_PATH"
    OUT=`cd $SVN_PATH/tags && svn revert --recursive .`
    if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  else
    echo " - committing $TAG_PATH (slow) ..."
    OUT=`cd $SVN_PATH/tags && svn commit -m "update $WP_TAG to git $CURRENT"`
    if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  fi
fi

# success
echo "and we're done!"
