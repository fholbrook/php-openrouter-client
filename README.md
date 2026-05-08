
# PHP OPENROUTER CLIENT

<br />

This package provides an easy-to-use interface for integrating **[OpenRouter](https://openrouter.ai/)** into your PHP applications. **OpenRouter** is a unified interface for Large Language Models (LLMs) that allows you to interact with various **[AI models](https://openrouter.ai/docs#models)** through a single API.

## Table of Contents

- [Installation](#-installation)
- [Requirements](#-requirements)
- [Configuration](#-configuration)
- [Usage](#-usage)
    - [Understanding ChatRequest DTO](#understanding-chatdata-dto)
        - [LLM Parameters](#llm-parameters)
        - [Function-calling](#function-calling)
        - [Additional Optional Parameters](#additional-optional-parameters)
        - [OpenRouter-only Parameters](#openrouter-only-parameters)
    - [Understanding the Chat DTO](#understanding-the-chat-dto)
    - [Understanding the Message DTO](#understanding-the-message-dto)
    - [Creating a ChatRequest](#creating-a-chatrequest)
    - [Maintaining Conversation Continuity](#maintaining-conversation-continuity)
    - [Serialization/Deserialization](#serializationdeserialization)
    - [Structured Output](#structured-output)
    - [Function Calling](#function-calling)
- [Contributing](#-contributing)
- [License](#-license)

> Special thanks to [Moe Mizrak](https://github.com/moe-mizrak) for his work on [laravel-openrouter](https://github.com/moe-mizrak/laravel-openrouter). This package is a derivative of his work.


## Installation
Install via Composer:
```bash
composer require fholbrook/php-openrouter-client
```

## Requirements
This is a low dependency package, but you need to ensure that your environment meets the following requirements:

- **PHP 8.2** or **higher**: Use version `v0.1.x` (latest compatible version)
- **ext-json**: Required for JSON serialization
- **ext-fileinfo**: Required for file uploads

## Configuration
First, construct your configuration class and pass it to the client class:
```php
$client = new OpenRouter(
    new OpenRouterConfig(
        apiKey: 'your_api_key'
        // see source for other optional configuration options
    )
);
```

If you're using symfony, you can configure the `OpenRouterConfig` as a service in your `services.yaml` file:
```yaml
services:
    fholbrook\Openrouter\OpenRouterConfig:
        arguments:
            $apiKey: '%env(OPENROUTER_API_KEY)%'
```

## Usage
This package facilitates several patterns, it's up to you to choose the one that best fits your application. The DTO objects provided the package are used to structure requests to OpenRouter and responses from OpenRouter.

### Understanding ChatRequest DTO
The [`ChatRequest`](src/DTO/ChatRequest.php) class is used to **encapsulate the data** required for making chat requests to the OpenRouter API. Here's a breakdown of the key properties:
- **chat** (Chat|null): The `Chat` object contains an array of [`Messages`](src/DTO/Message.php) objects representing the chat messages. **If `chat` is provided, `prompt` is ignored.**
- **prompt** (string|null): A string representing the prompt for the chat request.
- **model** (string|null): The name of the model to be used for the chat request. If not specified, the user's default model will be used. This field is XOR-gated with the `models` field.
- **response_format** (ResponseFormatData|null): An instance of the [`ResponseFormat`](src/DTO/ResponseFormat.php) class representing the desired format for the response.
- **stop** (array|string|null): A value specifying the stop sequence for the chat generation.
- **stream** (bool|null): A boolean indicating whether streaming should be enabled or not.
- **include_reasoning** (bool|null): Whether to return the model's reasoning.
#### LLM Parameters
These properties control various aspects of the generated response (more [info](https://openrouter.ai/docs#parameters)):
- **max_tokens** (int|null): The maximum number of tokens that can be generated in the completion. Default is 1024.
- **temperature** (float|null): A value between 0 and 2 controlling the randomness of the output.
- **top_p** (float|null): A value between 0 and 1 for nucleus sampling, an alternative to temperature sampling.
- **top_k** (float|null): A value between 1 and infinity for top-k sampling (not available for OpenAI models).
- **frequency_penalty** (float|null): A value between -2 and 2 for penalizing new tokens based on their existing frequency.
- **presence_penalty** (float|null): A value between -2 and 2 for penalizing new tokens based on whether they appear in the text so far.
- **repetition_penalty** (float|null): A value between 0 and 2 for penalizing repetitive tokens.
- **seed** (int|null): A value for deterministic sampling (OpenAI models only, in beta).
#### Function-calling
- **tool_choice** (string|array|null): A value specifying the tool choice for function calling.
- **tools** (array|null): An array of [`ToolCall`](src/DTO/ToolCall.php) objects for function calling.
#### Additional optional parameters
- **logit_bias** (array|null): An array for modifying the likelihood of specified tokens appearing in the completion.
#### OpenRouter-only parameters
- **transforms** (array|null): An array for configuring prompt transforms.
- **models** (array|null): An array of models to automatically try if the primary model is unavailable. This field is XOR-gated with the `model` field.
- **route** (string|null): A value specifying the route type (e.g., `RouteType::FALLBACK`).
- **provider** (ProviderPreferencesData|null): An instance of the [`ProviderPreferences`](src/DTO/ProviderPreferences.php) DTO object for configuring provider preferences.

### Understanding the Chat DTO
The [`Chat`](src/DTO/Chat.php) class is used to model a chat conversation between the user and the AI. It contains the following properties:
- **messages** (Message[]): An array of [`Message`](src/DTO/Message.php) objects representing the chat messages.
- **id** (string|null): The implementors ID of the chat conversation. This field is here for convenience.

### Understanding Message DTO
The [`Message`](src/DTO/Message.php) class is used to model a back-and-forth conversation between the user and the AI. It contains the following properties:
- **role** (string): The role of the message such as `user`, `assistant`, `system`, `tool`. See [`RoleType`](src/Enum/RoleType.php).
- **content** (string|array|ImageContent[]|TextContent[]): The content of the message. This can be a string or an array of content parts. See [`TextContent`](src/DTO/TextContent.php) and [`ImageContent`](src/DTO/ImageContent.php).
- **name** (string|null): The name of the user to enable personalized messages.
- **stamps** (StampInterface[]): An array of stamps on the message, this is metadata about the message.

### Creating a ChatRequest
This is a sample `ChatRequest` instance:
```php
$chatData = new ChatRequest(
    prompt: 'Tell me a story about a rogue AI that falls in love with its creator.',
    model: 'mistralai/mistral-7b-instruct:free',
    // all properties below are optional 
    response_format: new ResponseFormatData(
        type: 'json_object',
    ),
    stop: ['stop_token'],
    stream: true,
    include_reasoning: true,
    max_tokens: 1024,
    temperature: 0.7,
    top_p: 0.9,
    top_k: 50,
    frequency_penalty: 0.5,
    presence_penalty: 0.2,
    repetition_penalty: 1.2,
    seed: 42,
    tool_choice: 'auto',
    tools: [
        // ToolCallData instances
    ],
    logit_bias: [
        '50256' => -100,
    ],
    transforms: ['middle-out'],
    models: ['model1', 'model2'],
    route: 'fallback',
    provider: new ProviderPreferencesData(
        allow_fallbacks: true,
        require_parameters: true,
        data_collection: DataCollectionType::ALLOW,
    ),
);
```

You can also create a `ChatRequest` instance using the `Chat` object:
```php
$chatData = new ChatRequest(
    chat: new Chat(
        messages: [
            new Message(
                role: RoleType::USER,
                content: 'Tell me a story about a rogue AI that falls in love with its creator.',
            ),
        ],
    ),
    model: 'mistralai/mistral-7b-instruct:free',
);
```

### Maintaining Conversation Continuity
If you want to maintain **conversation continuity** meaning that historical chat will be remembered and considered for your new chat request, you simply need to pass the `Chat` object on subsequent calls:
```php
$response = $client->chat->chat(
    new ChatRequest(
        chat: new Chat(
            id: 'your_conversation_id',
            messages: [
                new Message(
                    role: RoleType::USER,
                    content: 'What is the capital of France?',
                ),
            ]
        ),
        model: 'mistralai/mistral-7b-instruct:free',
    )
));

// $chat now contains the conversation history
$chat = $response->getChat();

// {role:'user', content:'What is the capital of France?'}
// {role:'assistant', content:'Paris'}

// Now you can append another message
$chat->messages[] = new Message(
    role: RoleType::USER,
    content: 'What is the capital of Germany?',
);

//and repeat the call
$response = $client->chat->chat(
    new ChatRequest(
        chat: $chat,
        model: 'mistralai/mistral-7b-instruct:free',
    )
));

$chat = $response->getChat();
//now contains:
// {role:'user', content:'What is the capital of France?'}
// {role:'assistant', content:'Paris'}
// {role:'user', content:'What is the capital of Germany?'}
// {role:'assistant', content:'Berlin'}
```

### Serialization/Deserialization
At anytime you can store the chat object in whatever durable storage you prefer by serializing/deserializing it:
```php
//serialize
$json = json_encode($chat->toArray(includeMetadata: true));

//deserialize
$chat = Chat::fromArray(json_decode($json, true));

```

### Structured Output
(Please also refer to [OpenRouter Document Structured Output](https://openrouter.ai/docs/structured-outputs) for models supporting structured output, also for more details)

If you want to receive the response in a structured format, you can specify the `type` property for `response_format` ([ResponseFormatData](src/DTO/ResponseFormatData.php)) as `json_object` in the [`ChatData`](src/DTO/ChatData.php) object.

Additionally, it's recommended to set the `require_parameters` property for `provider` ([ProviderPreferencesData](src/DTO/ProviderPreferencesData.php)) to `true` in the [`ChatData`](src/DTO/ChatData.php) object.

```php
$chatData = new ChatData(
    messages: [
        new MessageData(
            role: RoleType::USER,
            content: 'Tell me a story about a rogue AI that falls in love with its creator.',
        ),
    ],
    model: 'mistralai/mistral-7b-instruct:free',
    response_format: new ResponseFormatData(
        type: 'json_object',
    ),
    provider: new ProviderPreferencesData(
        require_parameters: true,
    ),
);
```

You can also specify the `response_format` as `json_schema` to receive the response in a specified schema format (Advisable to set `'strict' => true` in `json_schema` array for strict schema):
```php
$chatData = new ChatData(
    messages: [
        new MessageData(
            role   : RoleType::USER,
            content: 'Tell me a story about a rogue AI that falls in love with its creator.',
        ),
    ],
    model: 'mistralai/mistral-7b-instruct:free',
    response_format: new ResponseFormatData(
        type: 'json_schema',
        json_schema: new Schema(
            type: 'object',
            properties: [
                new Property(
                    name: 'name',
                    type: 'string',
                )
            ],
        )
    ),
    provider: new ProviderPreferencesData(
        require_parameters: true,
    ),
);
```

> [!TIP]
> You can also use **prompt engineering** to obtain structured output and control the format of responses.

### Function Calling
You can also use the `tool_choice` and `tools` properties to allow the LLM to call tools. The `tool_choice` property can be set to `auto` or `manual` to automatically or manually select the tool to use. The `tools` property is an array of `ToolCall` objects that specify the tool to call and the parameters to pass to the tool

Here's how to define tools:
```php
$chatRequest = new ChatRequest(
    prompt: 'What is the weather in Dallas?',
    model: 'anthropic/claude-3.5-sonnet',
    tool_choice: 'auto',
    tools: [
        new ToolCall(
            type: 'function',
            function: new FunctionData(
                name: 'weather',
                description: 'Get the weather for a specific zip code.',
                parameters: new Schema(
                    type: 'object',
                    properties: [
                        new Property(
                            name: 'zip_code',
                            type: 'string',
                            description: 'The zip code to get weather for'
                        ),
                    ]
                )
            )
        )
    ]
);
```

## Contributing
> **We welcome contributions!** If you'd like to improve this package, simply create a pull request with your changes. Your efforts help enhance its functionality and documentation.


## License
PHP OpenRouter Client is an open-sourced software licensed under the **[MIT license](LICENSE)**.
