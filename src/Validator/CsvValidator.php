<?php

namespace Oshomo\CsvUtils\Validator;


use InvalidArgumentException;
use Oshomo\CsvUtils\Exceptions\InvalidRuleDeclarationException;
use ReflectionMethod;

class CsvValidator extends Rules
{
    use ValidatorMessageTrait;

    /**
     * Csv File Path;
     *
     * @var string
     */
    protected $file_path;

    /**
     * Csv delimiter;
     *
     * @var string
     */
    protected $delimiter;

    /**
     * CSV row count;
     *
     * @var string
     */
    protected $columnCount = 0;

    /**
     * Available validation rules.
     *
     * @var array
     */
    protected $availableRules = [
        'min', 'max', 'url', 'ascii'
    ];

    /**
     * The initial rules provided.
     *
     * @var array
     */
    protected $initialRules;

    /**
     * The CSV header
     *
     * @var array
     */
    public $headers = [];

    /**
     * CSV data that passed validation
     *
     * @var array
     */
    public $validData = [];

    /**
     * CSV data that failed validation
     *
     * @var array
     */
    public $invalidData = [];

    /**
     * Create a new Validator instance.
     * @param $file_path
     * @param $delimiter
     * @param array $rules
     * @internal param array $data
     */
    public function __construct($file_path = "", array $rules = [], $delimiter = ",")
    {
        if (!empty($file_path)) {
            $this->file_path = $file_path;
        }
        if (!empty($rules)) {
            $this->initialRules = $rules;
        }
        $this->delimiter = $delimiter;
    }

    /**
     * Returns method name for supplied rule
     * @param $rule
     * @return string
     */
    private function getRuleValidator($rule)
    {
        return "validate".ucfirst($rule);
    }

    /**
     * Validates that rules supplied are supported
     * Also validates that parameters are sent for rules that require parameters
     */
    private function validateInitialRules()
    {
        $invalidRulesError = [];
        if(!empty($this->initialRules)) {
            foreach ($this->initialRules as $key => $rules) {
                foreach ($rules as $rule => $parameter) {
                    $validationFunction = $this->getRuleValidator($rule);
                    // Checks if the rule supplied has a function for validating it
                    if (!method_exists($this, $validationFunction) &&
                        !is_callable(array($this, $validationFunction))
                    ) {
                        $invalidRulesError[$key][] = "Invalid rule {$rule} supplied.";
                    } else {
                        $func = new ReflectionMethod($this, $validationFunction);
                        $numberOfParameters = $func->getNumberOfParameters();

                        if ($numberOfParameters > 1 &&
                            $parameter === "") {
                            $invalidRulesError[$key][] = "Parameter for rule {$rule} cannot be empty.";
                        }
                    }

                }
            }

            if (!empty($invalidRulesError)) {
                throw new InvalidRuleDeclarationException($invalidRulesError);
            }
        }
    }

    /**
     * Read and validate CSV
     * @return $this
     */
    public function validate()
    {
        if ($this->validateFileExistAndReadable($this->file_path)) {
            $this->validateInitialRules();
            if (($handle = fopen($this->file_path, 'r')) !== FALSE) {
                while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
                    $this->columnCount ++;

                    if ($this->columnCount == 1) {
                        $this->setHeaders($row);
                        continue;
                    }

                    $result = [];
                    //Loop through the CSV rows, validate against rules and assign correct values to object
                    foreach ($row as $key => $value) {

                        $attribute = $this->headers[$key];
                        $validateAble = $this->isValidateAble($attribute);

                        if ($validateAble) {
                            $rule = $this->initialRules[$attribute];
                            $validateAttr = $this->validateAttribute($value, $rule, $attribute);
                            $result[$this->headers[$key]] = $value;
                            if (!empty($validateAttr)) {
                                $result["errors"] = $validateAttr;
                            }
                        } else {
                            $result[$this->headers[$key]] = $value;
                        }
                    }

                    if (!empty($result["errors"])) {
                        $this->invalidData[] = $result;
                    }else {
                        $this->validData[] = $result;
                    }
                }
            }
        }else {
            throw new InvalidArgumentException("The file path supplied is either not readable or doesn't exist.");
        }

        return $this;

    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    private function setHeaders($headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = strtolower($value);
        }
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

    /**
     * @param string $file_path
     * @return $this
     */
    public function setFilePath($file_path)
    {
        $this->file_path = $file_path;

        return $this;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->initialRules;
    }

    /**
     * @param array $initialRules
     * @return $this
     */
    public function setRules(array $initialRules)
    {
        $this->initialRules = $initialRules;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidData()
    {
        return $this->validData;
    }

    /**
     * @return array
     */
    public function getInvalidData()
    {
        return $this->invalidData;
    }

    /**
     * @return string
     */
    public function getAllData()
    {
        return array_merge($this->validData, $this->invalidData);
    }

    /**
     * @param $attribute
     * @return bool
     */
    private function isValidateAble($attribute)
    {
        return (
            isset($this->initialRules[$attribute]) &&
            count(array_intersect($this->availableRules, array_keys($this->initialRules[$attribute]))) == count(array_keys($this->initialRules[$attribute]))
        );
    }

    /**
     * @param $file_path
     * @return bool
     */
    private function validateFileExistAndReadable($file_path)
    {
        return file_exists($file_path) && is_readable($file_path);
    }

    /**
     * @param $value
     * @param $rules
     * @return array
     */
    private function validateAttribute($value, $rules, $attribute)
    {
        $messages = [];

        foreach ($rules as $rule => $parameters) {
            $method = $this->getRuleValidator($rule);
            if(!$this->$method($value, $parameters)) {
                $messages[] = $this->getMessage($rule, $value, $attribute);
            }
        }

        return $messages;

    }

}