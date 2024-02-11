
# jubilant-bassoon

A lottery app thats awesome

## Run Locally

Clone the project

```bash
  git clone https://github.com/nzuzondlovu/jubilant-bassoon.git
```

Go to the project directory

```bash
  cd jubilant-bassoon
```

Install dependencies

```bash
  composer install
```

## Add CSV files

Load files to the following respective folders
Tickets
```bash
  ./storage/app/SFTP/Tickets/
```
Winnings
```bash
  ./storage/app/SFTP/Tickets/
```

Run command to compare winnings

```bash
  php artisan app:lotto-draw
```