# Yandex disk backuper

Backuper working with [Yandex Disk](http://disk.yandex.ru/) for creating backups your web sites

## 1 Install
### 1.1 With [Composer](https://getcomposer.org/)

    composer require ruspanzer/ya-disk-backuper

### 1.2 Standalone

    git clone git@github.com:RuSPanzer/ya-disk-backuper.git /path/to/backuper
    cd /path/to/backuper
    composer install
    
## 2 Run

    php bin/backuper.php <command> [args]
    
Creating backups:

    php bin/backuper.php backuper:backup path/to/config.json
    
Decrypting backup:

    php bin/backuper.php backuper:decrypt path/to/backup.zip.encrypted --key=<encrypt_key>
    
**For working with encrypted files need `mcrypt` extension**

## 3 Configuration

For creating backup use command `backuper:backup` with path to `config.json` as first argument

Example `config.json`

    {
        "token": "AQAAAAFDsQDgAAN6Z0qSZqekmEFbvf5KTnfT_5r",
        "remote-backups-dir": "/TestBck/",
        "backups": {
            "site-1": {
                "crypt-key": "ThisSuperSecretKey",
                "filesystem": {
                    "files": [
                        "/etc/nginx/conf.d/site1.conf"
                    ],
                    "dirs": [
                        "/srw/www/site1/"
                    ],
                    "excluded-dirs": [
                        "/srw/www/site1/data/cache"
                    ]
                },
                "databases": {
                    "site_1": {
                        "host": "localhost",
                        "dbname": "site_1",
                        "user": "site_user",
                        "pass": "EtHewCuh=Peu7Es1Et33E$n!1U4Y@3243q8YdAgCuq8dfd",
                        "exclude-tables": [
                            "api_logs"
                        ]
                    },
                    "site_1_logs": {
                        "host": "localhost",
                        "dbname": "site_1_logs",
                        "user": "site_user",
                        "pass": "EtHewCuh=Peu7Es1Et33E$n!1U4Y@3243q8YdAgCuq8dfd"
                    }
                }
            },
            "my-second-backup": {
                "previous-backups-count": 3,
                "filesystem": {
                    "dirs": [
                        "/srv/www/site2/"
                    ],
                    "excluded-dirs": [
                        "/srw/www/site1/vendor"
                    ]
                }
            }
        }
    }

Main section:

| Param    |      Required      |  Description |
|----------|:-------------:|------:|
| `token` |  yes | [Yandex Disk OAuth token for WebDAV API](https://tech.yandex.ru/disk/webdav/) |
| `remote-backups-dir` | no | Remote root dir for backups. By default is _/Backups/_ |
| `backups` | yes |  Collections of backup configs. Each backup config must have a name  |

One backup configuration:

| Param    |      Required      |  Description |
|----------|:-------------:|------:|
| `previous-backups-count` |  no | Count of previous backup saving on remote server. If count remote backups more than this param, old backups will be deleted |
| `crypt-key` | no | Key for encrypting backup archive. If empty the backup will not be encrypted  |
| `filesystem` | no | Filesystem backup config. For details see bellow |
| `databases` | no | Collections of database configs |

Filesystem config:

| Param    |      Required      |  Description |
|----------|:-------------:|------:|
| `files` |  no | Array of path to one file |
| `dirs` | no | Array of path to dirs. Nested dirs are included in backup recursively |
| `excluded-dirs` | no | Array of dirs which will be ignored. |

Database config:

*Backuper working only with MySQL databases*

| Param    |      Required      |  Description |
|----------|:-------------:|------:|
| `host` |  yes | MySQL database host |
| `dbname` | yes | Database name |
| `user` | yes | Database user |
| `pass` | yes | Password |
| `exclude-tables` | no | Array of tables which will be ignored. Default empty |

One backup may include one fylesystem config and one databases config with more databases.

`config.json` may include  more backup configurations
