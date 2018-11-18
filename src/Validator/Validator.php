<?php

namespace Oshomo\CsvUtils\Validator;

use Oshomo\CsvUtils\Contracts\ConverterHandlerInterface;
use Oshomo\CsvUtils\Contracts\ConverterHandlerInterface as Converter;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface as ValidationRule;
use Oshomo\CsvUtils\Helpers\FormatsMessages;

class Validator
{
    use FormatsMessages;

    const FILE_EXTENSION = '.csv';
    const ERROR_MESSAGE = 'Validation fails.';
    const NO_ERROR_MESSAGE = 'File is valid.';
    const INVALID_FILE_PATH_ERROR = 'Supplied file is not accessible.';
    const SUCCESS_MESSAGE = 'CSV is valid.';

    /**
     * The message bag instance.
     *
     * @var array
     */
    protected $currentRowMessages = [];

    /**
     * The CSV data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Initialisation errors.
     *
     * @var string
     */
    protected $message;

    /**
     * The line number of the row under validation.
     *
     * @var int
     */
    protected $currentRowLineNumber = 0;

    /**
     * The row under validation.
     *
     * @var array
     */
    protected $currentRow;

    /**
     * The invalid rows.
     *
     * @var array
     */
    protected $invalidRows = [];

    /**
     * Csv File Path;.
     *
     * @var string
     */
    protected $filePath;

    /**
     * Csv File Name;.
     *
     * @var string
     */
    protected $fileName;

    /**
     * Csv File Directory;.
     *
     * @var string
     */
    protected $directory;

    /**
     * Csv delimiter;.
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * The rules to be applied to the data.
     *
     * @var array
     */
    protected $rules;

    /**
     * The array of custom error messages.
     *
     * @var array
     */
    public $customMessages = [];

    /**
     * The CSV header.
     *
     * @var array
     */
    public $headers = [];

    /**
     * Create a new Validator instance.
     *
     * @param string $filePath
     * @param string $delimiter
     * @param array  $rules
     * @param array  $messages
     */
    public function __construct($filePath, $delimiter, array $rules, array $messages = [])
    {
        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->rules = $rules;
        $this->customMessages = $messages;

        $this->setFileDirectory();
        $this->setFileName();
    }

    /**
     * Run the validator's rules against the supplied data.
     */
    public function validate()
    {
        if ($this->fails()) {
            return $this->errors();
        }

        return [
            'message' => self::SUCCESS_MESSAGE,
            'data' => $this->data,
        ];
    }

    /**
     * Return validation errors.
     */
    public function errors()
    {
        if (empty($this->message) && empty($this->invalidRows)) {
            $message = self::NO_ERROR_MESSAGE;
        } elseif (empty($this->message)) {
            $message = self::ERROR_MESSAGE;
        } else {
            $message = $this->message;
        }

        return [
            'message' => $message,
            'data' => $this->invalidRows,
        ];
    }

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * Determine if the data passes the validation rules.
     *
     * @return bool
     */
    protected function passes()
    {
        if ($this->doesFileExistAndReadable($this->filePath)) {
            if (false !== ($handle = fopen($this->filePath, 'r'))) {
                while (false !== ($row = fgetcsv($handle, 0, $this->delimiter))) {
                    ++$this->currentRowLineNumber;
                    if (empty($this->headers)) {
                        $this->setHeaders($row);
                        continue;
                    }

                    $rowWithAttribute = [];

                    foreach ($row as $key => $value) {
                        $attribute = $this->headers[$key];
                        $rowWithAttribute[$attribute] = $value;
                    }

                    $this->validateRow($rowWithAttribute);
                }
            }
        } else {
            $this->message = self::INVALID_FILE_PATH_ERROR;
        }

        return empty($this->invalidRows) && empty($this->message);
    }

    /**
     * Write the output data into any supplied format.
     *
     * @param ConverterHandlerInterface $format
     *
     * @return bool
     */
    public function write(Converter $format)
    {
        return $format
            ->convert($this->data)
            ->write($this->getWriteFileName($format->getExtension()));
    }

    /**
     * Set CSV filename.
     */
    protected function setFileName()
    {
        $this->fileName = basename($this->filePath, self::FILE_EXTENSION);
    }

