#!/bin/bash

COVERPARM="  --coverage-php build-reports/coverreport.php --coverage-html  build-reports " $(dirname $0)/rununitswithoutcoverage.sh $*


