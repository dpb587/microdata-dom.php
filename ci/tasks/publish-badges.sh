#!/bin/sh -eu

init-refs @repo/.git/HEAD $repo_branch

log=artifacts/logs/coverage/clover.xml \
  clover-coverage

log=artifacts/logs/coverage/clover.xml \
  clover-lines

log=artifacts/logs/junit/results.xml \
  junit-results

publish-s3
