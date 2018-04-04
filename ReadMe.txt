Introduction

This script parses log output from the NBA to csv documents.
The parser currently supports NBA import logs from Brahms, CRS
and the Catalogue of Life; other logs are ignored.

The parser will try to allocate 1GB of memory. When less memory 
is available, the parser may crash.



How to use

The parser has to be initialized with 
1. an input path (directory of log files to parse) and
2. an output path (directory to which a subdirectory with the results is written)

Copy config.php.tpl, add the correct paths and save the file as config.php.

Obviously inputhas to be readable and output has to be writable.
It is assumed that the name of 1 consists of just the import date.
See index.php for an example.



Results

The parser creates four files:
1. summary.csv
2. warnings.csv
3. errors.csv
4. normalization.csv

1. summary.csv contains aggregated data from all files related to a specific type 
of import. E.g. in case of Brahms, the numbers are the totals of multiple import files.

2. and 3. contain a full list of individual warnings and errors respectively.
These are related to specific unit ids (registration numbers).

4. normalization.csv contains the values that do not match fixed lists in de NBA.