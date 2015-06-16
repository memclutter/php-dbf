# php-dbf
Some php classes for easy work with dBase databases
# usage
First, require dBase class
```php
require 'dBase.php';
```
Next, create or open dbf file.
```php
$filename = 'example.dbf';
$mode = dBase::MODE_READ_WRITE;

$dBase = dBase::open($filename, $mode);
//$dBase = dBase::create($filename, $mode);
```
For read rows use Iterator interface
```php
foreach ($dBase as $record) {
    print_r($record);
}
```
For write record use array index operation
```php
// append new record
$dBase[] = [
    // column values
];

// modify exists record by index
$dBase[32] = [
    // new column values
];
```
For delete use unset operation
```php
// delete record by index
unset($dBase[0]);
```
Good luck!