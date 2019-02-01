### Шаги установки и запуска

```
composer install
```
```
cp .env.example .env
```
```
git submodule init && git submodule update
```
``` 
cp .laradock.env laradock/.env
```
```
cd laradock
```
``` 
docker-compose up -d nginx postgres
```
``` 
docker-compose exec workspace bash
```
```  
php artisan migrate:fresh
```

 Можно пробовать загружать фотографии через `http://127.0.0.1:8080/api/photos/upload`.
 
 Запуск тестов
 ```  
 vendor/bin/phpunit
 ```
