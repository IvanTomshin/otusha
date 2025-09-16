#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <sys/resource.h>
#include <sys/time.h>
#include <time.h>

typedef struct {
    struct timespec start;
} ElapsedTimer;

void startTimer(ElapsedTimer* t)
{
    clock_gettime(CLOCK_MONOTONIC, &t->start);
}

long long elapsedMilliseconds(ElapsedTimer* t)
{
    struct timespec now;
    clock_gettime(CLOCK_MONOTONIC, &now);

    long long sec_diff = now.tv_sec - t->start.tv_sec;
    long long nsec_diff = now.tv_nsec - t->start.tv_nsec;

    return sec_diff * 1000LL + nsec_diff / 1000000LL;
}







static double get_cpu_time()
{
    struct rusage usage;
    getrusage(RUSAGE_SELF, &usage);
    double user_time = usage.ru_utime.tv_sec + usage.ru_utime.tv_usec / 1e6;
    double sys_time = usage.ru_stime.tv_sec + usage.ru_stime.tv_usec / 1e6;
    return user_time + sys_time;
}

static uint16_t crc16(const uint8_t* data, size_t len)
{
    //    printf("\n CRC ");

    uint16_t crc = 0xFFFF;
    for (size_t pos = 0; pos < len; pos++) {
        crc += (uint8_t)data[pos];
        //        printf("%02X ", (uint8_t)data[pos]);
    }
    //    printf("\n");
    return crc;
}

typedef struct {
    uint8_t tag;
    char *value_hex;  // HEX-представление значения
} TLV_Item;

TLV_Item* tlv_parse_all(const uint8_t *buf, size_t len, size_t *out_count) {
    size_t pos = 0;
    size_t capacity = 8;
    size_t count = 0;
    TLV_Item *items = malloc(sizeof(TLV_Item) * capacity);
    if (!items) return NULL;

    while (pos + 2 <= len) { // минимум 2 байта для заголовка
        uint8_t tag = buf[pos];
        uint8_t l = buf[pos + 1];
        pos += 2;

        if (pos + l > len) {
            fprintf(stderr, "TLV parse error: length overflow\n");
            break;
        }

        // Расширяем массив при необходимости
        if (count >= capacity) {
            capacity *= 2;
            TLV_Item *tmp = realloc(items, sizeof(TLV_Item) * capacity);
            if (!tmp) break;
            items = tmp;
        }

        // Конвертируем значение в HEX строку
        char *out = malloc(l * 2 + 1);
        if (!out) break;
        for (int j = 0; j < l; j++) {
            sprintf(out + j*2, "%02X", buf[pos + j]);
        }
        out[l*2] = '\0';

        items[count].tag = tag;
        items[count].value_hex = out;
        count++;

        pos += l;
    }

    *out_count = count;
    return items;
}


char* tlv_get_tag_value(const uint8_t* buf, size_t len)
{
    size_t pos = 0;
    uint8_t tag = buf[pos];
    uint8_t l = buf[pos + 1];
    pos += 2;

    if (pos + l > len) {
        fprintf(stderr, "TLV parse error: length overflow\n");
        return NULL;
    }

    // Конвертируем в HEX строку
    char* out = malloc(l * 2 + 1);
    if (!out)
        return NULL;
    for (int j = 0; j < l; j++) {
        sprintf(out + j * 2, "%02X", buf[pos + j]);
    }
    out[l * 2] = '\0';
    //        printf("Tag=0x%02X Len=%u -> [%s]\n", tag, l, out);
    return out;

    pos += l; // перейти к следующему тегу
    return NULL;
}

uint32_t tlv_get_tag_value_uint32(const uint8_t* buf)
{
    uint8_t tag = buf[0];
    uint8_t l = buf[1];

    uint32_t value = 0;
    for (int j = 0; j < l && j < 4; j++) { // читаем максимум 4 байта в uint32_t
        value = (value << 8) | buf[2 + j]; // big-endian
    }

    // printf("Tag=0x%02X Len=%u -> 0x%X\n", tag, l, value);

    return value;
}

/*
 * Проверка TLV-сообщения:
 * [1 байт type][2 байта length (big-endian)][length байт value][2 байта CRC16 (LE)]
 * Возвращает 0 если OK, иначе -1..-N
 */
static int tlv_check(const uint8_t* msg, size_t len)
{
    if (len < 1 + 1 + 6 + 4)
        return -1; // слишком коротко

    uint8_t type = msg[0];
    uint8_t value_len = (uint8_t)msg[1];

    size_t expected = 1 /*type*/ + 1 /*len*/ + value_len /*crc*/;
    if (expected != len)
        return -2; // длина не совпала

    uint16_t recv_crc = (uint16_t)msg[len - 1] | ((uint16_t)msg[len - 2] << 8); // LE
    uint16_t calc_crc = crc16(msg, len - 2);

    if (recv_crc != calc_crc)
        return -3; // CRC ошибка

    (void)type; // если нужно - используйте type
    return 0;
}
