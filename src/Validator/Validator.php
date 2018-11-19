<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Validator;

use Oshomo\CsvUtils\Contracts\ConverterHandlerInterface;
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
     * @param array $rules
     * @param array $messages
     */
    public function __construct(string $filePath, string $delimiter = ',', array $rules, array $messages = [])
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
     *
     * @return array
     */
    public function validate(): array
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
     *
     * @return array
     */
    public function errors(): array
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
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Determine if the data passes the validation rules.
     *
     * @return bool
     */
    protected function passes(): bool
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
    public function write(ConverterHandlerInterface $format): bool
    {
        return $format
            ->convert($this->data)
            ->write($this->getWriteFileName($format->getExtension()));
    }

    /**
     * Set CSV filename.
     *
     * @return void
     */
    protected function setFileName(): void
    {
        $this->fileName = basename($this->filePath, self::FILE_EXTENSION);
    }

    /**
     * Set CSV file directory.
     *
     * @return void
     */
    protected function setFileDirectory(): void
    {
        $this->directory = dirname($this->filePath) . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the full path and name of the file to be written.
     *
     * @param string $extension
     *
     * @return string
     */
    protected function getWriteFileName(string $extension): string
    {
        return $this->directory . $this->fileName . '.' . $extension;
    }

    /**
     * Validate a given row with the supplied  rules.
     *
     * @param array $row
     */
    protected function validateRow(array $row): void
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
     * @param string|object $rule
     *
     * @return void
     */
    protected function validateAttribute(string $attribute, $rule): void
    {
        list($rule, $parameters) = ValidationRuleParser::parse($rule);

        if ('' === $rule) {
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

            return;
        }
    }

    /**
     * @param string $filePath
     *
     * @return bool
     */
    protected function doesFileExistAndReadable(string $filePath): bool
    {
        return file_exists($filePath) && is_readable($filePath);
    }

    /**
     * @param array $headers
     *
     * @return void
     */
    protected function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Determine if the attribute is validate-able.
     *
     * @param object|string $rule
     * @param array $parameters
     *
     * @return bool
     */
    protected function isValidateAble($rule, array $parameters): bool
    {
        return $this->ruleExists($rule) && $this->passesParameterCheck($rule, $parameters);
    }

    /**
     * Get the class of a rule.
     *
     * @param string $rule
     *
     * @return string
     */
    protected function getRuleClassName(string $rule): string
    {
        return 'Oshomo\\CsvUtils\\Rules\\' . $rule;
    }

    /**
     * Get the class of a rule.
     *
     * @param string $rule
     *
     * @return ValidationRuleInterface
     */
    protected function getRuleClass(string $rule): ValidationRuleInterface
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
    protected function ruleExists($rule): bool
    {
        return $rule instanceof ValidationRule || class_exists($this->getRuleClassName($rule));
    }

    /**
     * Determine if a given rule expect parameters and that the parameters where sent.
     *
     * @param object|string $rule
     * @param array $parameters
     *
     * @return bool
     */
    protected function passesParameterCheck($rule, array $parameters): bool
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
     * @param mixed $value
     * @param array $parameters
     * @param ValidationRuleInterface $rule
     *
     * @return void
     */
    protected function validateUsingCustomRule(string $attribute, $value, array $parameters, ValidationRuleInterface $rule): void
    {
        if (!$rule->passes($value, $parameters)) {
            $this->addFailure($rule->message(), $attribute, $value, $rule, $parameters);
        }
    }

    /**
     * Add a failed rule and error message to the collection.
     *
     * @param string $message
     * @param string $attribute
     * @param mixed $value
     * @param ValidationRuleInterface $rule
     * @param array $parameters
     *
     * @return void
     */
    protected function addFailure(string $message, string $attribute, $value, ValidationRuleInterface $rule, array $parameters = []): void
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
    protected function getValue(string $attribute)
    {
        return $this->currentRow[$attribute];
    }
}
