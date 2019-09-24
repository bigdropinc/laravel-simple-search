#Laravel Simple Search

##Requirements

Make sure all dependencies have been installed before moving on:

* [PHP](http://php.net/manual/en/install.php) >= 7.0

Pull the package via Composer:
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require bigdropinc/laravel-simple-search "1.0.0"
```

or add

```
"bigdropinc/laravel-simple-search": "1.0.0"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by:

```php
UserSearch::apply(User:class, request()->all());
```
Filters
=======

All attributes that should be used described in property `fillable`
```php
protected $fillable = [
   'id',
   'first_name',
   'last_name',
];
```

By default for each attribute applied condition `=`

Example:
```php
protected $fillable = [
   'id',
   'first_name',
   'last_name',
];
```
Equivalent
```php

User::where('first_name', $firstNameValue)
   ->where('last_name', $lastNameValue)
   ->where('id', $userIdValue)
```

Сustom filters
-------------
```php
pulic function id($value) {
   $this->query->where('id', '>', $value);
}
```

Cast
-----------
Used default Laravel attribute casting. By default use `string` cast

```php
protected $cast = [
  'id' => 'integer',	
];
```

Sort
---------
Default sort
```php
protected $defaultSort = 'first_name';
```
Ascending order by first_name: `sort=first_name`

Descending order by first_name: `sort=-first_name` | hyphen (`-`) in the start
