funtask
===============

yet another multitasking framework base swoole

### env

- \>= PHP-7.1
- \>= Swoole-4.0.3

### install

```bash
wget http://pecl.php.net/get/swoole-4.0.3.tgz
tar xf swoole-4.0.3.tgz
cd swoole-4.0.3
phpize
./configure
make && make install
```

vi /your-path/php.ini
```vim
extension=swoole
```

### install test

```bash
php -m | grep swoole
php --ri swoole
```

### autoload

```bash
composer install
```

### example

```bash
php test/Boostrap.php
```