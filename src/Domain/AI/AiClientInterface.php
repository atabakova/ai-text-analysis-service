<?php
declare(strict_types=1);

namespace App\Domain\AI;

interface AiClientInterface {
    //Уникальное имя провайдера: 'gemini', 'deepseek', ...
    public function getName():string;

    //Выполнить запрос к модели и вернуть унифицированный ответ
    public function complete(AiRequest $request): AiResponse;
}
