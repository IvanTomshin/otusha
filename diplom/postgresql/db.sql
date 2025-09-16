-- public."data" определение

-- Drop table

-- DROP TABLE "data";

CREATE TABLE "data" (
	ts timestamptz NOT NULL,
	device_id int8 NOT NULL,
	p_10 int8 NULL,
	p_11 int8 NULL,
	p_12 int8 NULL,
	p_13 int8 NULL
)
PARTITION BY RANGE (ts);

-- public.data_current определение

-- Drop table

-- DROP TABLE data_current;

CREATE TABLE data_current (
	device_id int8 NOT NULL,
	p_10 int8 NULL,
	p_11 int8 NULL,
	p_12 int8 NULL,
	p_13 int8 NULL
);

-- public.data_current_dt определение

-- Drop table

-- DROP TABLE data_current_dt;

CREATE TABLE data_current_dt (
	device_id int8 NOT NULL,
	ts timestamptz NOT NULL,
	p_10 timestamptz NULL,
	p_11 timestamptz NULL,
	p_12 timestamptz NULL,
	p_13 timestamptz NULL
);



-- public.data_day определение

-- Drop table

-- DROP TABLE data_day;

CREATE TABLE data_day (
	device_id int8 NOT NULL,
	dt date NOT NULL,
	p_11 int8 NULL
);



-- public.data_yesterday определение

-- Drop table

-- DROP TABLE data_yesterday;

CREATE TABLE data_yesterday (
	device_id int8 NOT NULL,
	p_10 int8 NULL,
	p_11 int8 NULL,
	p_12 int8 NULL,
	p_13 int8 NULL
);




-- public.device определение

-- Drop table

-- DROP TABLE device;

CREATE TABLE device (
	id bigserial NOT NULL,
	city int4 DEFAULT 1 NOT NULL,
	"number" int4 DEFAULT 0 NOT NULL,
	del int4 DEFAULT 0 NOT NULL,
	uid int8 DEFAULT 0 NOT NULL
);
CREATE INDEX device_idx ON public.device USING btree (city, number);




-- DROP FUNCTION public.aggregate_and_drop_yesterday_citus();

CREATE OR REPLACE FUNCTION public.aggregate_and_drop_yesterday_citus()
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
    yesterday_date text;
    tomorrow_date text;
    yesterday_val date;
    tomorrow_val date;
    sql_agg text;
    sql_drop text;
    sql_create text;
BEGIN
    yesterday_date := to_char(current_date - interval '1 day', 'YYYY_MM_DD');
    tomorrow_date  := to_char(current_date + interval '1 day', 'YYYY_MM_DD');
    yesterday_val  := current_date - interval '1 day';
    tomorrow_val   := current_date + interval '1 day';

    -- 1) Агрегируем вчерашнюю партицию
    sql_agg := format($sql$
        INSERT INTO data_day (device_id, dt, p_11)
        SELECT device_id,
               %L::date AS dt,
               sum(p_11)
        FROM data
        WHERE ts >= DATE %L AND ts < DATE %L and p_11 is not null
        GROUP BY device_id
    $sql$, yesterday_val, yesterday_val, current_date);

    RAISE NOTICE 'Aggregating: %', sql_agg;
    EXECUTE sql_agg;

    -- 2) Удаляем вчерашнюю партицию
    sql_drop := format('DROP TABLE IF EXISTS data_%s CASCADE;', yesterday_date);
    RAISE NOTICE 'Dropping: %', sql_drop;
    EXECUTE sql_drop;

    -- 3) Создаём завтрашнюю партицию
	sql_create := format($sql$
    CREATE TABLE data_%s 
        PARTITION OF data
        FOR VALUES FROM ('%s') TO ('%s');
	$sql$,
    tomorrow_date,
    tomorrow_val::text,
    (tomorrow_val + 1)::text
	);

    RAISE NOTICE 'Creating: %', sql_create;
    EXECUTE sql_create;

END;
$function$
;

/*
-- создание шардирования таблицы
SELECT create_distributed_table('data', 'device_id');


-- пример создания партиций таблицы на каждый день
CREATE TABLE data_2025_09_13
    PARTITION OF data
    FOR VALUES FROM ('2025-09-13') TO ('2025-09-14');

CREATE TABLE data_2025_09_14
    PARTITION OF data
    FOR VALUES FROM ('2025-09-14') TO ('2025-09-15');

CREATE TABLE data_2025_09_16
    PARTITION OF data
    FOR VALUES FROM ('2025-09-15') TO ('2025-09-16');

CREATE TABLE data_2025_09_17
    PARTITION OF data
    FOR VALUES FROM ('2025-09-16') TO ('2025-09-17');

-- пример удаления партиции за день.
DROP TABLE data_2025_09_16;
*/
