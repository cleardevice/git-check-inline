#!/bin/sh

# list of files changed during a commit
changed_files=$(git diff --cached --name-only --diff-filter=ACM)

# check inline
php ./vendors/inline_check.php $changed_files
if [ $? -ne 0 ]; then
  echo 'Check code style in files. Commit aborted.'
  exit 1
fi

exit 0
