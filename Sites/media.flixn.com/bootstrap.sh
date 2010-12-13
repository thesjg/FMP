#!/bin/sh
targets="fmp incoming"
chars="a b c d e f 0 1 2 3 4 5 6 7 8 9"
for t in $targets; do
    for i in $chars; do
        mkdir $t/$i;
        for j in $chars; do
            mkdir $t/$i/$j;
            for k in $chars; do
                mkdir $t/$i/$j/$k;
                for l in $chars; do
                    mkdir $t/$i/$j/$k/$l;
                done;
            done;
        done;
    done;
done