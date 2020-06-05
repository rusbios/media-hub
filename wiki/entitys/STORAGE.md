## Хранилища

Доступные типы хранилищ "**type**"
* ftp
* ftps

### Список доступных хранилищ
```http request
GET /api/storage
```

ожидаемый пагинированный ответ:
```json
{
  ...
  "storage": [
    {
      "id": 1,
      "type": "ftp",
      "default": "bool",
      "host": "string"
    }
  ]
}
```

### Получить карточку хранилища
```http request
GET /api/storage/{id}
```

ожидаемый ответ:
```json
{
  "id": 1,
  "type": "ftp",
  "default": "bool",
  "host": "string",
  "port": 21,
  "login": "string",
  "size": 0, // в мегабайтах
  "total": 0, // в мегабайтах или null если параметр недоступен
}
```

### Добавить хранилище
```http request
POST /api/storage/
{
  "type": "ftp",
  "default": "bool", // не обязательно
  "host": "string",
  "port": 21,
  "login": "string",
  "password": "string"
}
```

### Обновить параметры хранилища
```http request
POST /api/storage/{id}
{
  "default": "bool", // не обязательно
  "host": "string", // не обязательно
  "port": 21, // не обязательно
  "login": "string", // не обязательно
  "password": "string" // не обязательно
}
```

### Удалить хранилище
при удалении хранилища можно указать новое хранилище, в этом случае все файлы автоматически перенесутся на него,
по окончанию процесса копирования придёт уведомление.
```http request
DELETE /api/storage/{id}
{
  "mv_storage": 1 // не обязательно
}
```
