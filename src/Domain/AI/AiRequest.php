<?php

declare(strict_types=1);

namespace App\Domain\AI;

/**
 * Вход для LLM.
 * Рекомендация: держать prompt/system короткими и явными,
 * temperature ~0.0–0.3 (более детерминированные рекомендации).
 */
final class AiRequest{
    public function __construct(
        public string $prompt,
        public ?string $system = null,
        public ?string $model = null, // если null — адаптер возьмёт свой дефолт
        public float $temperature = 0.2,
        public int $maxTokens = 800,
        public array $extra = []  // любые провайдер-специфичные параметры
    )
    {

    }
}
