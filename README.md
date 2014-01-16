git-check-inline
================

Git pre-commit hook for check inline code in commited php files

Installation instructions

1. Copy folder ./vendors to your git repository;
2. Copy pre-commit file to your .git/hooks folder or add this code in it:

```bash
./vendors/check_inline.sh
RET=$?
if [ $RET -ne 0 ]; then
  exit $RET
fi
```
