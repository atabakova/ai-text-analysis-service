<?php
declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\AI\{AiClientInterface, AiRequest, AiResponse};
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class GeminiClient implements AiClientInterface
{
    public function __construct(
        private HttpClientInterface $http,
        private string $apiKey,
        private string $defaultModel = 'gemini-1.5-pro',
        private ?LoggerInterface $logger = null,
        private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta'
    ) {
        $this->logger ??= new NullLogger();
    }

    public function getName(): string
    {
        return 'gemini';
    }

    public function complete(AiRequest $request): AiResponse
    {
        $model = $request->model ?? $this->defaultModel;
        $url = sprintf('%s/models/%s:generateContent?key=%s',
            rtrim($this->baseUrl, '/'),
            rawurlencode($model),
            $this->apiKey
        );

        $payload = [
            'contents' => [[
                'role'  => 'user',
                'parts' => [['text' => $request->prompt]],
            ]],
            'generationConfig' => [
                'temperature'     => $request->temperature,
                'maxOutputTokens' => $request->maxTokens,
            ],
        ];

        if ($request->system) {
            // Gemini 1.5 - отдельное поле systemInstruction
            $payload['systemInstruction'] = [
                'role'  => 'system',
                'parts' => [['text' => $request->system]],
            ];
        }

        // Провайдер-специфичные параметры (safetySettings, tools и т.д.)
        if ($request->extra) {
            $payload = array_replace_recursive($payload, $request->extra);
        }

        $t0 = microtime(true);
        try {
            $resp = $this->http->request('POST', $url, [
                'json'    => $payload,
                'timeout' => 30,
            ]);

            $status = $resp->getStatusCode();
            $json   = $resp->toArray(false);

            // Обработка блокировок без 4xx
            $blockReason = $json['promptFeedback']['blockReason'] ?? null;
            if (is_string($blockReason) && $blockReason !== 'BLOCK_REASON_UNSPECIFIED') {
                $msg = $json['promptFeedback']['safetyRatings'][0]['category'] ?? 'Content blocked';
                return new AiResponse(false, error: (string)$msg, provider: $this->getName(), model: $model, raw: $json);
            }

            if ($status < 200 || $status >= 300) {
                $err = $json['error']['message'] ?? ('HTTP ' . $status);
                return new AiResponse(false, error: $err, provider: $this->getName(), model: $model, raw: $json);
            }

            // Склеиваем все parts с text
            $text = '';
            foreach ($json['candidates'][0]['content']['parts'] ?? [] as $part) {
                if (isset($part['text'])) {
                    $text .= (string)$part['text'];
                }
            }
            $text = trim($text);

            if ($text === '') {
                // Если пусто - возвращаем ошибку
                return new AiResponse(false, error: 'Empty response from model', provider: $this->getName(), model: $model, raw: $json);
            }

            $this->logger->info('Gemini OK', [
                'model' => $model,
                'ms'    => (int)round((microtime(true) - $t0) * 1000),
            ]);

            return new AiResponse(
                ok: true,
                text: $text,
                provider: $this->getName(),
                model: $model,
                usage: $json['usageMetadata'] ?? [],
                raw: $json
            );

        } catch (\Throwable $e) {
            $this->logger->error('GeminiClient error: ' . $e->getMessage(), ['exception' => $e]);
            return new AiResponse(false, error: $e->getMessage(), provider: $this->getName(), model: $model);
        }
    }
}
