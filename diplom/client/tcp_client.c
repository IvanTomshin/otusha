// tcp_client.c
// gcc tcp_client.c -o tcp_client


#include "tcp_lib.c"
#include <arpa/inet.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <unistd.h>

#define BUFFER_SIZE 1024

int main(int argc, char* argv[])
{
    printf("Использование: %s <IP> <PORT> <Попыток>\n", argv[0]);

    // Значения по умолчанию
    const char* ip_address = "127.0.0.1";
    int ROUTE_COUNTER = 1000;
    int SERVER_PORT = 9000;

    // Если есть аргументы, заменяем значения по умолчанию
    if (argc >= 2 && strlen(argv[1]) > 0) {
        ip_address = argv[1];
    }

    if (argc >= 3 && strlen(argv[1]) > 0) {
        SERVER_PORT = atoi(argv[2]);
    }

    if (argc >= 4) {
        ROUTE_COUNTER = atoi(argv[3]);
    }

    printf("IP: %s\n", ip_address);
    printf("PORT: %d\n", SERVER_PORT);
    printf("Соединений: %d\n", ROUTE_COUNTER);

    srand((unsigned int)time(NULL));
    int sockfd;
    uint32_t TimeStampt = 0x01;
    uint32_t UID = 0;
    uint16_t Pressure = 0;
    uint16_t val = 0;
    uint16_t summ = 0;
    uint32_t Counter1 = 0;
    uint16_t _crc16 = 0;
    uint32_t counter_connect = 1;

    struct sockaddr_in servaddr;
    char buffer[BUFFER_SIZE];

    double start = get_cpu_time();

    while (counter_connect < ROUTE_COUNTER) {

        UID = ((uint32_t)rand() << 16) | (uint32_t)rand();

        // Создаем TCP сокет
        if ((sockfd = socket(AF_INET, SOCK_STREAM, 0)) < 0) {
            perror("socket failed");
            exit(EXIT_FAILURE);
        }

        // Заполняем структуру адреса сервера
        memset(&servaddr, 0, sizeof(servaddr));
        servaddr.sin_family = AF_INET;
        servaddr.sin_port = htons(SERVER_PORT);

        // Преобразуем IP в бинарный вид
        if (inet_pton(AF_INET, ip_address, &servaddr.sin_addr) <= 0) {
            perror("inet_pton failed");
            close(sockfd);
            exit(EXIT_FAILURE);
        }

        // Подключаемся к серверу
        if (connect(sockfd, (struct sockaddr*)&servaddr, sizeof(servaddr)) < 0) {
            perror("connect failed");
            close(sockfd);
            exit(EXIT_FAILURE);
        }

        //    printf("Connected to server %s:%d\n", SERVER_IP, SERVER_PORT);

        uint8_t buffer_out[256];
        size_t pos = 0;

        buffer_out[pos++] = 0x00; // Тэг шаблона
        buffer_out[pos++] = 0; // длина

        // Тэг "TimeStamp"
        buffer_out[pos++] = 0x01; // Tag ID counter
        buffer_out[pos++] = 0x04; // Tag len
        buffer_out[pos++] = (TimeStampt >> 24) & 0xFF;
        buffer_out[pos++] = (TimeStampt >> 16) & 0xFF;
        buffer_out[pos++] = (TimeStampt >> 8) & 0xFF;
        buffer_out[pos++] = TimeStampt & 0xFF;

        buffer_out[pos++] = 0x02; // Tag ID uid
        buffer_out[pos++] = 0x04; // Tag len
        buffer_out[pos++] = (UID >> 24) & 0xFF;
        buffer_out[pos++] = (UID >> 16) & 0xFF;
        buffer_out[pos++] = (UID >> 8) & 0xFF;
        buffer_out[pos++] = UID & 0xFF;

        buffer_out[pos++] = 0x10; // Tag pressure
        buffer_out[pos++] = 0x02; // Tag len
        Pressure = ((uint16_t)rand() << 8) | (uint16_t)rand();
        buffer_out[pos++] = (Pressure >> 8) & 0xFF;
        buffer_out[pos++] = Pressure & 0xFF;

        buffer_out[pos++] = 0x11; // Tag summ
        buffer_out[pos++] = 0x02; // Tag len
        summ = ((uint16_t)rand() << 8) | (uint16_t)rand();
        buffer_out[pos++] = (summ >> 8) & 0xFF;
        buffer_out[pos++] = summ & 0xFF;

        buffer_out[pos++] = 0x12; // Tag value
        buffer_out[pos++] = 0x02; // Tag len
        val = summ / 5;
        buffer_out[pos++] = (val >> 8) & 0xFF;
        buffer_out[pos++] = val & 0xFF;

        buffer_out[pos++] = 0x13; // Tag counter
        buffer_out[pos++] = 0x02; // Tag len
        buffer_out[pos++] = (Counter1 >> 8) & 0xFF;
        buffer_out[pos++] = Counter1 & 0xFF;
        Counter1 += val;

        buffer_out[1] = pos + 2; // длина
        buffer_out[pos++] = 0x7F; // Tag ID CRC
        buffer_out[pos++] = 0x02; // Tag len
        _crc16 = crc16(buffer_out, pos);
        buffer_out[pos++] = (_crc16 >> 8) & 0xFF;
        buffer_out[pos++] = _crc16 & 0xFF;

        // Отправляем сообщение
        send(sockfd, buffer_out, pos, 0);

        /*    printf("Sent: ");
            for (size_t i = 0; i < pos; i++) {
                printf("%02X ", buffer_out[i]);
            }
            printf("\n");
        */

        // Читаем ответ
        int n = read(sockfd, buffer, BUFFER_SIZE - 1);
        if (n > 0) {

            int rc = tlv_check(buffer, (size_t)n);

            if (!rc) {
                uint32_t TimeStampt_ret = tlv_get_tag_value_uint32(buffer + 2);
                if (TimeStampt == TimeStampt_ret)
                    TimeStampt++;
            }
        }

        counter_connect++;
        close(sockfd);
    }
    double end = get_cpu_time();

    printf("Соединений всего: %02d, успешных: %02d, latency: %.4f ms\n", counter_connect, TimeStampt, 1000 * (end - start) / (counter_connect));

    return 0;
}
