<?php

declare(strict_types=1);

namespace Oshomo\CsvUtils\Validator;

use Oshomo\CsvUtils\Contracts\ConverterHandlerInterface;
use Oshomo\CsvUtils\Contracts\ParameterizedRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface;
use Oshomo\CsvUtils\Contracts\ValidationRuleInterface as ValidationRule;
use Oshomo\CsvUtils\Helpers\FormatsMessages;

class Validator
{
    use FormatsMessages;

    public const FILE_EXTENSION = '.csv';
    public const ERROR_MESSAGE = 'Validation fails.';
    public const NO_ERROR_MESSAGE = 'File is valid.';
    public const INVALID_FILE_PATH_ERROR = 'Supplied file is not accessible.';
    public const SUCCESS_MESSAGE = 'CSV is valid.';

    /**
     * The message bag instance.
     */
    protected array $currentRowMessages = [];

    /**
     * The CSV data.
     */
    protected array $data = [];

    /**
     * Initialisation errors.
     */
    protected string $message;

    /**
     * The line number of the row under validation.
     */
    protected int $currentRowLineNumber = 0;

    /**
     * The row under validation.
     */
    protected array $currentRow;

    /**
     * The invalid rows.
     */
    protected array $invalidRows = [];

    /**
     * Csv File Path;.
     */
    protected string $filePath;

    /**
     * Csv File Name;.
     */
    protected string $fileName;

    /**
     * Csv File Directory;.
     */
    protected string $directory;

    /**
     * Csv delimiter;.
     */
    protected string $delimiter = ',';

    /**
     * The rules to be applied to the data.
     */
    protected array $rules;

    /**
     * The array of custom error messages.
     */
    public array $customMessages = [];

    /**
     * The CSV header.
     */
    public array $headers = [];

    /**
     * Create a new Validator instance.
     */
    public function __construct(string $filePath, array $rules, string $delimiter = ',', array $messages = [])
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
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Determine if the data passes the validation rules.
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
     */
    public function write(ConverterHandlerInterface $format): bool
    {
        return $format
            ->convert($this->data)
            ->write($this->getWriteFileName($format->getExtension()));
    }

    /**
     * Set CSV filename.
     */
    protected function setFileName(): void
    {
        $this->fileName = basename($this->filePath, self::FILE_EXTENSION);
    }

    /**
     * Set CSV file directory.
     */
    protected function setFileDirectory(): void
    {
        $this->directory = dirname($this->filePath) . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the full path and name of the file to be written.
     */
    protected function getWriteFileName(string $extension): string
    {
        return $this->directory . $this->fileName . '.' . $extension;
    }

    /**
     * Validate a given row with the supplied  rules.
     */
    protected function validateRow(array $row): void
    {
        $this->currentRowMessages = [];
        $this->currentRow = $row;

        foreach ($this->rules as $attribute => $attributeRules) {
            foreach ($attributeRules as $key => $value) {
                if (is_int($key)) {
                    $rulePayload = $value;
                } else {
                    $rulePayload = [$key, $value];
                }

                $this->validateAttribute($attribute, $rulePayload, $row);
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
     */
    protected function validateAttribute(string $attribute, object|array|string $rule): void
    {
        list($rule, $parameters) = ValidationRuleParser::parse($rule);

        if ('' === $rule) {
            return;
        }

        $value = $this->getAttributeValueFromCurrentRow($attribute);

        if ($rule instanceof ValidationRule) {
            $this->validateUsingCustomRule($attribute, $value, $parameters, $rule);

            return;
        }

        if ($this->isValidateAble($rule, $parameters)) {
            $ruleClass = $this->getRuleClass($rule);
            if (!$ruleClass->passes($value, $parameters, $this->currentRow)) {
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

    protected function doesFileExistAndReadable(string $filePath): bool
    {
        return file_exists($filePath) && is_readable($filePath);
    }

    protected function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Determine if the attribute is validate-able.
     */
    protected function isValidateAble(object|string $rule, array $parameters): bool
    {
        return $this->ruleExists($rule) && $this->passesParameterCheck($rule, $parameters);
    }

    /**
     * Get the class of a rule.
     */
    protected function getRuleClassName(string $rule): string
    {
        return 'Oshomo\\CsvUtils\\Rules\\' . $rule;
    }

    /**
     * Get the class of a rule.
     */
    protected function getRuleClass(string $rule): ValidationRuleInterface
    {
        $ruleClassName = $this->getRuleClassName($rule);

        return new $ruleClassName();
    }

    /**
     * Determine if a given rule exists.
     */
    protected function ruleExists(object|string $rule): bool
    {
        return $rule instanceof ValidationRule || class_exists($this->getRuleClassName($rule));
    }

    /**
     * Determine if a given rule expect parameters and that the parameters where sent.
     */
    protected function passesParameterCheck(object|string $rule, array $parameters): bool
    {
        if (!$rule instanceof ValidationRule) {
            $rule = $this->getRuleClass($rule);
        }

        if ($rule instanceof ParameterizedRuleInterface) {
            $ruleParameterCount = count($rule->allowedParameters());
            $parameterCount = count($parameters);

            return $parameterCount === $ruleParameterCount;
        }

        return true;
    }

    /**
     * Validate an attribute using a custom rule object.
     */
    protected function validateUsingCustomRule(
        string $attribute,
        $value,
        array $parameters,
        ValidationRuleInterface $rule,
    ): void {
        if (!$rule->passes($value, $parameters, $this->currentRow)) {
            $this->addFailure($rule->message(), $attribute, $value, $rule, $parameters);
        }
    }

    /**
     * Add a failed rule and error message to the collection.
     */
    protected function addFailure(
        string $message,
        string $attribute,
        $value,
        ValidationRuleInterface $rule,
        array $parameters = [],
    ): void {
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
     */
    protected function getAttributeValueFromCurrentRow(string $attribute)
    {
        return $this->currentRow[$attribute];
    }
}
