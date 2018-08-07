#!/usr/bin/env bash

set -x

# Install the dependencies (as defined in the composer.lock) first so we can package them up
composer install --no-dev --optimize-autoloader --no-interaction




version=$1
KRN_REPO_SLUG=$2

apt-get update -y
apt-get install -y rsync

sed -i "s/v9.9.9/$version/g" ${KRN_REPO_SLUG}.php
sed -i "s/%TRAVIS_TAG%/$version/g" ${KRN_REPO_SLUG}.php

# Cleanup the old dir if it is there
rm -rf /tmp/tmp_folder-svn

# Checkout the svn repo
svn co http://plugins.svn.wordpress.org/$KRN_REPO_SLUG/ /tmp/tmp_folder-svn

echo "Copying files to trunk"
rsync -Rrd --delete  ./ /tmp/tmp_folder-svn/trunk/

cd /tmp/tmp_folder-svn/

rm -vfr trunk/.git
svn status | grep '^!' | awk '{print $2}' | xargs svn delete
svn add --force * --auto-props --parents --depth infinity -q

svn status

svn commit --username $WP_ORG_USERNAME --password $WP_ORG_PASSWORD  --no-auth-cache -m "Syncing v${version}"

echo "Creating release tag"

mkdir /tmp/tmp_folder-svn/tags/${version}
svn add /tmp/tmp_folder-svn/tags/${version}
svn commit --username $WP_ORG_USERNAME --password $WP_ORG_PASSWORD  -m "Creating tag for v${version}"

echo "Copying versioned files to v${version} tag"

svn cp --parents trunk/* tags/${version}

svn commit --username $WP_ORG_USERNAME --password $WP_ORG_PASSWORD  -m "Tagging v${version}"

