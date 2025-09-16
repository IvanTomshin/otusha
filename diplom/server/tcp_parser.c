/* gcc tcp_parser.c -o tcp_parser -I/usr/include/postgresql -lhiredis -lpq */

#define _GNU_SOURCE
#include "tcp_lib.c"
#include <fcntl.h>
#include <hiredis/hiredis.h>
#include <netinet/in.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/epoll.h>
#include <sys/socket.h>
#include <time.h>
#include <unistd.h>
#include <libpq-fe.h>

redisContext* redis_ctx = NULL;


int main()
{
    ElapsedTimer timer;
    uint32_t count_pack = 0;
    uint8_t timer_counter = 0;

    redisContext* c = redisConnect("127.0.0.1", 6379);
    if (c == NULL || c->err) {
        printf("Ошибка подключения: %s\n", c ? c->errstr : "неизвестная");
        return 1;
    }


    const char *conninfo = "host=127.0.0.1 port=65432 dbname=otuscola user=postgres password=12345";
    PGconn *conn = PQconnectdb(conninfo);

    if (PQstatus(conn) != CONNECTION_OK) {
        fprintf(stderr, "Connection to database failed: %s\n", PQerrorMessage(conn));
        PQfinish(conn);
        return 1;
    }

    const char *sql = "INSERT INTO data (ts, device_id, p_10, p_11, p_12, p_13) VALUES (now(),$1,$2,$3,$4,$5)";



    startTimer(&timer);

    while (1) {
        if (timer_counter++ == 0) {
            long long et = elapsedMilliseconds(&timer);
            if (et > 10000) {
                printf("                  |   PARSER RPS: %05lld \n", (1000 * count_pack / et));
                startTimer(&timer);
                fflush(stdout);
                count_pack = 0;
            }
        }

        redisReply* r = redisCommand(c, "BRPOP packets 0");
        if (r && r->type == REDIS_REPLY_ARRAY && r->elements == 2) {
            redisReply* packet = r->element[1];

            /*            uint32_t uid = tlv_get_tag_value_uint32(packet);*/

            size_t n;
            TLV_Item* tag_items = tlv_parse_all((const uint8_t*)packet->str, packet->len, &n);

            if (!tag_items) continue;

            const size_t col_count = 5; // например, p_10..p_13
            const char* paramValues[col_count];
            int paramLengths[col_count];
            int paramFormats[col_count];
            char tmp[col_count][32];

            // инициализация NULL
            for (size_t i = 0; i < col_count; i++) {
                paramValues[i] = NULL;
                paramLengths[i] = 0;
                paramFormats[i] = 0;
            }

            for (size_t i = 0; i < n; i++) {
                int idx = -1;
                if (tag_items[i].tag == 0x02) {
                    idx = 0; // device_id
                } else if (tag_items[i].tag >= 0x10 && tag_items[i].tag <= 0x13) {
                    idx = tag_items[i].tag - 0x10 + 1; // p_10..p_13
                }

                if (idx >= 0 && idx < col_count) {
                    // Сохраняем текстовое значение
                    snprintf(tmp[idx], sizeof(tmp[idx]), "%lld", strtoll(tag_items[i].value_hex, NULL, 16));
                    paramValues[idx]  = tmp[idx];
                    paramLengths[idx] = 0;  // не обязательно для текстового
                    paramFormats[idx] = 0;  // текстовый формат
                }
            }


            // Выполнение запроса
                PGresult *res = PQexecParams(conn,
                               sql,
                               col_count,
                               NULL,        // types
                               paramValues,
                               paramLengths,
                               paramFormats,
                               0);          // текстовый формат результата

            for (size_t i = 0; i < n; i++) {
                free(tag_items[i].value_hex);
            }

            count_pack++;
            free(tag_items);

            // вставка в PostgreSQL
        } else if (r && r->type == REDIS_REPLY_ERROR) {
            printf("Ошибка Redis: %s\n", r->str);
        }

        if (r)
            freeReplyObject(r);
    }

    redisFree(c);
    return 0;
}
