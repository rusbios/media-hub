# Документация к API MediaHub

Для работы с API необходимо необходим **токен**, инструкция по его получению [тут](entitys/AUTH.md).

Токен необходимо передавать в заголовке запроса или в параметрах запроса, примеры:
```http request
GET /user/info?api_token={token}
```
или
```http request
POST /user/info
X-API-TOKEN: {token}
```
или
```http request
POST /user/info
{"api-token":"token"}
```

В случае успешного выполнения запроса в ответ придёт json, код ответа **200**
```json
{
  "success": true,
  ...
}
```

В случае возникновение ошибок вернётся json с ошибкой,
 код ответа будет указывать на ошибку например: **400** 
```json
{
  "success": false,
  "error": "message"
}
```

Работа с пагинацией, в проекте некоторые сущности возвращают пагинированный ответ,
формат JSON ответа в этом случае:
```json
{
  "success": true,
  "pagination": {
    "total": 100,
    "page": 10,
    "per_page": 1
  },
  ...
}
```

Сущности с которыми API позволяет работать:

* [Пользователь](entitys/AUTH.md)
* [Альбомы](entitys/ALBUM.md)
* [Хранилища](entitys/STORAGE.md)
* [Файлы](entitys/FILES.md)
