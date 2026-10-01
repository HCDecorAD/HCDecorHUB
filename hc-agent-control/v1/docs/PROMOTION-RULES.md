# v1-next promotion rules

The frozen Early Use baseline is not overwritten merely because v1-next compiles.

Before promotion:
1. Run 79_PROMOTE_NEXT_TO_EARLY_USE_PRECHECK.bat.
2. Run the build on HOCUONG with managed Edge.
3. Review the dashboard visually.
4. Keep Real Send OFF.
5. Preserve the upgrade baseline backup.
6. Only then merge/copy the accepted v1-next code into the Early Use baseline.

A failed precheck leaves the current Early Use baseline unchanged.
