// tcp_server_epoll.c

/* gcc tcp_server.c -o tcp_server -lhiredis */

#define _GNU_SOURCE
#include "tcp_lib.c"
#include <errno.h>
#include <fcntl.h>
#include <hiredis/hiredis.h>
#include <netinet/in.h>
#include <signal.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/epoll.h>
#include <sys/socket.h>
#include <unistd.h>

#define PORT 9000
#define MAX_EVENTS 1024
#define BUFFER_SIZE 4096 // побольше, чтобы реже читать



redisContext* redis_ctx = NULL;

int redis_connect(const char* host, int port)
{
    redis_ctx = redisConnect(host, port);
    if (redis_ctx == NULL || redis_ctx->err) {
        if (redis_ctx)
            fprintf(stderr, "Redis error: %s\n", redis_ctx->errstr);
        else
            fprintf(stderr, "Cannot allocate redis context\n");
        return -1;
    }
    return 0;
}

static int make_socket_non_blocking(int fd)
{
    int flags = fcntl(fd, F_GETFL, 0);
    if (flags == -1)
        return -1;
    if (fcntl(fd, F_SETFL, flags | O_NONBLOCK) == -1)
        return -1;
    return 0;
}

static int set_reuseaddr_port(int fd)
{
    int yes = 1;
    if (setsockopt(fd, SOL_SOCKET, SO_REUSEADDR, &yes, sizeof(yes)) == -1)
        return -1;
#ifdef SO_REUSEPORT
    if (setsockopt(fd, SOL_SOCKET, SO_REUSEPORT, &yes, sizeof(yes)) == -1)
        return -1;
#endif
    return 0;
}

