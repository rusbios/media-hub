## Файлы

статусы файла **status**
* 0 - Ошибка при загрузке;
* 1 - Находится во временном хранилище;
* 2 - обработка и загрузка в постоянное хранилище;
* 3 - Уже обработано и загружено в постоянное хранилище;

### Список доступных файлов
```http request
GET /api/file
```

ожидаемый пагинированный ответ:
```json
{
  ...
  "file": [
    {
      "guid": "string",
      "mime_type": "string",
      "preview": "string",
      "size": 0, // в мегабайтах
      "status": 3
    }
  ]
}
```

### Получить карточку файла
```http request
GET /api/file/{guid}
```

ожидаемый ответ:
```json
{
  ...
  "file": {
      "guid": "string",
      "name": "string",
      "mime_type": "string",
      "preview": "string",
      "size": 0,
      "status": 3,
      "created_at": "2020-06-04T09:44:22.000000Z"
  }
}
```

### Добавить файл
```http request
POST /api/file/
{
  "files": [],
  "ftp": 0 // не обязательно
  "aldum": 0 // не обязательно
}
```

ожидаемый ответ:
```json
{
  ...
  "file": [
    {
      "guid": "string",
      "name": "string",
      "status": 1
    }
  ]
}
```

### Удалить файл
```http request
DELETE /api/files/{guid}
```

### Скачать оригинал файла
```http request
GET /file/{guid}
```
