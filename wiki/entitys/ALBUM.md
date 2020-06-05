## Управление Альбомами

В альбомах хранятся все Ваши файлы, альбомами можно делится или иметь общий альбом с друзьями

поле "**access**", может содержать следующие значения
* 1 - доступен только мне;
* 2 - доступен только выбранному списку пользователей;
* 3 - доступен всем по ссылке;

### Список доступных альбомов
```http request
GET /api/album
```

ожидаемый пагинированный ответ:
```json
{
  ...
  "album": [
    {
      "id": 1,
      "default": true,
      "name": "string",
      "access": 1,
      "uri": "string"
    }
  ]
}
```

### Получить карточку одного альбома
```http request
GET /api/album/{id}
```

ожидаемый ответ:
```json
{
  ...
  "album": {
    "id": 1,
    "default": true,
    "name": "string",
    "uri": "string",
    "access": 1,
    "access_users": [
      {
        "id": 1,
        "name": "string"
      }
    ]
  }
}
```

### Создать новый альбом
```http request
POST /api/album/
{
  "name": "string",
  "default": false, // не обязательно
  "access": 1 // не обязательно по умолчанию 1
}
```

ожидаемый ответ:
```json
{
  ...
  "album": {
    "id": 1,
    "default": true,
    "name": "string",
    "access": 1,
    "uri": "string"
  }
}
```

### Обновить состояние альбома
```http request
PUT /api/album/{id}
{
  "name": "string", // не обязательно
  "default": false, // не обязательно
  "access": 1 // не обязательно
}
```

ожидаемый ответ:
```json
{
  ...
  "album": {
    "id": 1,
    "default": true,
    "name": "string",
    "access": 1,
    "uri": "string"
  }
}
```

### Открыть доступ для пользователя
```http request
GET /api/album/{id}/open/{email}
```

### Закрыть доступ для пользователя
```http request
POST /api/album/{id}/close/{user_id}
```