int main(int argc, char* argv[])
{

    uint8_t redis_enable = 1;
    printf("Использование: %s -redis \n", argv[0]);

    // Если есть аргументы, заменяем значения по умолчанию
    if (argc >= 2 && strlen(argv[1]) > 0 && strcmp(argv[1], "-redis") == 0) {
        printf("Redis disable !!! \n");
        redis_enable = 0;
    }

    if (redis_connect("127.0.0.1", 6379) != 0) {
        fprintf(stderr, "Cannot connect to Redis\n");
        return 1;
    }

    ElapsedTimer timer;
    uint32_t count_pack = 0;
    uint32_t count_pack_s = 0;
    signal(SIGPIPE, SIG_IGN); // чтобы write() не убивал процесс при разрыве

    int listen_fd = socket(AF_INET, SOCK_STREAM, 0);
    if (listen_fd == -1) {
        perror("socket");
        return 1;
    }

    if (set_reuseaddr_port(listen_fd) == -1) {
        perror("setsockopt");
        return 1;
    }

    struct sockaddr_in server_addr;
    memset(&server_addr, 0, sizeof(server_addr));
    server_addr.sin_family = AF_INET;
    server_addr.sin_addr.s_addr = htonl(INADDR_ANY);
    server_addr.sin_port = htons(PORT);

    if (bind(listen_fd, (struct sockaddr*)&server_addr, sizeof(server_addr)) == -1) {
        perror("bind");
        return 1;
    }

    if (make_socket_non_blocking(listen_fd) == -1) {
        perror("fcntl(O_NONBLOCK)");
        return 1;
    }

    if (listen(listen_fd, SOMAXCONN) == -1) {
        perror("listen");
        return 1;
    }

    int epoll_fd = epoll_create1(0);
    if (epoll_fd == -1) {
        perror("epoll_create1");
        return 1;
    }

    struct epoll_event event;
    event.data.fd = listen_fd;
    event.events = EPOLLIN | EPOLLET;
    if (epoll_ctl(epoll_fd, EPOLL_CTL_ADD, listen_fd, &event) == -1) {
        perror("epoll_ctl listen_fd");
        return 1;
    }

    struct epoll_event* events = calloc(MAX_EVENTS, sizeof(struct epoll_event));
    if (!events) {
        perror("calloc");
        return 1;
    }

    printf("Server listening on port %d...\n", PORT);

    uint8_t buffer[BUFFER_SIZE];
    startTimer(&timer);

    clock_gettime(CLOCK_MONOTONIC, &timer.start);
    uint8_t timer_counter = 0;

    for (;;) {
        int n = epoll_wait(epoll_fd, events, MAX_EVENTS, -1);
        if (n == -1) {
            if (errno == EINTR)
                continue;
            perror("epoll_wait");
            break;
        }

        if (timer_counter++ == 0) {
            long long et = elapsedMilliseconds(&timer);
            if (et > 10000) {
                printf("SERVER RPS: %05lld |", (1000 * count_pack / et));
                if (count_pack != count_pack_s)
                    printf(" (битых пакетов %d) ", (count_pack - count_pack_s));
                printf("\n");

                startTimer(&timer);
                fflush(stdout);
                count_pack = 0;
                count_pack_s = 0;
            }
        }

        for (int i = 0; i < n; i++) {
            int evfd = events[i].data.fd;

            if (evfd == listen_fd) {
                // Edge-triggered accept loop
                for (;;) {
                    struct sockaddr_in in_addr;
                    socklen_t in_len = sizeof(in_addr);
                    int infd = accept(listen_fd, (struct sockaddr*)&in_addr, &in_len);
                    if (infd == -1) {
                        if (errno == EAGAIN || errno == EWOULDBLOCK)
                            break;
                        perror("accept");
                        break;
                    }

                    if (make_socket_non_blocking(infd) == -1) {
                        perror("fcntl client");
                        close(infd);
                        continue;
                    }

                    struct epoll_event ce;
                    ce.data.fd = infd;
                    ce.events = EPOLLIN | EPOLLET;
                    if (epoll_ctl(epoll_fd, EPOLL_CTL_ADD, infd, &ce) == -1) {
                        perror("epoll_ctl client");
                        close(infd);
                        continue;
                    }
                    // printf("New connection (fd=%d)\n", infd);
                }
            } else {
                // Edge-triggered read loop
                for (;;) {
                    ssize_t count = read(evfd, buffer, sizeof(buffer));
                    if (count == -1) {
                        if (errno == EAGAIN || errno == EWOULDBLOCK)
                            break; // всё прочитали
                        perror("read");
                        close(evfd);
                        break;
                    } else if (count == 0) {
                        // клиент закрыл соединение
                        printf("close");
                        close(evfd);
                        break;
                    } else {
                        // Обработка пачки данных (одного сообщения)
                        count_pack++;
                        int rc = tlv_check(buffer, (size_t)count);
                        if (rc < 0) {
                            close(evfd); // политикой можно не закрывать — на ваше усмотрение
                            break;
                        } else {
                            // OK -> ACK

                            /* Redis insert */
                            // бинарный пакет
                            const char* packet = (const char*)(buffer + 8);
                            size_t packet_len = count - (2+6+4); /* минус шаблон, минус номер сообщения минус контрольная сумма) */

                            // команда LPUSH packets <packet>
                            const char* argv[3];
                            size_t argvlen[3];

                            argv[0] = "LPUSH";
                            argvlen[0] = 5;
                            argv[1] = "packets";
                            argvlen[1] = 7;
                            argv[2] = packet;
                            argvlen[2] = packet_len;
                            if (redis_enable == 1) {
                                redisReply* reply = redisCommandArgv(redis_ctx, 3, argv, argvlen);
                                freeReplyObject(reply);
                            }
                            /*Redis end */

                            uint32_t TimeStampt = tlv_get_tag_value_uint32(buffer + 2);
                            uint8_t buffer_out[255];
                            size_t pos = 0;

                            buffer_out[pos++] = 0x00; // Тэг шаблона
                            pos++;
                            // Тэг "TimeStamp"
                            // Тэг "TimeStamp"
                            buffer_out[pos++] = 0x01; // Tag ID counter
                            buffer_out[pos++] = 0x04; // Tag len
                            buffer_out[pos++] = (TimeStampt >> 24) & 0xFF;
                            buffer_out[pos++] = (TimeStampt >> 16) & 0xFF;
                            buffer_out[pos++] = (TimeStampt >> 8) & 0xFF;
                            buffer_out[pos++] = TimeStampt & 0xFF;

                            buffer_out[1] = pos + 2; // длина
                            buffer_out[pos++] = 0x7F; // Tag ID CRC
                            buffer_out[pos++] = 0x02; // Tag len
                            uint16_t _crc16 = crc16(buffer_out, pos);
                            buffer_out[pos++] = (_crc16 >> 8) & 0xFF;
                            buffer_out[pos++] = _crc16 & 0xFF;

                            /*                            printf("Sent: ");
                                                        for (size_t i = 0; i < pos; i++) {
                                                            printf("%02X ", buffer_out[i]);
                                                        }
                                                        printf("\n");
                            */
                            count_pack_s++;

                            write(evfd, buffer_out, pos);
                            close(evfd);
                            break;
                        }
                        // Если возможно приходили несколько сообщений подряд,
                        // вы бы сделали frame decoder и вызывали tlv_check на кадры.
                    }
                }
            }
        }
    }

    free(events);
    close(listen_fd);
    close(epoll_fd);
    if (redis_ctx)
        redisFree(redis_ctx);
    return 0;
}
