## Авторизация пользователя

Время жизни токена авторизации 24 часа

### Информация об авторизации
```http request
GET /auth/info
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
POST /auth/reg
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
POST /auth/api_token
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
GET /auth/recover/{email}
```

### Новый пароль
Поменять пароль можно при помощи API_TOKEN или токена из письма [восстановления пароля]
```http request
POST /auth/new_pas/{token}
{
  "password": "string",
  "confirm_password": "string"
}
```

### Подтвердить email
в данном случае необходимый токен из письма

```http request
GET /auth/email/{token}
```
