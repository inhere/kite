# EasyTemplate

## Using the filters

You can use the filters in any of your blade templates.

#### Regular usage:

```php
{{ 'john' | ucfirst }} // John
```

#### Chained usage:

```php
{{ 'john' | ucfirst | substr:0,1 }} // J
{{ '1999-12-31' | date:'Y/m/d' }} // 1999/12/31
```

#### Passing non-static values:

```php
{{ $name | ucfirst | substr:0,1 }}
{{ $user['name'] | ucfirst | substr:0,1 }}
{{ $currentUser->name | ucfirst | substr:0,1 }}
{{ getName() | ucfirst | substr:0,1 }}
```

#### Passing variables as filter parameters:

```php
$currency = 'HUF'
{{ '12.75' | currency:$currency }} // HUF 12.75
```

#### Built-in Laravel functionality:

```php
{{ 'This is a title' | slug }} // this-is-a-title
{{ 'This is a title' | title }} // This Is A Title
{{ 'foo_bar' | studly }} // FooBar
```