# Modified CSV Importer

A small PHP library for building CSV importers in modified Shop modules.

`robinthehood/modified-csv-importer` gives you a reusable base class (`CsvImporter`) that handles the repetitive import flow:

- Open CSV file
- Read line by line
- Convert source encoding to UTF-8
- Call your row handler (`processRow`) for each line
- Write progress data to a task log file

The module is designed to be extended by your own importer classes (for example product, category, shipping cost, or tag importers).

## Requirements

- modified Shop runtime (because it uses modified environment and ORM)
- `robinthehood/modified-orm`
- PHP environment that can access files under shop `DOCUMENT_ROOT`

## Installation

Add dependency in your module `moduleinfo.json`:

```json
{
  "require": {
    "robinthehood/modified-csv-importer": "^1.2.0"
  }
}
```

Namespace:

```php
use RobinTheHood\ModifiedCsvImporter\Classes\CsvImporter;
```

## Quick Start

Create a class that extends `CsvImporter` and implement `processRow`:

```php
<?php

declare(strict_types=1);

namespace Vendor\MyImport\Classes;

use RobinTheHood\ModifiedCsvImporter\Classes\CsvImporter;

class CsvMyEntityImporter extends CsvImporter
{
    private int $insertCount = 0;
    private int $updateCount = 0;
    private int $skipCount = 0;

    public function processRow($row, $lineNumber)
    {
        // 1) Validate/parse CSV row
        // 2) Create/update your entities

        // Update progress info for frontend polling
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

Run the import:

```php
$importer = new CsvMyEntityImporter();
$importer->setDelimiter(';');
$importer->setCsvEncoding('UTF-8');
$importer->import($filePath, 1); // start at line 1 (usually skip header)
```

## API Overview

### Core workflow

- `setDelimiter($delimiter)`
- `setCsvEncoding($csvEncoding)`
- `import($filePath, $start, $end = 0)`
- `preProcess()`
- `getTask()`

### CSV behavior

- Default delimiter: `;`
- Default input encoding: `UTF-8`
- Rows are read with `fgetcsv`
- Every value in a row is converted to UTF-8 via `iconv($inputEncoding, 'UTF-8', $value)`
- `import(..., $start, $end)` can process a subset of the file
- If `$end <= 0`, importer reads until file end

### Progress logging

`Task` writes JSON to:

- `${DOCUMENT_ROOT}/task_log_001.txt` (default task id is `001`)

Typical payload while running:

```json
{
  "start": 1,
  "end": 1200,
  "current": 153,
  "inserts": 40,
  "updates": 70,
  "skips": 43,
  "status": "active"
}
```

When finished, status becomes `done`.

## Advanced Helpers Included in CsvImporter

`CsvImporter` also contains helper methods for common modified ORM import tasks, including:

- category create/find and product-category assignment
- shipping status create/find and assignment to product
- manufacturer create/find and assignment to product
- product tag option/value create/find and assignment
- product VPE create/find
- cleanup helpers for product-category links and tags

These helpers are optional. You can use them in your subclass when they match your import use case.
