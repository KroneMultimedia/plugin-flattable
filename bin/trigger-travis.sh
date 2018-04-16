#!/bin/bash
body="{\"request\": {\"branch\":\"${1}\"}}"

curl -s -X POST \
   -H "Content-Type: application/json" \
   -H "Accept: application/json" \
   -H "Travis-API-Version: 3" \
   -H "Authorization: token $TRAVIS_TOKEN" \
   -d "$body" \
   https://api.travis-ci.org/repo/KroneMultimedia%2Fplugin-flattable/requests
