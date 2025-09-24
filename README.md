# SemanticReports

This extension provides a command-line tool for generating reports based on semantic queries. It supports CSV output
format and allows users to specify the output file for the generated report.

## Usage

```bash
# Output report to stdout
php maintenance/run.php SemanticReports:GenerateReport -q '[[Category:Books]] [[Property:Test]] |?Property' -f csv
# Save report to report.csv
php maintenance/run.php SemanticReports:GenerateReport -q '[[Category:Books]] [[Property:Test]] |?Property' -f csv -o report.csv
```

# Limitations

- Only supports CSV output format
- Limited error handling and reporting
