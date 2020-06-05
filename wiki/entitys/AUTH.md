## Авторизация пользователя

### Информация об авторизации
```http request
GET /user/info
```

ожидаемый ответ:
```json
{
  ...
  "user": {
    "id": 1,
    "name": "string",
    "email": "email@domain.ru"
  }
}
```

### Регистрация
```http request
POST /user/reg
{
  "name": "string",
  "email":  "string",
  "password": "string",
  "confirm_password": "string"
}
```

ожидаемый ответ:
```json
{
  ...
  "user": {
    "id": 1,
    "name": "string",
    "email": "email@domain.ru"
  }
}
```

### Получить токен для API
```http request
POST /user/api_token
{
  "email": "string",
  "password": "string"
}
```

ожидаемый ответ:
```json
{
  ...
  "api_token": "string"
}
```

### Запрос на восстановление пароля
```http request
GET /user/recover/{email}
```

### Новый пароль
Поменять пароль можно при помощи API_TOKEN или токена из письма [восстановления пароля]
```http request
POST /user/new_pas/{token}
{
  "password": "string",
  "confirm_password": "string"
}
```

### Подтвердить email
в данном случае необходимый токен из письма

```http request
GET /user/email/{token}
```
