# AI Text Analysis Service (PHP)

**Languages:**  
[Русский](#русский) | [English](#english) | [Português](#português)

---

## Русский

### Описание

Пример сервиса анализа текста через LLM (большие языковые модели), реализованный на PHP с использованием чистой архитектуры.

Проект демонстрирует:
- разделение Domain и Infrastructure слоёв
- работу с AI через интерфейсы
- возможность легко менять AI-провайдеров
- использование DTO для запросов и ответов

На данный момент реализована интеграция с **Google Gemini**.

---

### Структура проекта

```text
src/
├── Domain/
│   └── AI/
│       ├── AiClientInterface.php
│       ├── AiGateway.php
│       ├── AiRequest.php
│       └── AiResponse.php
│
├── Infrastructure/
│   └── AI/
│       └── GeminiClient.php
```

---

### Архитектура

- **Domain** слой не зависит от конкретных AI-сервисов
- **Infrastructure** содержит адаптеры провайдеров
- `AiGateway` управляет выбором AI-клиента и fallback-логикой
- Добавление нового AI-провайдера не требует изменений Domain слоя

---

### Пример использования

```php
$request = new AiRequest(
    prompt: 'Проанализируй текст'
);

$response = $aiGateway->complete('gemini', $request);

if ($response->ok) {
    echo $response->text;
}
```

---

### Требования

- PHP 8.1+
- Composer
- API-ключ Google Gemini

---

## English

### Description

A sample text analysis service using LLMs (Large Language Models), built in PHP with a clean architecture approach.

The project demonstrates:
- separation of Domain and Infrastructure layers
- AI interaction via interfaces
- easy replacement of AI providers
- usage of DTOs for requests and responses

Currently, **Google Gemini** is implemented.

---

### Architecture

- **Domain** layer is independent of AI providers
- **Infrastructure** layer contains provider adapters
- `AiGateway` handles provider selection and fallback logic
- New providers can be added without modifying the Domain layer

---

### Usage example

```php
$request = new AiRequest(
    prompt: 'Analyze the text'
);

$response = $aiGateway->complete('gemini', $request);

if ($response->ok) {
    echo $response->text;
}
```

---

### Requirements

- PHP 8.1+
- Composer
- Google Gemini API key

---

## Português

### Descrição

Exemplo de um serviço de análise de texto usando LLMs (Modelos de Linguagem de Grande Escala), desenvolvido em PHP com arquitetura limpa.

O projeto demonstra:
- separação entre as camadas Domain e Infrastructure
- interação com IA via interfaces
- facilidade para trocar provedores de IA
- uso de DTOs para requisições e respostas

Atualmente, **Google Gemini** está implementado.

---

### Arquitetura

- A camada **Domain** é independente dos provedores de IA
- A camada **Infrastructure** contém os adaptadores
- `AiGateway` gerencia a escolha do provedor e o fallback
- Novos provedores podem ser adicionados sem alterar o Domain

---

### Exemplo de uso

```php
$request = new AiRequest(
    prompt: 'Analise o texto'
);

$response = $aiGateway->complete('gemini', $request);

if ($response->ok) {
    echo $response->text;
}
```

---

### Requisitos

- PHP 8.1+
- Composer
- Chave de API do Google Gemini
