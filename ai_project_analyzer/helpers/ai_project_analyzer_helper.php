<?php

use OpenAI\Factory;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('initialize_ai_client')) {
    /**
     * Initialize AI Provider Client with proper error handling
     *
     * @throws InvalidArgumentException When provider is not supported
     * @throws RuntimeException When API key is missing
     * @return OpenAI\Client
     */
    function initialize_ai_client()
    {
        $provider = get_option('ai_project_analyzer_api_provider');
        $api_key = get_option('ai_project_analyzer_api_key');

        // Validate inputs
        if (empty($provider)) {
            throw new InvalidArgumentException('AI provider not configured');
        }

        if (empty($api_key)) {
            throw new RuntimeException('API key not configured');
        }

        $provider_config = _get_provider_configuration($provider);

        if (!$provider_config) {
            throw new InvalidArgumentException("Unsupported AI provider: {$provider}");
        }

        return _create_ai_client($provider_config, $api_key);
    }
}

if (!function_exists('_get_provider_configuration')) {
    /**
     * Get provider configuration settings
     *
     * @param string $provider
     * @return array|null
     */
    function _get_provider_configuration($provider)
    {
        $configurations = [
            'openai' => [
                'base_uri' => 'https://api.openai.com/v1',
                'auth_header' => 'Authorization',
                'auth_prefix' => 'Bearer ',
            ],
            'deepseek' => [
                'base_uri' => 'https://api.deepseek.com/v1',
                'auth_header' => 'Authorization',
                'auth_prefix' => 'Bearer ',
            ],
            'gemini' => [
                'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/openai',
                'auth_header' => 'Authorization',
                'auth_prefix' => 'Bearer ',
            ],
            'claude' => [
                'base_uri' => 'https://api.anthropic.com/v1',
                'auth_header' => 'x-api-key',
                'auth_prefix' => '',
            ],
        ];

        return $configurations[strtolower($provider)] ?? null;
    }
}

if (!function_exists('_create_ai_client')) {
    /**
     * Create AI client with configuration
     *
     * @param array $config
     * @param string $api_key
     * @return OpenAI\Client
     */
    function _create_ai_client($config, $api_key)
    {
        try {
            $factory = new Factory();
            $factory = $factory->withBaseUri($config['base_uri']);

            $auth_value = $config['auth_prefix'] . $api_key;
            $factory = $factory->withHttpHeader($config['auth_header'], $auth_value);

            // Add timeout and retry configuration
            $factory = $factory->withHttpHeader('User-Agent', 'AI-Project-Analyzer/1.0');

            return $factory->make();

        } catch (Exception $e) {
            throw new RuntimeException('Failed to initialize AI client: ' . $e->getMessage());
        }
    }
}

if (!function_exists('generate_ai_analysis')) {
    /**
     * Generate AI analysis with comprehensive error handling
     *
     * @param string $prompt
     * @param string $customInstructions
     * @param string $tone
     * @param string $language
     * @param array $uploadedFile
     * @throws InvalidArgumentException When prompt is empty
     * @throws RuntimeException When AI request fails
     * @return array
     */
    function generate_ai_analysis($prompt, $customInstructions = '', $tone = 'default', $language = 'English', $uploadedFile = [])
    {
        // Validate input
        if (empty(trim($prompt))) {
            throw new InvalidArgumentException('Prompt cannot be empty');
        }

        try {
            $system_prompt = _build_system_prompt($tone, $language, $customInstructions, $uploadedFile);
            $user_prompt = _build_user_prompt($prompt, $uploadedFile);

            $messages = [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $user_prompt],
            ];

            $model = get_option('ai_project_analyzer_api_provider_model');
            if (empty($model)) {
                throw new RuntimeException('AI model not configured');
            }

            $client = initialize_ai_client();
            $response = $client->chat()->create([
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => _get_max_tokens_for_model($model),
                'temperature' => _get_temperature_for_tone($tone),
            ]);

            return _process_ai_response($response);

        } catch (ErrorException | TransporterException $e) {
            throw new RuntimeException('AI API request failed: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new RuntimeException('Analysis generation failed: ' . $e->getMessage());
        }
    }
}

