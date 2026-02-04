<?php
declare(strict_types=1);

namespace App\Domain\AI;

/**
 * Контекст Strategy: хранит адаптеры и делегирует вызов выбранному.
 * Использование:
 *   $gw = new AiGateway([ new GeminiClient(...), new DeepSeekClient(...) ]);
 *   $res = $gw->complete('gemini', new AiRequest(prompt: '...'));
 */
final class AiGateway {
    /** @var array <string, AiClientInterface> */
    private array $clientsByName = [];

    /** @param iterable<AiClientInterface> $clients */
    public function __construct(iterable $clients)
    {
        foreach ($clients as $c){
            $this->clientsByName[$c->getName()]=$c;
        }
    }

    /**
     * Выполнить запрос у конкретного провайдера.
     * Если $provider === 'auto' - берём первый доступный адаптер.
     */
    public function complete(string $provider, AiRequest $request): AiResponse
    {
        if ($provider === 'auto'){
            $first = reset($this->clientsByName);
            if($first instanceof AiClientInterface){
                return $first->complete($request);
            }
            return new AiResponse(false, error: 'No AI clients configured');
        }

        $client = $this->clientsByName[$provider]??null;
        if(!$client){
            return new AiResponse(false, error: "Unknown AI provider: {$provider}");
        }
        return $client->complete($request);
    }

    /**
     * Базовая логика выбора провайдера и промпта для анализа текста.
    */
    public function completeAuto(array $facts, ?string $preferred = null): AiResponse
    {
        if (empty($facts)) {
            return new AiResponse(false, error: 'facts are required');
        }

        //  Базовая политика выбора провайдера
        $provider = $preferred
            ?? (isset($this->clientsByName['gemini']) ? 'gemini' : array_key_first($this->clientsByName));

        // Базовый промпт и ограничения
        $prompt = "Проанализируй текст и объедини его в 5 пунктов:\n".json_encode($facts, JSON_UNESCAPED_UNICODE);
        $req = new AiRequest(prompt: $prompt, temperature: 0.2, maxTokens: 600);

        // Основной вызов + фолбэк
        $resp = $this->complete($provider, $req);
        if (!$resp->ok) {
            foreach ($this->clientsByName as $name => $client) {
                if ($name === $provider) continue;
                $resp = $this->complete($name, $req);
                if ($resp->ok) break;
            }
        }
        return $resp;
    }

}
