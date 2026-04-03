#!/usr/bin/env bash
set -e

PR_NUMBER=local
APP_IMAGE=php-sf-template:pr-local
COMPOSE="docker compose -p pr-$PR_NUMBER -f docker/staging/docker-compose.yml"

echo "==> Ensuring nginx-proxy network exists"
docker network create nginx-proxy 2>/dev/null || true

echo "==> Building image"
DOCKER_BUILDKIT=1 docker build \
  -f docker/staging.Dockerfile \
  --build-arg SERVER_IP=127.0.0.1 \
  -t "$APP_IMAGE" \
  .

echo "==> Tearing down previous stack"
PR_NUMBER=$PR_NUMBER APP_IMAGE=$APP_IMAGE $COMPOSE down -v --remove-orphans 2>/dev/null || true

echo "==> Starting stack"
PR_NUMBER=$PR_NUMBER APP_IMAGE=$APP_IMAGE $COMPOSE up -d --wait

echo "==> Granting MySQL/MariaDB user access to staging_pr${PR_NUMBER}_% databases"
docker exec pr-$PR_NUMBER-mysql-1 mysql -uroot -prootpassword \
  -e "GRANT ALL PRIVILEGES ON \`staging_pr${PR_NUMBER}\\_%\`.* TO 'user'@'%'; FLUSH PRIVILEGES;"
docker exec pr-$PR_NUMBER-mariadb-1 mariadb -uroot -prootpassword \
  -e "GRANT ALL PRIVILEGES ON \`staging_pr${PR_NUMBER}\\_%\`.* TO 'user'@'%'; FLUSH PRIVILEGES;"

echo "==> Creating databases and schemas"
for em in main blog payments; do
  PR_NUMBER=$PR_NUMBER APP_IMAGE=$APP_IMAGE $COMPOSE exec -T app php bin/console doctrine:database:create --if-not-exists --connection=$em
  PR_NUMBER=$PR_NUMBER APP_IMAGE=$APP_IMAGE $COMPOSE exec -T app php bin/console doctrine:schema:create --em=$em
done

echo "==> Loading fixtures"
for em in main blog payments; do
  PR_NUMBER=$PR_NUMBER APP_IMAGE=$APP_IMAGE $COMPOSE exec -T app \
    php bin/console doctrine:fixtures:custom-loader --no-interaction --em=$em --group=$em
done

echo "==> Setting up test databases"
for em in main blog payments; do
  PR_NUMBER=$PR_NUMBER APP_IMAGE=$APP_IMAGE $COMPOSE exec -T -e APP_ENV=test app \
    php bin/console doctrine:database:create --if-not-exists --connection=$em
  PR_NUMBER=$PR_NUMBER APP_IMAGE=$APP_IMAGE $COMPOSE exec -T -e APP_ENV=test app \
    php bin/console doctrine:schema:create --em=$em
  PR_NUMBER=$PR_NUMBER APP_IMAGE=$APP_IMAGE $COMPOSE exec -T -e APP_ENV=test app \
    php bin/console doctrine:fixtures:custom-loader --no-interaction --em=$em --group=$em
done

IP=$(docker inspect pr-$PR_NUMBER-app-1 \
  --format '{{index .NetworkSettings.Networks "nginx-proxy" "IPAddress"}}')

echo ""
echo "✓ Staging ready: http://$IP:8000"
echo "  Run tests:     docker exec -e APP_ENV=test pr-$PR_NUMBER-app-1 vendor/bin/codecept run Functional"
