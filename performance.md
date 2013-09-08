
Performance
===========

as tested on MySQL table for selecting locations by distance from a center point:

without indexes:
- *n-vector* is about 10% faster than *lat/lon*, due to less CPU intensive algorythm

with indexes:
- *n-vector* is about the same as *lat/lon* on small data (~30k rows), but gets worse on medium data (~200k rows) or large data
- on large data (~10M rows) it can be slower by order of magnitude (it may be due to 3-column index instead of 2-column, but my testing environment is crappy)

lon/lat in radians:
- when storing *lon/lat in radians* instead of *degrees*, selects are about 2% faster
- this is not enough to justify worse human readability of data


problems:
---------
- lon/lat does not work arround poles and 180th meridian
- n-vector has false negatives *(bug)* and performance issues and needs further testing


**it is recommended to use lon/lat in degrees for now**
