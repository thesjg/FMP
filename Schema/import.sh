#!/bin/csh

dropdb -U flixn fmp_dev
createdb -U flixn fmp_dev
createlang -U pgsql plpgsql fmp_dev

for i in `find *-* | sort`; do psql -U flixn -h db.flixn.com -f $i fmp_dev; done