<a href="https://travis-ci.org/hoshomoh/CSVUtils">
    <img src="https://travis-ci.org/hoshomoh/CSVUtils.svg" alt="Build Status">
</a>
<a href="https://codecov.io/gh/hoshomoh/CSVUtils">
    <img src="https://codecov.io/gh/hoshomoh/CSVUtils/branch/master/graph/badge.svg" alt="Codecov" />
</a>

# CSVUtils

*Make sure you use a tagged version when requiring this package. The current version is [v2.0.0](https://packagist.org/packages/oshomo/csv-utils#v2.0.0)*

### How to run

I have added a sample `index.php` file for a quick test of how to use the package. To run the sample; from the package root, run `composer install` then using php built in server run `php -S localhost:8000`, this would start the server at `localhost:8000`. Visit the URL from your browser and you should see the generated files in the `sample` folder at the root of the package.

### Implementation

The `Validator` expects a valid file path, the CSV delimiter, an array of validation rule(s) and an optional message(s) array to over-write the default messages of the validator.

### Documentation

##### Initializing a Validator. 

Set a valid csv file path, pass the CSV delimiter and pass in your validation rules.

```php
use Oshomo\CsvUtils\Validator\Validator;

$validator = new Validator("some/valid/file_path", ",", [
    "name" => ["ascii_only"],
    "uri"   => ["url"],
    "stars" => ["between:0,5"]
]);
```

##### Validating the CSV

Now we are ready to validate the CSV. The validator provides a `validate ` method that can be called like so: `$validator->validate();`. The `validate` method returns an array of the invalid rows if validation fails. If the validation passes the `validate` method returns the CSV data as an array

A better implementation:

```php
use Oshomo\CsvUtils\Validator\Validator;

$validator = new Validator("some/valid/file_path", ",", [
    'title' => ["ascii_only", "url"]
]);

if ($validator->fails()) {
    // Do something when validation fails
    $errors = $validator->errors()
}
```

##### Error messages

To get the rows with validation errors and there errors. The validator expose `errors` method that can be used like so `$validator->errors()`.

You can also customize the error messages for different validation rules and different attributes by passing a message array to the validator like so:

```php
use Oshomo\CsvUtils\Validator\Validator;

$validator = new Validator("some/valid/file_path", ",", [
    'title' => ["ascii_only", "url"]
], [
    'ascii_only' => 'The :value supplied for :attribute attribute is invalid',
    // This specifies a custom message for a given attribute.
    'hotel_link:url' => 'The :attribute must be a valid link',
]);
```

In this above example, the `:attribute` place-holder will be replaced by the actual name of the field under validation. The `:value` place-holder will also be replaced with value being validated. You may also utilize other place-holders in validation messages. For example the `between` rule exposes two other placeholder `min` and `max`. Find more about this in the available rules section

##### Available rules

`between:min,max`:
```
Validates that a cell value is between a :min and :max. The rule exposes the :min and :max placeholder for inline messages
```
`ascii_only`:  
```
Validates that a cell value does not contain a non-ascii character
```
`url`:    
```
Validates that a cell value is a valid URL. By valid URL we mean 

(#protocol) 
(#basic auth) 
(#a domain name or #an IP address or #an IPv6 address) 
(#a port(optional)) then 
(#a /, nothing, a / with something, a query or a fragment)

```

##### Writing CSV output data

The output of the CSV file can be written into any format. The currently suported format is `xml` and `json`. The validator exposes a `write` method to write the output data into the same folder as the CSV. Find example implementation below:

```php
use Oshomo\CsvUtils\Validator\Validator;
use Oshomo\CsvUtils\Converter\JsonConverter;
use Oshomo\CsvUtils\Converter\XmlConverter;

$validator = new Validator('some/valid/file_path', ',', [
    "stars" => ["between:0,5"],
    "name" => ["ascii_only"],
    "uri"   => ["url"],
]);

if(!$validator->fails()) {
    $validator->write(new JsonConverter());
    $validator->write(new XmlConverter("hotel"));
} else {
    print_r($validator->errors());
}
```

The `JsonConverter` simply writes the output data as JSON. The `XmlConverter` converter writes the data as XML. `XmlConverter` takes an optional parameter for setting the XML records element. If non is supplied it defaults to `item` e.g `$validator->write(new XmlConverter("hotel"));` would write the below:

```
<?xml version="1.0"?>
<data>
  <hotel>
    <name>Beni Gold Hotel and Apartments</name>
    <stars>5</stars>
    <uri>https://hotels.ng/hotel/86784-benigold-hotel-lagos</uri>
  </hotel>
  <hotel>
    <name>Hotel Ibis Lagos Ikeja</name>
    <stars>4</stars>
    <uri>https://hotels.ng/hotel/52497-hotel-ibis-lagos-ikeja-lagos</uri>
  </hotel>
</data>
```

**NOTE**: *Either validation passes or fails, you can always write the CSV output data to the available formats. In cases where validation fails there would be an extra error property in the written data.*

##### Passing custom rule to validator (Using Rule Object)

Passing a custom rule to the validator is easy. Create a CustomRule class the implements `Oshomo\CsvUtils\Contracts\ValidationRuleInterface` interface. And pass that class to the rule array, easy. E.g:

```php
use Oshomo\CsvUtils\Validator\Validator;

$validator = new Validator('some/valid/file_path', ',', [
    "name" => ["ascii_only", new UppercaseRule]
]);
```

The class definition for `UppercaseRule`. Follow the same approach if you want to create your own rule.

```php
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;

class UppercaseRule implements ValidationRuleInterface
{
    /**
     * Determine if the validation rule accepts parameters or not.
     * If it does not accept any parameter return true else return false
     *
     * @return boolean
     */
    public function isImplicit()
    {
        return true;
    }

    /**
     * Get the number of parameters that should be supplied. 
     * If no parameter should be supplied return 0 else 
     * return the number of parameters that should be returned
     *
     * @return integer
     */
    public function parameterCount()
    {
        return 0;
    }

    /**
     * Determines if the validation rule passes. This is where we do the
     * actual validation. If the validation passes return true else false
     *
     * @param  mixed $value
     * @param $parameters
     * @return bool
     */
    public function passes($value, $parameters)
    {
        return strtoupper($value) === $value;
    }

    /**
     * Get the validation error message. Specify the message that should
     * be returned if the validation fails. You can make use of the 
     * :attribute and :value placeholders in the message string
     *
     * @return string
     */
    public function message()
    {
        return "The :attribute value :value must be uppercase.";
    }

    /**
     * Replace error messages parameter with right values. If you want 
     * to allow user pass custom placeholders in the inline message
     * specify and replace them here. If not just return $message
     * i.e return $message. But if you want to allow custom placeholder 
     * return str_replace(
     *      [':custom_a', ':custom_b'], 
     *      [$parameters[0], $parameters[1]], 
     *      $message
     * );
     *
     * @param string $message
     * @param array $parameters
     * @return string
     */
    public function parameterReplacer($message, $parameters)
    {
        return $message;
    }
}

```

##### Passing custom rule to validator (Using Closure)

If you only need the functionality of a custom rule once throughout your application, you may use a Closure instead of a rule object. The Closure receives the attribute's value, and a `$fail` callback that should be called if validation fails:

```php
use Oshomo\CsvUtils\Validator\Validator;

$validator = new Validator("some/valid/file_path", ",", [
    "uri"   => ["url", function($value, $fail) {
        if (strpos($value, "https://") !== 0) {
            return $fail('The URL passed must be https i.e it must start with https://');
        }
    }]
]);
```

##### Writing CSV output data to other formats

Writing the CSV ouput data to other format is also very easy. Create a CustomConverter class the implements `Oshomo\CsvUtils\Contracts\ConverterHandlerInterface` interface. And pass that class to the `write` method of the validator, easy. Below is an sample implementation of a JSON converter

```php
use Oshomo\CsvUtils\Contracts\ConverterHandlerInterface;

class JsonConverter implements ConverterHandlerInterface
{
    const FILE_EXTENSION = "json";

    /**
     * The converted data
     *
     * @var string
     */
    protected $data;

    /**
     * @return string
     */
    public function getExtension()
    {
        return self::FILE_EXTENSION;
    }

    /**
     * @param array $data
     * @return $this|mixed
     */
    public function convert($data)
    {
        $this->data = json_encode($data,
            JSON_PRETTY_PRINT |
            JSON_NUMERIC_CHECK |
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        );

        return $this;
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function write($filename)
    {
        return (file_put_contents($filename, $this->data)) ? true : false;
    }
}

//////////////////////////////////////////////////////
// To use the converter above.
//////////////////////////////////////////////////////

$validator->write(new JsonConverter());

```

### Running Test's

Run `composer test` from the root of the Package.
