# Symfony docker template

## NEW Setup process

- make reset-all
- make watch
- open new terminal and make in-dc to use yarn, composer, bin/console commands

### Site link:
http://localhost:8811

### Phpmyadmin link:
http://localhost:8812
root
root

### Mailhog link:
http://localhost:8814

### To log as admin:
admin0@admin0.com
admin0

---
## OLD Setup process

- Verify settings in composer.json like the project name and description
- Move .env.example or .env.prod.example to .env and edit parameters to match your project
- Make sure permission are well-defined for 1000:1000 (`sudo chown 1000:1000 /path/ -R`)
- Start containers with `docker-compose up -d`
- Go into the php container with `docker-compose exec php bash`
- Install libs with `composer install`
- Setup nodejs with `npm install`
- Run either `npm run dev` or `npm run prod`
- Generate application secret key in .env at field `APP_SECRET` with `bin/console key-generate`
- Enjoy :)
