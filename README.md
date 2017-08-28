<a href="https://travis-ci.org/hoshomoh/CSVUtils">
    <img src="https://travis-ci.org/hoshomoh/CSVUtils.svg" alt="Build Status">
</a>
<a href="https://codecov.io/gh/hoshomoh/CSVUtils">
    <img src="https://codecov.io/gh/hoshomoh/CSVUtils/branch/master/graph/badge.svg" alt="Codecov" />
</a>

# CSVUtils by Oforomeh Oshomo

### How to run

From the package root, run assignment using php built in server `php -S localhost:8000`, this would start the server at `localhost:8000`. Visit the URL from your browser and you should see the generated files at the root of the package.
The `sample.csv` is at the root of the package. Generated files would also be saved there.

To run test make sure you have `PHPUNIT` installed on the test machine. Then run `phpunit` also from the root of the Package.

### Implementation

The `CsvConverter` and `CsvValidator` were written in isolation of each other so, that they can be used seperately if need arise. The `CsvConverter` expects a valid file path and an array of validation rule(s) and in turn an array of all, valid and invalid data can be accessed. 

The `CsvValidator` on the other hand expects a valid folder path, array of data to be written into either `JSON` or `XML`, and in turn write the file in the folder specified.

### Documentation

##### CsvValidator
Currently supported validation rules:

`min (expects a value)`:
```
Validates that a cell value is not less than the specified value
```
`max (expects a value)`:    
```
Validates that a cell value is not greater than the specified value
```
`ascii`:  
```
Validates that a cell value does not contain a non-ascii character
```
`url`:    
```
Validates that a cell is a valid URL. By valid URL we mean 

(#protocol) 
(#basic auth) 
(#a domain name or #an IP address or #an IPv6 address) 
(#a port(optional)) then 
(#a /, nothing, a / with something, a query or a fragment)

```

Initializing a `CsvValidator`. Set a valid csv file path and pass in your validation rules.
```php
$validator = new CsvValidator("some/valid/file_path", [
    "name" => ["ascii" => ""],
    "uri"   => ["url" => ""],
    "stars" => ["min" => 0, "max" => 5]
]);
```

OR

```php
$validator = new CsvValidator();
$validator->setFilePath("some/valid/file_path");
$validator->setRules([
    "uri"   => ["url" => ""],
    "stars" => ["min" => 0, "max" => 5]
]);
```

Validating the CSV

```php
$validator->validate();
```

Other available methods (To be called after `validate()` else you would get an empty array `[]`)

`getHeaders()` e.g. `$validator->vaildate()->getHeaders();`
Returns CSV header as an array

`getAllData()` e.g. `$validator->vaildate()->getAllData();`
Returns All CSV data both those that passed validation and those that failed validation as an array

`getValidData()` e.g. `$validator->vaildate()->getValidData();`
Returns All CSV data that passed validation as an array

`getInvalidData()` e.g. `$validator->vaildate()->getInvalidData();`
Returns All CSV data that failed validation as an array with `error` bag containing which and why each column failed.

##### CsvConverter
Currently supported converters:

`JSON` and `XML`

Initializing a `CsvConverter`. Set a valid folder path to save generated files and also pass data to be converted as an array. You would mostly pass data from `$validator->vaildate()->getValidData();` or `$validator->vaildate()->getAllData();` as the case maybe.
```php
$converter = new CsvConverter([], "some/valid/folder");
```

OR

```php
$converter = new CsvConverter();
$converter->setPath("some/valid/folder");
$converter->setData([]);
```

Converting to `JSON`
```php
$converter->toJSon("filename.json");
```

Converting to `XML`
```php
$converter->toXml("filename.xml");
```

### Todo's

 - Support for more validation rules `string`, `number`, `date`, `required`, `boolean`, `email`, `phone_number`, `ip_address`
 - Make CsvConverter extensible, so that user can pass there own validation rule
 - Add	options	to	sort/group/filter the data before writing the data.
