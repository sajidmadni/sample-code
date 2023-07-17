#!/bin/bash

set -x

[ -z "$WORKSPACE" ] && WORKSPACE=$1
[ -z "$WORKSPACE" ] && WORKSPACE=$(pwd)

CONSOLE=$WORKSPACE/bin/console
APP=$WORKSPACE/app/phpunit.xml

# MEMLIMIT=" -d memory_limit=2048M "
MEMLIMIT=" -d memory_limit=4096M "

PHP=$(which php)
[ -z "$PHP" ] && PHP="/usr/bin/php"

PHPUNIT="$PHP bin/phpunit"

$PHP -v
$PHPUNIT --version

./bin/setMode.sh test

#$PHP $CONSOLE doctrine:schema:update --force
rm -rf web/uploads/hospitals/*.jpeg
rm -f out.txt
for i in tmp/q tmp/wfq tmp/xwip tmp/xfwip tmp/wfwip tmp/wfxwip tmp/wfxq
do
    mkdir -p $i 
done 



git status

git log | head -40

rm -fr app.var/cache 

$PHPUNIT $MEMLIMIT -c app --verbose --debug --process-isolation  $COVERPARM  2>&1 | tee $WORKSPACE/phpunit.log
RESLT=$(grep Tests: $WORKSPACE/phpunit.log)

echo $WORKSPACE $(date) $RESLT >> $HOME/phpunit.history

cat > $WORKSPACE/results.log <<XXX

RESULTS: $RESLT 
DETAILS $JOB_NAME $BUILD_DISPLAY_NAME 

XXX
git log | head -20 >> $WORKSPACE/results.log

## bin/phpunit -d memory_limit=2048M -c app --process-isolation --debug --verbose --coverage-html build-reports 
## 
## #Entire Set of tests -- please do not delete
## $PHPUNIT -c $APP   --testsuite=engagement_related_tests --testdox --log-junit reports1.xml
## 
## $PHPUNIT -c $APP   --testsuite=physician_related_tests --testdox --log-junit reports2.xml
## 
## $PHPUNIT -c $APP   --testsuite=hospital_related_tests --testdox --log-junit reports3.xml
## 
## $PHPUNIT -c $APP   --testsuite=oncall_related_tests --testdox --log-junit reports4.xml
## 
## $PHPUNIT -c $APP   --testsuite=messaging_related_tests --testdox --log-junit reports5.xml
## 
## $PHPUNIT -c $APP   --testsuite=api_related_tests --testdox --log-junit reports6.xml
## 
## $PHPUNIT -c $APP   --testsuite=consult_related_tests --testdox --log-junit reports7.xml
## 
## $PHPUNIT -c $APP   --testsuite=patient_related_tests --testdox --log-junit reports8.xml
## 
## $PHPUNIT -c $APP   --testsuite=sync_related_tests --testdox --log-junit reports9.xml
## 
## 

#perform cleanup
rm -rf web/uploads/hospitals/*.jpeg

#Clear Cache to free space
$PHP $CONSOLE cache:clear --no-warmup -e dev
$PHP $CONSOLE cache:clear --no-warmup -e test

echo ' ' > $WORKSPACE/app.var/test/logs/test.log
