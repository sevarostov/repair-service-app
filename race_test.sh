#!/bin/bash

APP_CONTAINER="php"
BASE_URL="http://localhost"

REQUEST_ID=1
MASTER_COOKIE=""

CONCURRENT_REQUESTS=5

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

login_as_master() {
    echo -e "${YELLOW}Выполняем авторизацию как мастер...${NC}"

    response=$(docker exec -i $APP_CONTAINER curl -s -c /tmp/cookies.txt \
        -X POST "$BASE_URL/login" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        --data-urlencode "email=master@example.com" \
        --data-urlencode "password=master_pwd")

    # Извлекаем cookie сессии
    MASTER_COOKIE=$(docker exec -i $APP_CONTAINER cat /tmp/cookies.txt | grep -oP 'laravel_session\t\K[^\t]+')

    if [ -z "$MASTER_COOKIE" ]; then
        echo -e "${RED}Ошибка авторизации: не удалось получить cookie сессии${NC}"
        exit 1
    fi

    echo -e "${GREEN}Авторизация успешна, получен cookie сессии${NC}"
}

make_take_request() {
    local request_id=$1
    local attempt_num=$2

    http_code=$(docker exec -i $APP_CONTAINER curl -s -o /dev/null -w "%{http_code}" \
        -X POST "$BASE_URL/requests/$request_id/status/update" \
        -H "Cookie: laravel_session=$MASTER_COOKIE" \
        -H "Accept: application/json")

    echo "$http_code"
}

run_race_test() {
    local request_id=$1

    echo -e "${YELLOW}Начинаем тест гонки для запроса #$request_id...${NC}"
    echo -e "Ожидается: один запрос 200 OK, остальные 409 Conflict${NC}"
    echo "---"

    declare -a results

    for i in $(seq 1 $CONCURRENT_REQUESTS); do
        make_take_request $request_id $i &
        pids[$i]=$!
    done

    for i in $(seq 1 $CONCURRENT_REQUESTS); do
        wait ${pids[$i]}
        results[$i]=$?
    done

    success_count=0
    conflict_count=0

    for i in $(seq 1 $CONCURRENT_REQUESTS); do
        result=$(make_take_request $request_id $i)
        results[$i]=$result

        if [ "$result" == "200" ]; then
            ((success_count++))
            echo -e "Запрос #$i → ${GREEN}HTTP 200 | Успешно взят в работу${NC}"
        elif [ "$result" == "409" ]; then
            ((conflict_count++))
            echo -e "Запрос #$i → ${RED}HTTP 409 | Уже взят другим запросом${NC}"
        else
            echo -e "Запрос #$i → ${YELLOW}HTTP $result | Неожиданный статус${NC}"
        fi
    done

    echo "---"
    echo -e "${YELLOW}РЕЗУЛЬТАТЫ ТЕСТА:${NC}"
    echo "Всего запросов: $CONCURRENT_REQUESTS"
    echo -e "Успешных (200): ${GREEN}$success_count${NC}"
    echo -e "Конфликтов (409): ${RED}$conflict_count${NC}"

    if [ $success_count -eq 1 ] && [ $conflict_count -eq $((CONCURRENT_REQUESTS - 1)) ]; then
        echo -e "${GREEN}ТЕСТ ПРОЙДЕН: защита от гонки работает корректно${NC}"
        return 0
    else
        echo -e "${RED}ТЕСТ НЕ ПРОЙДЕН: нарушена логика защиты от гонки${NC}"
        return 1
    fi
}

check_container() {
    if ! docker ps --format '{{.Names}}' | grep -q "^$APP_CONTAINER$"; then
        echo -e "${RED}Контейнер '$APP_CONTAINER' не найден или не запущен${NC}"
        echo "Запустите приложение через docker-compose up"
        exit 1
    fi
}

echo -e "${YELLOW}=== ТЕСТ ЗАЩИТЫ ОТ ГОНКИ (RACE CONDITION) ===${NC}"
echo "Цель: проверить, что только один запрос может взять заявку в работу"
echo ""

check_container

login_as_master

run_race_test $REQUEST_ID

echo ""
echo -e "${GREEN}Тест завершён.${NC}"