    /**
     * Set CSV file directory.
     */
    protected function setFileDirectory()
    {
        $this->directory = dirname($this->filePath) . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the full path and name of the file to be written.
     *
     * @param $extension
     *
     * @return string
     */
    protected function getWriteFileName($extension)
    {
        return $this->directory . $this->fileName . '.' . $extension;
    }

    /**
     * Validate a given row with the supplied  rules.
     *
     * @param $row
     */
    protected function validateRow($row)
    {
        $this->currentRowMessages = [];
        $this->currentRow = $row;

        foreach ($this->rules as $attribute => $rules) {
            foreach ($rules as $rule) {
                $this->validateAttribute($attribute, $rule);
            }
        }

        if (!empty($this->currentRowMessages)) {
            $row['errors'] = $this->currentRowMessages;
            $this->invalidRows[] = $row;
        }

        $this->data[] = $row;
    }

    /**
     * Validate a given attribute against a rule.
     *
     * @param string $attribute
     * @param string $rule
     *
     * @return void|null
     */
    protected function validateAttribute($attribute, $rule)
    {
        list($rule, $parameters) = ValidationRuleParser::parse($rule);

        if ('' == $rule) {
            return;
        }

        $value = $this->getValue($attribute);

        if ($rule instanceof ValidationRule) {
            $this->validateUsingCustomRule($attribute, $value, $parameters, $rule);

            return;
        }

        if ($this->isValidateAble($rule, $parameters)) {
            $ruleClass = $this->getRuleClass($rule);
            if (!$ruleClass->passes($value, $parameters)) {
                $this->addFailure(
                    $this->getMessage($attribute, $ruleClass, $rule),
                    $attribute,
                    $value,
                    $ruleClass,
                    $parameters
                );
            }
        }
    }

    /**
     * @param $filePath
     *
     * @return bool
     */
    protected function doesFileExistAndReadable($filePath)
    {
        return file_exists($filePath) && is_readable($filePath);
    }

    /**
     * @param array $headers
     */
    protected function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * Determine if the attribute is validate-able.
     *
     * @param object|string $rule
     * @param string        $parameters
     *
     * @return bool
     */
    protected function isValidateAble($rule, $parameters)
    {
        return $this->ruleExists($rule) &&
            $this->passesParameterCheck($rule, $parameters);
    }

    /**
     * Get the class of a rule.
     *
     * @param $rule
     *
     * @return string
     */
    protected function getRuleClassName($rule)
    {
        return 'Oshomo\\CsvUtils\\Rules\\' . $rule;
    }

    /**
     * Get the class of a rule.
     *
     * @param $rule
     *
     * @return ValidationRuleInterface
     */
    protected function getRuleClass($rule)
    {
        $ruleClassName = $this->getRuleClassName($rule);

        return new $ruleClassName();
    }

    /**
     * Determine if a given rule exists.
     *
     * @param object|string $rule
     *
     * @return bool
     */
    protected function ruleExists($rule)
    {
        return $rule instanceof ValidationRule ||
            class_exists($this->getRuleClassName($rule));
    }

    /**
     * Determine if a given rule expect parameters and that the parameters where sent.
     *
     * @param object|string $rule
     * @param $parameters
     *
     * @return bool
     */
    protected function passesParameterCheck($rule, $parameters)
    {
        if (!$rule instanceof ValidationRule) {
            $rule = $this->getRuleClass($rule);
        }

        $ruleParameterCount = $rule->parameterCount();
        $parameterCount = count($parameters);

        return (0 === $ruleParameterCount) ? true : ($parameterCount === $ruleParameterCount);
    }

    /**
     * Validate an attribute using a custom rule object.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param $parameters
     * @param ValidationRuleInterface $rule
     */
    protected function validateUsingCustomRule($attribute, $value, $parameters, $rule)
    {
        if (!$rule->passes($value, $parameters)) {
            $this->addFailure($rule->message(), $attribute, $value, $rule, $parameters);
        }
    }

    /**
     * Add a failed rule and error message to the collection.
     *
     * @param $message
     * @param string                  $attribute
     * @param mixed                   $value
     * @param ValidationRuleInterface $rule
     * @param array                   $parameters
     */
    protected function addFailure($message, $attribute, $value, $rule, $parameters = [])
    {
        $this->currentRowMessages[] = $this->makeReplacements(
            $message,
            $attribute,
            $value,
            $rule,
            $parameters,
            $this->currentRowLineNumber
        );
    }

    /**
     * Get the value of a given attribute.
     *
     * @param string $attribute
     *
     * @return mixed
     */
    protected function getValue($attribute)
    {
        return $this->currentRow[$attribute];
    }
}
