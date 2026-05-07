<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\DTO;

final class FunctionData
{
    public function __construct(
        /**
         * The name of the function e.g. getCurrentTemperature.
         *
         * @var string
         */
        public string $name,

        /**
         * Arguments for the function.
         * JSON format arguments.
         *
         * @var string|null
         */
        public ?string $arguments = null,

        /**
         * A description of the function.
         *
         * @var string|null
         */
        public ?string $description = null,

        /**
         * Parameters for the function.
         * JSON Schema object.
         *
         * @var Schema|null
         */
        public ?Schema $parameters = null
    ) {
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->name,
                'arguments' => $this->arguments,
                'description' => $this->description,
                'parameters' => $this->parameters?->toArray(),
            ]
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['arguments'] ?? null,
            $data['description'] ?? null,
            $data['parameters'] ?? null
        );
    }
}