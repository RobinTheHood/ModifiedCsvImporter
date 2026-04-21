# Usage Guide (How-To)

This guide explains how to build and run imports with `robinthehood/modified-csv-importer`.

## 1. Create Your Importer Class

Create a class that extends:

- `RobinTheHood\ModifiedCsvImporter\Classes\CsvImporter`

Implement:

- `processRow($row, $lineNumber)`

Optional:

- `preProcess()` (called once before CSV loop starts)

Example:

```php
<?php

declare(strict_types=1);

namespace Vendor\Example\Classes;

use RobinTheHood\ModifiedCsvImporter\Classes\CsvImporter;

class CsvExampleImporter extends CsvImporter
{
    private int $insertCount = 0;
    private int $updateCount = 0;
    private int $skipCount = 0;

    public function preProcess()
    {
        // Optional one-time setup before rows are processed.
    }

    public function processRow($row, $lineNumber)
    {
        // Map CSV columns, validate, then insert/update your data.
        // Example counters:
        // $this->insertCount++;
        // $this->updateCount++;
        // $this->skipCount++;

        $task = $this->getTask();
        $task->setLogValues([
            'start' => $this->start,
            'end' => $this->end,
            'current' => $lineNumber,
            'inserts' => $this->insertCount,
            'updates' => $this->updateCount,
            'skips' => $this->skipCount,
            'status' => 'active'
        ]);
    }
}
```

## 2. Configure Delimiter and Encoding

Before calling `import()`, set CSV format details:

```php
$importer = new CsvExampleImporter();
$importer->setDelimiter(';');
$importer->setCsvEncoding('UTF-8');
```

Common real-world values used in production modules:

- Delimiter: `*`, `;`, `,`, or tab (`"\t"`)
- Encoding: `Windows-1252` or `UTF-8`

## 3. Run Import

```php
$filePath = DIR_FS_DOCUMENT_ROOT . '/import/my-file.csv';

// Start at line 1 to skip header row
$importer->import($filePath, 1);

// Or process a range (for testing/chunks)
$importer->import($filePath, 1, 500);
```

Behavior:

- Reads file with `fgetcsv`
- Converts each field to UTF-8
- Calls `processRow` for each selected line
- Writes task status to a log file

## 4. Show Progress in Admin UI

`Task` writes JSON to:

- `${DOCUMENT_ROOT}/task_log_001.txt`

You can poll and parse this JSON in JavaScript and build a progress bar.

A typical running payload:

```json
{
  "start": 1,
  "end": 1000,
  "current": 250,
  "inserts": 90,
  "updates": 120,
  "skips": 40,
  "status": "active"
}
```

When import finishes, importer writes:

```json
{
  "start": 1,
  "end": 1000,
  "current": 1000,
  "status": "done"
}
```

## 5. Use Built-in Helper Methods (Optional)

`CsvImporter` includes helper methods that can save a lot of boilerplate if you work with modified ORM entities:

- Category helpers
: create/find category paths and assign product to category
- Shipping status helpers
: create/find shipping status and assign to product
- Manufacturer helpers
: create/find manufacturer and assign to product
- Product tag helpers
: create/find option/value and assign tags to product
- VPE helpers
: create/find product VPE
- Cleanup helpers
: remove product-category links or tags from product

If your importer does not need these, you can ignore them and implement your own logic.
