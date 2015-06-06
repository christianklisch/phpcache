# PHPCache v. 0.1.2

PHPCache is a simple file based object cache, caching big or complex calculated data in filesystem. 

Use it for:
* Increasing speed of web application
* Caching result objects from complex calculations
* Caching results from complex database queries
* Caching function calls
* Cache primitive data types, own objects, function results 

Features:
* Define caching time in seconds, cache directory
* Can be used with every variable / data (except. templates)
* Load data from cache or determine new data
* Clear caching directory
* Garbage collector


## Installation

You can either install the script manually using the `require` method:

```php
require 'PHPCache.class.php';
```

or use the composer:
```
    "require": {
        "christianklisch/phpcache": "0.1.2"
    }
```

This is currently the first release of PHPCache, so in this example you'll be able to update your script with any updates made to PHPCache.

## Deploying

Include the script in your project either with Composer or via the manual `require` method and create a new instance of the class, using the appropriate parameters if needed:

```php
$cache = new PHPCache();
```

Possible parameters include:

```php
$cache = new PHPCache(
	array('debug' => true, 'cacheDir' => 'cache', 'cacheTime' => 10)
);
/**
 * @param  array $settings Associative array of caching settings
 */
```

### Primitive data type
Then cache an primitive data type with:

```php
$myString = $cache->cacheVal('String to cache','cachingID');
```

This will cache the string of the first parameter into a file named by second parameter. Use the second parameter setting key for primitive data types.


### Object data type

Cache an object data type with:

```php
$myObj = new FooBar();
$obj = $cache->cacheVal($myObj, $myObj->getId());
```

This will cache the object in the first parameter into a file named by second parameter. Use the second parameter setting key as filename for object cache.

### Object data type with automatic ID-getter

Define for each object type the id-key/primary key remove second id-parameter:

```php
$myObj = new FooBar();

/**
 * Setting get-functions for primary keys of classes
 * @param  array $primkeys Associative array of class primary-key-function
 */
$cache->setPrimaryKeys(array(
    'FooBar' => 'getId'
));

$obj = $cache->cacheVal($myObj);
```

Now the objects id is automatically determined by PHPCache logic. Caching configuration is 

### Check for cached data

Check with the given key if data is cached. Can be used to call a new cacheVal().

```php
/**
 * check, if key or object in first parameter is cached. Returns true, if cached
 * @return bool
 */

if($cache->isCached($key))
    $obj = $cache->cacheVal($myObj, $key);  
else
    $obj = $logic->calcVal($key);  
```

### Cache whole function calls

Like the value-caching you can cache results of complex logical function calls. The defined function will only be called in case of absence cached function results.

```php
/**
 * cache function result and return it
 * @return object     
 */    

$key = 1110;

$result = $cache->cacheFun(
    function () use ($key, $logic) {
        return $logic->getComplexResult();
    }
);

echo "result: ".$result; 
```

Please don't use function parametes. Submit your variables via the use()-keyword. It is important using one parameter named 'key' for caching. The name and count of other parameters is not important. Write the call of your complex logic inside the anonymous function. This code will be called, if your key isn't found in cache.

### Delete old cached data

Delete old cached data in caching directory with:
 
```php
/**
 * delete old cached files 
 */    
$cache->gc();
```
Garbage Collector is not called automatically.


### Clear caching directory

Delete all cached data in caching directory with:
 
```php
/**
 * delete all cached files 
 */    
$cache->clearCache();
```

## Contributors

* Christian Klisch http://www.christian-klisch.de


## Copyright and license

Copyright 2014 Christian Klisch, released under [the Apache](LICENSE) license.