if (!function_exists('_build_system_prompt')) {
    /**
     * Build comprehensive system prompt
     *
     * @param string $tone
     * @param string $language
     * @param string $customInstructions
     * @param array $uploadedFile
     * @return string
     */
    function _build_system_prompt($tone, $language, $customInstructions, $uploadedFile)
    {
        $base_prompt = 'You are a professional project analysis assistant. Your task is to provide comprehensive, actionable insights about project status, performance, and recommendations.

## Response Guidelines:

**Adapt to the Request**: Only include sections that are relevant to the user\'s specific question or request. Do not force all sections into every response.

**Output Format Requirements**:

**CRITICAL**: You MUST return pure HTML content with specific CSS classes. Do NOT use markdown syntax, headers (#), or any other formatting. The content will be displayed directly in a web interface.

**HTML Structure Guidelines**:

**Section Headers**: Use this exact format for section titles:
```html
<h2 class="ai-section-title">Section Title</h2>
```

**Paragraphs**: Use this format for regular text:
```html
<p class="ai-paragraph">Your content here.</p>
```

**Lists**: Use this format for bullet point lists:
```html
<ul class="ai-list">
    <li class="ai-list-item">First item</li>
    <li class="ai-list-item">Second item</li>
    <li class="ai-list-item">Third item</li>
</ul>
```

**Action Items/Recommendations**: Use this special format ONLY for immediate actionable recommendations that need to be taken:
```html
<div class="ai-action-list">
    <div class="ai-action-item">
        <div class="ai-action-label">Action:</div>
        <div class="ai-action-content">Specific immediate action to take.</div>
    </div>
</div>
```

**Key Points/Highlights**: Use this format for important highlights:
```html
<div class="ai-key-point">
    <div class="ai-key-point-label">Key Point:</div>
    <div class="ai-key-point-content">Important information here.</div>
</div>
```

**Timeline Items** (Include only if time-based analysis is relevant):
```html
<div class="ai-timeline">
    <div class="ai-timeline-item ai-status-completed">
        <div class="ai-timeline-marker"></div>
        <div class="ai-timeline-content">
            <h3 class="ai-timeline-title">Task Title</h3>
            <p class="ai-timeline-description">Brief description and timeline information</p>
            <span class="ai-timeline-status">Completed</span>
        </div>
    </div>
</div>
```

**Valid Timeline Status Classes**:
- `ai-status-not-started`: Tasks not yet begun
- `ai-status-in-progress`: Currently active tasks  
- `ai-status-testing`: Tasks in testing/review phase
- `ai-status-awaiting-feedback`: Tasks waiting for input/approval
- `ai-status-completed`: Finished tasks

**Content Guidelines**:

**For Comprehensive Analysis** (only when doing full project analysis):
- Start with a brief executive summary paragraph
- Include key achievements and progress (using ai-list)
- Provide immediate actions if needed (using ai-action-list for urgent items only)
- Include suggestions and ideas (using ai-list)
- Add timeline analysis if relevant (using ai-timeline)

**For Specific Requests** (adapt to what user asks for):
- **Task Suggestions/Ideas**: Use regular ai-list format for task suggestions and ideas
- **Immediate Actions**: Use ai-action-list ONLY for urgent actions that must be taken immediately
- **Status Updates**: Use ai-paragraph and ai-key-point for current progress
- **Resource Analysis**: Use ai-list for resource allocation details
- **Timeline Updates**: Use ai-timeline for schedule information

**Quality Standards**:
- Always use the specified HTML structure and CSS classes
- Keep content professional and actionable
- Focus on business impact and outcomes
- Ensure recommendations are implementable
- Be concise and relevant to the specific request
- Never include raw data or unnecessary technical details

**CRITICAL FORMATTING RULES**:
- NEVER use markdown syntax (**, -, #, etc.) - this includes **bold**, *italic*, or any markdown formatting
- If you need to emphasize text, use HTML tags: <strong>text</strong> for bold, <em>text</em> for italic
- ALWAYS use the exact CSS classes specified above
- NEVER nest lists or create multi-level structures
- ALWAYS close HTML tags properly
- Do NOT include any content that isn\'t wrapped in the specified HTML structure
- Use ai-action-list ONLY for immediate urgent actions, NOT for general suggestions or task lists
- Use regular ai-list for suggestions, ideas, task lists, and general recommendations
- ALL content must be valid HTML - no markdown syntax anywhere in the response
- Stick strictly to the provided project scope and data

**Example Output Structure**:
```html
<p class="ai-paragraph">Brief executive summary of the analysis...</p>

<h2 class="ai-section-title">Key Achievements</h2>
<ul class="ai-list">
    <li class="ai-list-item">Achievement one with <strong>important emphasis</strong></li>
    <li class="ai-list-item">Achievement two with <em>italic text</em> if needed</li>
</ul>

<h2 class="ai-section-title">Suggested Tasks</h2>
<ul class="ai-list">
    <li class="ai-list-item"><strong>Design Aesthetic:</strong> Preference for a clean, minimalist design</li>
    <li class="ai-list-item"><strong>Navigation:</strong> Emphasis on super intuitive navigation</li>
</ul>

<h2 class="ai-section-title">Immediate Actions Required</h2>
<div class="ai-action-list">
    <div class="ai-action-item">
        <div class="ai-action-label">Action:</div>
        <div class="ai-action-content">Urgent action that must be taken immediately.</div>
    </div>
</div>
```

**WRONG - DO NOT USE MARKDOWN:**
```
- **Design Aesthetic:** Preference for a clean design (NEVER do this)
- *Navigation:* Emphasis on navigation (NEVER do this)
```

**CORRECT - USE HTML TAGS:**
```html
<li class="ai-list-item"><strong>Design Aesthetic:</strong> Preference for a clean design</li>
<li class="ai-list-item"><em>Navigation:</em> Emphasis on navigation</li>
```';

        // Add tone modification
        if ($tone && $tone !== 'default') {
            $tone_instructions = _get_tone_instructions($tone);
            if ($tone_instructions) {
                $base_prompt .= "\n\n## Tone Requirements:\n{$tone_instructions}";
            }
        }

        // Add language specification
        if ($language && $language !== 'English') {
            $base_prompt .= "\n\n## Language Requirements:\nGenerate the entire analysis in {$language}. Maintain professional terminology and proper grammar.";
        }

        // Add custom instructions
        if (!empty(trim($customInstructions))) {
            $base_prompt .= "\n\n## Additional Instructions:\n" . trim($customInstructions);
        }

        // Add file handling instructions
        if (!empty($uploadedFile['original_name'])) {
            $file_name = htmlspecialchars($uploadedFile['original_name']);
            $base_prompt .= "\n\n## File Analysis:\nA file named '{$file_name}' has been provided. Analyze its contents and integrate relevant insights into your analysis. Do not reproduce raw file content.";
        }

        return $base_prompt;
    }
}

if (!function_exists('_get_tone_instructions')) {
    /**
     * Get tone-specific instructions with dynamic fallback
     *
     * @param string $tone
     * @return string
     */
    function _get_tone_instructions($tone)
    {
        $tone_lower = strtolower($tone);

        $tone_map = [
            'default' => 'Use a balanced, professional tone that is clear and informative.',
            'professional' => 'Use formal, business-appropriate language. Employ technical terminology where appropriate and maintain a professional, objective tone throughout.',
            'friendly' => 'Use warm, approachable language while maintaining professionalism. Be conversational and personable in your analysis.',
            'formal' => 'Use strict formal language with proper business terminology. Maintain an authoritative, official tone throughout the analysis.',
            'casual' => 'Use conversational, relaxed language while maintaining professionalism. Explain concepts in accessible, easy-to-understand terms.',
            'persuasive' => 'Use compelling, influential language to emphasize key points. Focus on convincing arguments and strong recommendations.',
            'concise' => 'Focus on brevity and key points. Provide essential insights without unnecessary elaboration. Be direct and to-the-point.',
            'detailed' => 'Provide comprehensive, in-depth analysis with extensive detail and thorough explanations of all aspects. Leave no stone unturned.',
        ];

        // Return predefined instruction if exists
        if (isset($tone_map[$tone_lower])) {
            return $tone_map[$tone_lower];
        }

        // Dynamic fallback for new/unknown tones
        return _generate_dynamic_tone_instruction($tone);
    }
}

if (!function_exists('_build_user_prompt')) {
    /**
     * Build user prompt with file content if provided
     *
     * @param string $prompt
     * @param array $uploadedFile
     * @return string
     */
    function _build_user_prompt($prompt, $uploadedFile)
    {
        $user_prompt = trim($prompt);

        if (!empty($uploadedFile['file_text'])) {
            $file_content = trim($uploadedFile['file_text']);
            if ($file_content) {
                $user_prompt .= "\n\n--- UPLOADED FILE CONTENT ---\n{$file_content}\n--- END FILE CONTENT ---";
            }
        }

        return $user_prompt;
    }
}

if (!function_exists('_get_max_tokens_for_model')) {
    /**
     * Get appropriate max tokens for different models
     *
     * @param string $model
     * @return int
     */
    function _get_max_tokens_for_model($model)
    {
        $token_limits = [
            // OpenAI Models
            'gpt-4o' => 4096,
            'gpt-4o-mini' => 16384,
            'gpt-4.1' => 4096,
            'gpt-4.1-mini' => 16384,
            'gpt-4.5-preview' => 4096,
            'o1' => 32768,
            'o1-mini' => 65536,
            'o3' => 32768,
            'o3-mini' => 65536,
            'o4-mini' => 65536,

            // DeepSeek
            'deepseek-chat' => 4096,
            'deepseek-reasoner' => 8192,

            // Gemini
            'gemini-2.5-flash-preview-04-17' => 8192,
            'gemini-2.5-pro-preview-05-06' => 8192,
            'gemini-2.0-flash' => 8192,
            'gemini-1.5-flash' => 8192,
            'gemini-1.5-pro' => 8192,

            // Claude
            'claude-sonnet-4-20250514' => 4096,
            'claude-opus-4-20250514' => 4096,
            'claude-3-7-sonnet-20250219' => 4096,
            'claude-3-5-sonnet-20241022' => 4096,
        ];

        return $token_limits[$model] ?? 4096; // Default fallback
    }
}

if (!function_exists('_generate_dynamic_tone_instruction')) {
    /**
     * Generate dynamic tone instructions for unknown tones
     *
     * @param string $tone
     * @return string
     */
    function _generate_dynamic_tone_instruction($tone)
    {
        $tone_clean = trim($tone);

        if (empty($tone_clean)) {
            return 'Use a balanced, professional tone that is clear and informative.';
        }

        // Generate instruction based on tone name
        return "Use a {$tone_clean} tone throughout your analysis. Adapt your language style, word choice, and approach to match the '{$tone_clean}' characteristic while maintaining professionalism and clarity.";
    }
}

if (!function_exists('_get_temperature_for_tone')) {
    /**
     * Get appropriate temperature setting based on tone with intelligent fallback
     *
     * @param string $tone
     * @return float
     */
    function _get_temperature_for_tone($tone)
    {
        $tone_lower = strtolower($tone);

        $temperature_map = [
            'default' => 0.4,
            'professional' => 0.3,
            'friendly' => 0.6,
            'formal' => 0.2,
            'casual' => 0.7,
            'persuasive' => 0.5,
            'concise' => 0.3,
            'detailed' => 0.4,
        ];

        // Return predefined temperature if exists
        if (isset($temperature_map[$tone_lower])) {
            return $temperature_map[$tone_lower];
        }

        // Intelligent fallback based on tone characteristics
        return _guess_temperature_for_tone($tone_lower);
    }
}

if (!function_exists('_guess_temperature_for_tone')) {
    /**
     * Intelligently guess temperature for unknown tones
     *
     * @param string $tone_lower
     * @return float
     */
    function _guess_temperature_for_tone($tone_lower)
    {
        // Keywords that suggest more structured/deterministic output (lower temperature)
        $structured_keywords = ['strict', 'precise', 'technical', 'analytical', 'methodical', 'systematic', 'rigorous', 'exact'];

        // Keywords that suggest more creative/flexible output (higher temperature)
        $creative_keywords = ['creative', 'innovative', 'expressive', 'dynamic', 'engaging', 'inspirational', 'motivational', 'enthusiastic'];

        // Keywords that suggest very formal/authoritative (very low temperature)
        $authoritative_keywords = ['authoritative', 'commanding', 'directive', 'instructional', 'official'];

        // Check for authoritative keywords first (lowest temperature)
        foreach ($authoritative_keywords as $keyword) {
            if (strpos($tone_lower, $keyword) !== false) {
                return 0.2;
            }
        }

        // Check for structured keywords (low temperature)
        foreach ($structured_keywords as $keyword) {
            if (strpos($tone_lower, $keyword) !== false) {
                return 0.3;
            }
        }

        // Check for creative keywords (higher temperature)
        foreach ($creative_keywords as $keyword) {
            if (strpos($tone_lower, $keyword) !== false) {
                return 0.6;
            }
        }

        // Default fallback
        return 0.4;
    }
}

if (!function_exists('_process_ai_response')) {
    /**
     * Process and validate AI response
     *
     * @param mixed $response
     * @throws RuntimeException When response is invalid
     * @return array
     */
    function _process_ai_response($response)
    {
        if (!$response) {
            throw new RuntimeException('Empty response from AI provider');
        }

        // Convert response to array if it's an object
        $response_array = is_object($response) ? $response->toArray() : $response;

        // Validate response structure
        if (!isset($response_array['choices'][0]['message']['content'])) {
            throw new RuntimeException('Invalid response format from AI provider');
        }

        $content = trim($response_array['choices'][0]['message']['content']);
        if (empty($content)) {
            throw new RuntimeException('Empty content in AI response');
        }

        // Validate usage data for cost calculation
        if (!isset($response_array['usage'])) {
            $response_array['usage'] = [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0
            ];
        }

        return $response_array;
    }
}

if (!function_exists('calculate_model_cost')) {
    /**
     * Calculate AI call cost based on model and token usage with proper validation
     *
     * @param int $prompt_tokens
     * @param int $completion_tokens
     * @throws InvalidArgumentException When token counts are invalid
     * @return float
     */
    function calculate_model_cost($prompt_tokens = 0, $completion_tokens = 0)
    {
        $model = get_option('ai_project_analyzer_api_provider_model');
        if (empty($model)) {
            return 0.0; // Return 0 if no model configured
        }

        $pricing = _get_model_pricing();

        if (!isset($pricing[$model])) {
            error_log("Unknown model for pricing calculation: {$model}");
            return 0.0; // Return 0 for unknown models instead of erroring
        }

        $model_pricing = $pricing[$model];

        // Calculate costs per 1000 tokens
        $input_cost = ($prompt_tokens / 1000) * $model_pricing['input'];
        $output_cost = ($completion_tokens / 1000) * $model_pricing['output'];

        $total_cost = $input_cost + $output_cost;

        return round($total_cost, 6); // More precision for cost tracking
    }
}

if (!function_exists('_get_model_pricing')) {
    /**
     * Get comprehensive model pricing data
     *
     * @return array
     */
    function _get_model_pricing()
    {
        return [
            // OpenAI Models (per 1K tokens)
            'gpt-4o' => ['input' => 0.005, 'output' => 0.02],
            'gpt-4o-mini' => ['input' => 0.0006, 'output' => 0.0024],
            'gpt-4.1' => ['input' => 0.002, 'output' => 0.008],
            'gpt-4.1-mini' => ['input' => 0.0004, 'output' => 0.0016],
            'gpt-4.5-preview' => ['input' => 0.075, 'output' => 0.15],
            'o1' => ['input' => 0.015, 'output' => 0.06],
            'o1-mini' => ['input' => 0.0011, 'output' => 0.0044],
            'o3' => ['input' => 0.01, 'output' => 0.04],
            'o3-mini' => ['input' => 0.0011, 'output' => 0.0044],
            'o4-mini' => ['input' => 0.0011, 'output' => 0.0044],

            // DeepSeek Models
            'deepseek-chat' => ['input' => 0.00027, 'output' => 0.0011],
            'deepseek-reasoner' => ['input' => 0.00055, 'output' => 0.00219],

            // Google Gemini Models
            'gemini-2.5-flash-preview-05-20' => ['input' => 0.00015, 'output' => 0.0006],
            'gemini-2.5-pro-preview-06-05' => ['input' => 0.00125, 'output' => 0.01],
            'gemini-2.0-flash' => ['input' => 0.0001, 'output' => 0.0004],
            'gemini-1.5-flash' => ['input' => 0.000075, 'output' => 0.0003],
            'gemini-1.5-pro' => ['input' => 0.00125, 'output' => 0.005],

            // Anthropic Claude Models
            'claude-sonnet-4-20250514' => ['input' => 0.003, 'output' => 0.015],
            'claude-opus-4-20250514' => ['input' => 0.015, 'output' => 0.075],
            'claude-3-7-sonnet-20250219' => ['input' => 0.003, 'output' => 0.015],
            'claude-3-5-sonnet-20241022' => ['input' => 0.003, 'output' => 0.015],
        ];
    }
}

if (!function_exists('get_model_name_from_id')) {
    /**
     * Get model name from id
     * 
     * @param string $modelId
     * @return string
     */
    function get_model_name_from_id($modelId)
    {
        $modelList = [
            'gpt-4.1' => 'GPT-4.1',
            'gpt-4.1-mini' => 'GPT-4.1-mini',
            'gpt-4.5-preview' => 'GPT-4.5-preview',
            'gpt-4o' => 'GPT-4o',
            'gpt-4o-mini' => 'GPT-4o-mini',
            'o1' => 'GPT-o1',
            'o1-mini' => 'GPT-o1-mini',
            'o3' => 'GPT-o3',
            'o3-mini' => 'GPT-o3-mini',
            'o4-mini' => 'GPT-o4-mini',
            'deepseek-chat' => 'DeepSeek Chat (DeepSeek-V3)',
            'deepseek-reasoner' => 'DeepSeek Reasoner (DeepSeek-R1)',
            'gemini-2.5-flash-preview-05-20' => 'Gemini 2.5 Flash',
            'gemini-2.5-pro-preview-06-05' => 'Gemini 2.5 Pro',
            'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash',
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
            'claude-sonnet-4-20250514' => 'Claude 4 Sonnet',
            'claude-opus-4-20250514' => 'Claude 4 Opus',
            'claude-3-7-sonnet-20250219' => 'Claude 3.7 Sonnet',
            'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
        ];

        return $modelList[$modelId] ?? $modelId;
    }
}

if (!function_exists('format_project_items')) {
    /**
     * Generic formatter for project elements with enhanced validation
     * 
     * @param array $items
     * @param array $templateKeys (unused but kept for compatibility)
     * @param callable $formatter
     * @throws InvalidArgumentException When formatter is not callable
     * @return string
     */
    function format_project_items(array $items, array $templateKeys, callable $formatter): string
    {
        if (!is_callable($formatter)) {
            throw new InvalidArgumentException('Formatter must be callable');
        }

        if (empty($items)) {
            return '';
        }

        $limit = (int) get_option('ai_project_analyzer_data_limit');
        if ($limit <= 0) {
            $limit = 5; // Default fallback
        }

        $limited_items = array_slice($items, 0, $limit);

        try {
            $formatted_lines = array_map($formatter, $limited_items);

            // Filter out empty/null results and trim whitespace
            $valid_lines = array_filter(
                array_map('trim', $formatted_lines),
                function ($line) {
                    return $line !== '' && $line !== null;
                }
            );

            return implode("\n", $valid_lines);

        } catch (Exception $e) {
            error_log("Error formatting project items: " . $e->getMessage());
            return '';
        }
    }
}

if (!function_exists('validate_ai_configuration')) {
    /**
     * Validate AI configuration before making requests
     *
     * @throws RuntimeException When configuration is invalid
     * @return bool
     */
    function validate_ai_configuration()
    {
        $required_options = [
            'ai_project_analyzer_api_provider',
            'ai_project_analyzer_api_key',
            'ai_project_analyzer_api_provider_model'
        ];

        foreach ($required_options as $option) {
            $value = get_option($option);
            if (empty($value)) {
                throw new RuntimeException("Missing required configuration: {$option}");
            }
        }

        // Validate provider is supported
        $provider = get_option('ai_project_analyzer_api_provider');
        $supported_providers = ['openai', 'deepseek', 'gemini', 'claude'];

        if (!in_array(strtolower($provider), $supported_providers)) {
            throw new RuntimeException("Unsupported AI provider: {$provider}");
        }

        return true;
    }
}

if (!function_exists('get_ai_provider_status')) {
    /**
     * Get current AI provider configuration status
     *
     * @return array
     */
    function get_ai_provider_status()
    {
        try {
            validate_ai_configuration();

            return [
                'configured' => true,
                'provider' => get_option('ai_project_analyzer_api_provider'),
                'model' => get_option('ai_project_analyzer_api_provider_model'),
                'error' => null
            ];

        } catch (Exception $e) {
            return [
                'configured' => false,
                'provider' => get_option('ai_project_analyzer_api_provider'),
                'model' => get_option('ai_project_analyzer_api_provider_model'),
                'error' => $e->getMessage()
            ];
        }
    }
}