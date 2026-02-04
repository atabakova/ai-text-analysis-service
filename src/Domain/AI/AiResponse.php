<?php
declare(strict_types=1);

namespace App\Domain\AI;

final class AiResponse
{
    public function __construct(
        public bool $ok,
        public string $text = '', // нормализованный текст-ответ
        public ?string $error = null, // описание ошибки, если есть
        public ?string $provider = null, // имя провайдера (из адаптера)
        public ?string $model = null, // фактическая модель
        public array $usage = [], // токены/стоимость/и т.п., если доступны
        public array $raw = []  // сырой распарсенный JSON ответа
    )
    {

    }
}
