Soal Teknis dari detik.com
=============================

membuat restapi dan php-cli yang memiliki spesifikasi sebagai berikut :

- API untuk membuat article
- API untuk edit article
- PHP-Cli untuk menggabungkan data artikel dan data dari Dummy-API
  
  Apabila task php-cli ini di akses, maka akan menarik data yang ada di URL dummy API di atas,
  dan menggabungkan dengan data record article di database.
  Data dummy API akan disisipkan sesuai urutan position-nya.
  Kombinasi data yang sudah tergabung tersebut hanya akan ditampilkan sebanyak 5
  data saja dan dimunculkan di terminal saat PHP-Cli ini saat di eksekusi.
  
INSTALASI
------------

1. Clone repositori ini

        $ git clone https://github.com/ifanfairuz/detikcom-interview-article-api.git && cd detikcom-interview-article-api

2. Salin konfigurasi `env.ini.example` menjadi `env.ini`

        $ cp env.ini.example env.ini

2. isi file konfigurasi `env.ini`

        [env]
        env = development

        [database]
        db_connection = pgsql ; can be mysql or pgsql
        db_host       = localhost
        db_port       = 5432
        db_name       = detik-interview
        db_user       = root
        db_password   = 
        db_socket     = 

3. jalankan migrasi database

        $ php cli migrate

      > jika anda mengalami masalah koneksi dengan mysql coba isi bagian `db_socket` dengan alamat ke file socket di file `env.ini`

        db_socket     = path/to/mysql.sock

4. jalankan data seed

        $ php cli seed

5. jalankan server

        $ php -S localhost:8000 -t public
 
 
 
ENDPOINTS
------------

#### Insert Article
`POST` /article/create - insert artikel ke database

***200 OK***
```
{
    "article_id": 7,
    "created_at": "2023-05-31 13:13:53"
}
```

#### Update Article
`PUT` /article/update - update artikel ke database

***200 OK***
```
{
    "article_id": 7,
    "updated_at": "2023-05-31 13:13:53"
}
```

#### Error Response

***400 Bad Request***
```
{
    "status": 400,
    "message": "Bad Request"
    "info": {
        "error": "InvalidValidation",
        "type": "required",
        "field": "article_id",
        "message": "article_id harus diisi."
    }
}
```

CLI
------------

#### Menggabungkan Data Artikel

**Artikel**

    $ php cli artikel
    $ php cli artikel --dump  // untuk mengeluarkan hasil menggunakan fungsi var_dump()

**Migrate**

    $ php cli migrate


#### Migrasi

**Migrate**

    $ php cli migrate
    
**Rolback**

    $ php cli migrate --rollback 2 // berarti rollback 2 step
    
### Seed

**Seed**

    $ php cli seed


