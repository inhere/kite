# PHPUnit

There are some examples for use `phpunit` tool.

## usage

quick:

```bash
phpunit
```

with params:

```bash
phpunit --filter MyClass
```

## Test case class

examples:

```php
<?php declare(strict_types=1);

namespace Inhere\CodeTest;

use PHPUnit\Framework\TestCase;

class MyClassTest extends TestCase
{
    public function testSome(): void
    {
        // do something ...
    }
}
```