# OtusSmartLinks

[![codecov](https://codecov.io/gh/HiDiv/OtusSmartLinks/graph/badge.svg?token=2XB3G5M3IH)](https://codecov.io/gh/HiDiv/OtusSmartLinks)

Проектная работа на тему "Умные ссылки" для курса "Архитектура и шаблоны проектирования" OTUS

## Система «Умных ссылок» состоит из двух основных модулей:

1. **Цепочка обработчиков запросов** (middleware)
2. **Ядро «умных ссылок»** (Strategy → Condition → Action)

### 1. Цепочка обработчиков запросов

Все HTTP-запросы проходят через цепочку классов, реализующих `RequestHandlerInterface`.

Каждый обработчик:

* Реализует метод
    ```php
    public function handle(Request $request): Response
    ```
* В случае невозможности собственно обработать запрос вызывает
    ```php
    $this->next($request)
    ```
    чтобы передать управление следующему звену цепочки.

#### Добавление нового middleware-обработчика:

1. Создать класс, имплементирующий `RequestHandlerInterface`.
2. Пометить его атрибутом
    ```php
    #[AsTaggedItem('app.request_handler', priority: <число>)]
    ```
3. Symfony DI автоматически соберёт все обработчики и выстроит их по убыванию `priority`.

#### Примеры реализованных обработчиков

* `MethodNotAllowedHandler` (priority 900): сразу возвращает 405 для всех не-GET-запросов.
* `SmartRedirectHandler` (priority 500): запускает логику «умных ссылок» и редиректит по первой успешно прошедшей стратегии.
* `NotFoundHandler` (priority 0): всегда отдаёт 404, если никто ранее не обработал запрос.

### 2. Ядро «умных ссылок».

Все настройки «умных ссылок» хранятся в БД как три сущности:

* **Strategy**
* * `path` — точное совпадение URL-path
* * `priority` — порядок проверки (DESC)
* * `conditions` → `Condition` (0…N)
* * `action` → `Action` (0‒1)

* **Condition**
* * `handlerTag` — код условного обработчика
* * `parameters` — массив доп. аргументов

* **Action**
* * `handlerTag` — код обработчика действия
* * `parameters` — массив доп. аргументов

#### Flow обработки одного запроса:

1. В `SmartRedirectHandler` по `path` из `$request` вызываем
    ```php
    $strategies = $repo->fetchForPath($path);
    ```
    — получаем массив `Strategy`, уже с `conditions` и `action` через `LEFT JOIN`.

2. Для каждой `Strategy` по-порядку:
   * Проверяем все её условия:
       ```php
       foreach ($conditions as $conditionEntity) {
          $tag = $conditionEntity->getHandlerTag();
          $params = $conditionEntity->getParameters() ?? [];
    
          $checker = $this->conditionResolver->getCondition($tag);
          if (!$checker->check($request, $params)) {
              return false;
          }
       }
       ```
       — если нет условий, считаем их пройденными.
   * Выполняем действие: 
       ```php
       $actionEntity = $strategy->getAction();
       $tag = $actionEntity->getHandlerTag();
       $params = $actionEntity->getParameters() ?? [];
       return $this->actionResolver->getAction($tag)->handle($request, $params);
       ```

3. Если ни одна стратегия не сработала, передаём управление следующему middleware.

### 3. Подключаемые стратегии

* **ConditionStrategy** (интерфейс `ConditionStrategyInterface`)

  Метод
  ```php
  public function check(Request $request, array $params): bool
  ```
  Пример:
  ```php
  #[AsTaggedItem('before_timestamp')]
  class BeforeTimestampStrategy implements ConditionStrategyInterface
  {
      public function check(Request $request, array $params): bool { /*…*/ }
  }
  ```

* **ActionStrategy** (интерфейс `ActionStrategyInterface`)

  Метод:
  ```php
  public function handle(Request $request, array $params): Response
  ```
  Пример:
  ```php
  #[AsTaggedItem('redirect')]
  class RedirectStrategy implements ActionStrategyInterface
  {
      public function handle(Request $request, array $params): Response { /*…*/ }
  }
  ```

#### Добавление нового Condition/Action-обработчика:

1. Имплементировать соответствующий интерфейс.
2. Пометить атрибутом
    ```php
    #[AsTaggedItem('<handlerTag>')]
    ```
3. DI автоматически загрузит класс в локатор — дальше он доступен по `getCondition(tag)` или `getAction(tag)`.

## UML-диаграмма flow-обработки запросов

```mermaid
flowchart TD
    A[Incoming HTTP Request] --> B[RequestHandlerChainService handle]

    B --> C[First Middleware handle]
    C -->|cannot handle| D[SmartRedirectHandler handle]
    D --> E[StrategyProcessor process]

    E --> F{Strategies for path?}
    F -->|none| P[Next Middleware handle]
    F -->|one or more| G[Loop through strategies]

subgraph StrategyLoop direction TD
G --> H[Take next Strategy]
H --> I{Conditions exist?}
I -->|no| K[Execute Action]
I -->|yes| J[Loop through Conditions]

subgraph ConditionLoop direction TD
J --> L[Take next Condition]
L --> M[ConditionStrategy check]
M -->|false| Q[Skip to next Strategy]
M -->|true| N{More Conditions?}
N -->|yes| L
N -->|no| K
end

K --> O[ActionStrategy handle]
O --> R[Return Response]
Q --> H
end

P --> Z[Fallback Response]
```

**Пояснение:**

1. Запрос сначала проходит через цепочку middleware — вплоть до SmartRedirectHandler.
2. Внутри SmartRedirectHandler запускается StrategyProcessor.
3. Из БД получают все стратегии по path.
4. Для каждой стратегии последовательно перебираются её условия; при первом false переходят к следующей стратегии.
5. Если все условия вернули true (или их не было), выполняется соответствующее действие и возвращается HTTP-ответ.
6. Если ни одна стратегия не сработала, управление возвращается следующему middleware и отдаётся «fallback»-ответ.

## Проблемы сложности и пути их решения.

### Ускорение поиска нужного middleware  
**Проблема:** каждый запрос последовательно проходит все обработчики, и с ростом их числа время ответа растёт.  
**Решение:** добавить префильтрацию по условию и кешировать ее, чтобы сразу вызывать только релевантные звенья цепочки.

### Удобное управление правилами и приоритетами  
**Проблема:** при большом количестве стратегий трудно визуально контролировать порядок и логику.  
**Решение:** создать веб-UI с drag-and-drop приоритетов, фильтрами и симулятором прохождения запроса.

### Надёжность обработки исключений  
**Проблема:** исключение в любом `ConditionStrategy` или `ActionStrategy` обрывает цепочку.  
**Решение:**
- Внедрить обёртки-спасатели (`try/catch`) вокруг каждого вызова стратегии.
- Логировать контекст через Monolog + Sentry.
- При ошибке переходить к «дефолтному» действию или отдавать заранее настроенный fallback-ответ.
