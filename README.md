# XML Converter

## Installation
```php
composer install
composer dump-autoload
```

## Command ausführen
Das Command erwartet zwei Parameter. 
Das targetFormat muss "csv","json" oder "xlsx" entsprechen.

```php
php App.php convertFile <pathToFile> <targetFormat>
```
