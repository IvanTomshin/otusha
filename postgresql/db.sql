--
-- PostgreSQL database dump
--

-- Dumped from database version 16.4 (Debian 16.4-1.pgdg120+2)
-- Dumped by pg_dump version 17.0

-- Started on 2025-03-24 15:55:42

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 5 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: pg_database_owner
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO pg_database_owner;

--
-- TOC entry 3415 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: pg_database_owner
--

COMMENT ON SCHEMA public IS 'standard public schema';


--
-- TOC entry 235 (class 1255 OID 236502)
-- Name: tr_users_insert(); Type: FUNCTION; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE FUNCTION public.tr_users_insert() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    BEGIN

    IF TG_OP = 'INSERT' THEN
	INSERT INTO users_data (id) values (NEW.id);
    END IF;

 RETURN NEW;

    END;
$$;


ALTER FUNCTION public.tr_users_insert() OWNER TO c103814_otusha_all_exclusive_ru;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 220 (class 1259 OID 108457)
-- Name: r_citys; Type: TABLE; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE TABLE public.r_citys (
    id integer NOT NULL,
    city_name character varying(255)
);


ALTER TABLE public.r_citys OWNER TO c103814_otusha_all_exclusive_ru;

--
-- TOC entry 219 (class 1259 OID 108456)
-- Name: citys_id_seq; Type: SEQUENCE; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE SEQUENCE public.citys_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.citys_id_seq OWNER TO c103814_otusha_all_exclusive_ru;

--
-- TOC entry 3416 (class 0 OID 0)
-- Dependencies: 219
-- Name: citys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER SEQUENCE public.citys_id_seq OWNED BY public.r_citys.id;


--
-- TOC entry 222 (class 1259 OID 108814)
-- Name: r_interests; Type: TABLE; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE TABLE public.r_interests (
    id integer NOT NULL,
    interest_name character varying(255) NOT NULL
);


ALTER TABLE public.r_interests OWNER TO c103814_otusha_all_exclusive_ru;

--
-- TOC entry 3417 (class 0 OID 0)
-- Dependencies: 222
-- Name: TABLE r_interests; Type: COMMENT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COMMENT ON TABLE public.r_interests IS 'справочник интересов';


--
-- TOC entry 221 (class 1259 OID 108813)
-- Name: r_interest_id_seq; Type: SEQUENCE; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE SEQUENCE public.r_interest_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.r_interest_id_seq OWNER TO c103814_otusha_all_exclusive_ru;

--
-- TOC entry 3418 (class 0 OID 0)
-- Dependencies: 221
-- Name: r_interest_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER SEQUENCE public.r_interest_id_seq OWNED BY public.r_interests.id;


--
-- TOC entry 217 (class 1259 OID 99149)
-- Name: users; Type: TABLE; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    deleted smallint DEFAULT '0'::smallint NOT NULL,
    login character varying(64) NOT NULL,
    password bytea NOT NULL,
    reply smallint DEFAULT 0 NOT NULL,
    token uuid DEFAULT public.uuid_generate_v4() NOT NULL
);


ALTER TABLE public.users OWNER TO c103814_otusha_all_exclusive_ru;

--
-- TOC entry 3419 (class 0 OID 0)
-- Dependencies: 217
-- Name: COLUMN users.deleted; Type: COMMENT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COMMENT ON COLUMN public.users.deleted IS 'Признак удален';


--
-- TOC entry 3420 (class 0 OID 0)
-- Dependencies: 217
-- Name: COLUMN users.login; Type: COMMENT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COMMENT ON COLUMN public.users.login IS 'Логин';


--
-- TOC entry 3421 (class 0 OID 0)
-- Dependencies: 217
-- Name: COLUMN users.password; Type: COMMENT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COMMENT ON COLUMN public.users.password IS 'Хэш пароля';


--
-- TOC entry 3422 (class 0 OID 0)
-- Dependencies: 217
-- Name: COLUMN users.reply; Type: COMMENT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COMMENT ON COLUMN public.users.reply IS 'Номер сервера БД где находятся данные';


--
-- TOC entry 218 (class 1259 OID 99249)
-- Name: users_data; Type: TABLE; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE TABLE public.users_data (
    middle_name character varying(255),
    id bigint NOT NULL,
    first_name character varying(255),
    second_name character varying(255),
    sex smallint DEFAULT 0,
    birth_day date,
    city_id integer
);


ALTER TABLE public.users_data OWNER TO c103814_otusha_all_exclusive_ru;

--
-- TOC entry 216 (class 1259 OID 99148)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO c103814_otusha_all_exclusive_ru;

--
-- TOC entry 3423 (class 0 OID 0)
-- Dependencies: 216
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 224 (class 1259 OID 108842)
-- Name: users_ref_interests; Type: TABLE; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE TABLE public.users_ref_interests (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    r_interests_id integer NOT NULL
);


ALTER TABLE public.users_ref_interests OWNER TO c103814_otusha_all_exclusive_ru;

--
-- TOC entry 223 (class 1259 OID 108841)
-- Name: users_ref_interests_id_seq; Type: SEQUENCE; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE SEQUENCE public.users_ref_interests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_ref_interests_id_seq OWNER TO c103814_otusha_all_exclusive_ru;

--
-- TOC entry 3424 (class 0 OID 0)
-- Dependencies: 223
-- Name: users_ref_interests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER SEQUENCE public.users_ref_interests_id_seq OWNED BY public.users_ref_interests.id;


--
-- TOC entry 3241 (class 2604 OID 108460)
-- Name: r_citys id; Type: DEFAULT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER TABLE ONLY public.r_citys ALTER COLUMN id SET DEFAULT nextval('public.citys_id_seq'::regclass);


--
-- TOC entry 3242 (class 2604 OID 108817)
-- Name: r_interests id; Type: DEFAULT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER TABLE ONLY public.r_interests ALTER COLUMN id SET DEFAULT nextval('public.r_interest_id_seq'::regclass);


--
-- TOC entry 3236 (class 2604 OID 99217)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 3243 (class 2604 OID 108845)
-- Name: users_ref_interests id; Type: DEFAULT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER TABLE ONLY public.users_ref_interests ALTER COLUMN id SET DEFAULT nextval('public.users_ref_interests_id_seq'::regclass);


--
-- TOC entry 3405 (class 0 OID 108457)
-- Dependencies: 220
-- Data for Name: r_citys; Type: TABLE DATA; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COPY public.r_citys (id, city_name) FROM stdin;
1	Москва
2	Подлипки дачные
3	Раменское
4	Балашиха
5	Рыбинск
6	Екатеринбург
7	Тюмень
8	Хабаровск
9	Санкт Петербург
10	Подольск
\.


--
-- TOC entry 3407 (class 0 OID 108814)
-- Dependencies: 222
-- Data for Name: r_interests; Type: TABLE DATA; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COPY public.r_interests (id, interest_name) FROM stdin;
1	php
2	java
3	театрал
4	приготовление супов
5	коллекционер женщин
6	охота
7	цветы
8	насекомые
9	пресса
10	звезды
11	политика
12	СССР
\.


--
-- TOC entry 3402 (class 0 OID 99149)
-- Dependencies: 217
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COPY public.users (id, deleted, login, password, reply, token) FROM stdin;
2	0	ivanius	\\xff	0	6e60b78b-fbf8-4653-b09d-c574f11828b8
3	0	vladimir	\\x3937313666616131353239376264356535636432623733363437303833376465	0	d8ef35bd-64ed-46f9-9093-73c88afcf3b6
4	0	Sabryna_Lemke21	\\x3037373166633236366661666536323065303130653631656535303133336530	0	1295a41d-1647-4db6-84e8-90c84a64135b
5	0	Deontae_Rempel86	\\x6339393932343033646263343433633564396330626231313266633661333333	0	3cc999c4-5286-4352-bf99-389da488b638
6	0	Fernando45	\\x6330363832346262646530326163656238373236623761646137313933383234	0	49138b8e-044e-4fe7-b395-3f047552a49e
7	0	Noe_Padberg	\\x3539653830313763656330626636646463363261343364356164353439353764	0	a0d9655c-97f6-4276-990d-b05882f3d656
8	0	Trent_Ward	\\x3136373039643035333765383931316437613161393463616461643134336265	0	5b2a1b73-4365-4a67-9a57-2e54bb19bfa0
9	0	Micah.Upton2	\\x6434333632383734633366306362613132356266373033303133633637663465	0	76236e11-b44e-463f-893c-efb237c40149
10	0	Boris_Abshire	\\x3864313063303232633534633861346331326431663930643739633062326466	0	63067402-30b6-4a27-9774-44ef73b1d8ed
11	0	Jordy_Homenick25	\\x3230363238616163663037653837396430623265393738643830623939313761	0	fcd91b91-6ef5-44f1-a99e-69ee67939096
12	0	Emory_Hickle	\\x3734613461363238656262326532316664363166373936376639303966316136	0	9d6c0a72-d847-436a-8799-97fcebd398b8
13	0	Gunnar_Conn67	\\x3963353437323462346566316364626664656662393463643134303062353434	0	24a67793-a7a2-4ca3-8528-30715b7b9a1f
14	0	Maribel_Gusikowski	\\x6666613030626337623832666530666637313566393030396136613962336665	0	b137faae-0778-47dd-bc7d-4bc3796ba0ce
15	0	Mylene.Padberg	\\x6437313761613338393235646338376232313065366638623262663031636561	0	ba5330fa-5644-4f36-8b54-b2662fd47303
16	0	Sidney.Turcotte	\\x3861656461366632323330623366393066376430396233663834633366323130	0	65713b7e-6a90-4077-9575-420977e71d79
17	0	Ashly_Wilderman43	\\x3561333939343235316632643830306132356565643437646435393865623434	0	8cff4ad4-5557-4bbf-96b6-2aabccacf1c1
18	0	Jolie_Bailey12	\\x3766333832653935393533623534343662386637646466346437343532633363	0	c26f826d-509c-45fa-9f8c-954d94eea044
19	0	Georgiana.Huel	\\x3037643931356332666431623337393132393166633136323137613265366465	0	ec8285cd-13cb-4add-976a-ca05f263396d
20	0	Zena.McClure37	\\x6461393236646664623635343830366162333835386263393232396135353765	0	152a5d13-e1cc-40fc-8f62-7f8e047cb2e4
21	0	Lauryn57	\\x3630663730363035626139663236396165653635316434346364373166623061	0	b9ba1afe-f05b-4035-a5cc-fc9d93d4b44f
22	0	Savanah_Williamson6	\\x3665613533636162626232356137363963336137633063643962663161386663	0	ee14b2f8-eefb-4cf6-91d8-b153ddc50c03
23	0	Alexys65	\\x3432363634643535613365366434346463323965396537313233666139333530	0	634b4d20-a814-475d-838d-525cc43448d2
28	0	Ora.Strosin67	\\x3161306165663534636332613265393666396532313832336335343436336231	0	3c0daf79-39b0-4ace-a729-7bf17ef77b6e
29	0	Carolina.Lueilwitz	\\x3437346235306635663562613564313437356534353839393364313433663561	0	02b5cad9-41b0-4ab1-b1df-8942d8acbded
30	0	Jeff43	\\x6466393031373938386462663930326564643833353161643965393239363238	0	60a4e9b0-6658-4edf-8f2e-8a18da340bfd
31	0	Jared_Johnston33	\\x6639316666366235343263653439656334396437373663363065323365333166	0	e01607ce-cd36-423e-bbcd-d37d9542e6f5
32	0	Erin.Zemlak	\\x6330323731393037636232343062303537336139616562663861646664396264	0	bd85edc3-6ac4-4b98-ae26-f0d7f77fa326
33	0	Bulah.Hickle31	\\x3236306166323634353764313535616566666435646639336230373338376438	0	d91a8e35-e414-42bf-9fb3-8730f861f19b
34	0	Elias5	\\x6565326161656662303664633335333331353938363762666666393833333263	0	dab9cc4d-8c47-44c4-bc59-7d7fd20c614a
35	0	Bailey76	\\x6665646535303830363035396665646434316433353035313162386133656164	0	1d009710-94c3-4cfa-9401-f0193602d229
36	0	Dina.Nolan67	\\x6266633164363261383033613464663031633633373664306634333234623731	0	9a8cc51c-dd8c-4201-a6e2-f78442129007
37	0	Neha.Brakus15	\\x3061333038346136633365313236376539343933363638373334376461336665	0	729e58bf-d478-4c1a-a7d5-c158fff092d2
38	0	Monroe_Hermann99	\\x3838663430316564353762376464396239393763373861633734613734376631	0	38b64155-31d1-4b8b-a0e5-b44960a03c65
39	0	Heaven_Pfannerstill	\\x3631393466616238626233333064353734616165626539646162646261343335	0	d0a12e6b-028d-4142-b50a-79e0519b3bca
\.


--
-- TOC entry 3403 (class 0 OID 99249)
-- Dependencies: 218
-- Data for Name: users_data; Type: TABLE DATA; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COPY public.users_data (middle_name, id, first_name, second_name, sex, birth_day, city_id) FROM stdin;
Тронин	2	Иван	Михайлович	1	1976-09-30	6
\N	28	\N	\N	0	\N	\N
\N	29	\N	\N	0	\N	\N
\N	30	\N	\N	0	\N	\N
\N	31	\N	\N	0	\N	\N
\N	32	\N	\N	0	\N	\N
Mohammed.Bailey34	33	Etha.Boyer	Emerald_Sawayn	1	2024-06-01	5
Verona.Spencer97	34	Ines33	Vincenza.Nicolas10	1	2025-01-08	5
Rowena.Monahan	35	Lula87	Hellen.Monahan	1	2024-04-27	1
Cristobal_Durgan51	36	Josefa.Konopelski47	Joesph67	0	2024-12-16	3
Uriah.Kunde75	37	Roberto.Quitzon54	Antonina76	1	2025-03-07	5
\N	4	\N	\N	0	\N	\N
\N	5	\N	\N	0	\N	\N
\N	6	\N	\N	0	\N	\N
\N	7	\N	\N	0	\N	\N
\N	8	\N	\N	0	\N	\N
\N	9	\N	\N	0	\N	\N
\N	10	\N	\N	0	\N	\N
\N	11	\N	\N	0	\N	\N
\N	12	\N	\N	0	\N	\N
\N	13	\N	\N	0	\N	\N
\N	14	\N	\N	0	\N	\N
\N	15	\N	\N	0	\N	\N
\N	16	\N	\N	0	\N	\N
\N	17	\N	\N	0	\N	\N
\N	18	\N	\N	0	\N	\N
\N	19	\N	\N	0	\N	\N
\N	20	\N	\N	0	\N	\N
\N	21	\N	\N	0	\N	\N
\N	22	\N	\N	0	\N	\N
\N	23	\N	\N	0	\N	\N
Jadyn_Anderson	3	Ubaldo71	Zetta_Fadel	0	2025-01-11	1
Dorthy.Pollich	38	Augustus.Langosh48	Lavada.Rau41	0	2025-01-28	6
Raheem89	39	Lauryn.Roberts98	Kaycee69	0	2024-12-14	1
\.


--
-- TOC entry 3409 (class 0 OID 108842)
-- Dependencies: 224
-- Data for Name: users_ref_interests; Type: TABLE DATA; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

COPY public.users_ref_interests (id, user_id, r_interests_id) FROM stdin;
2	2	1
3	2	12
\.


--
-- TOC entry 3425 (class 0 OID 0)
-- Dependencies: 219
-- Name: citys_id_seq; Type: SEQUENCE SET; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

SELECT pg_catalog.setval('public.citys_id_seq', 10, true);


--
-- TOC entry 3426 (class 0 OID 0)
-- Dependencies: 221
-- Name: r_interest_id_seq; Type: SEQUENCE SET; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

SELECT pg_catalog.setval('public.r_interest_id_seq', 12, true);


--
-- TOC entry 3427 (class 0 OID 0)
-- Dependencies: 216
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

SELECT pg_catalog.setval('public.users_id_seq', 39, true);


--
-- TOC entry 3428 (class 0 OID 0)
-- Dependencies: 223
-- Name: users_ref_interests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

SELECT pg_catalog.setval('public.users_ref_interests_id_seq', 3, true);


--
-- TOC entry 3246 (class 2606 OID 99216)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 3247 (class 1259 OID 108766)
-- Name: citys_id_idx; Type: INDEX; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE UNIQUE INDEX citys_id_idx ON public.r_citys USING btree (id);


--
-- TOC entry 3248 (class 1259 OID 108829)
-- Name: r_interest_id_idx; Type: INDEX; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE UNIQUE INDEX r_interest_id_idx ON public.r_interests USING btree (id);


--
-- TOC entry 3249 (class 1259 OID 108830)
-- Name: r_interest_interest_name_idx; Type: INDEX; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE UNIQUE INDEX r_interest_interest_name_idx ON public.r_interests USING btree (interest_name);


--
-- TOC entry 3244 (class 1259 OID 108465)
-- Name: users_deleted_idx; Type: INDEX; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE INDEX users_deleted_idx ON public.users USING btree (deleted, login, password);


--
-- TOC entry 3250 (class 1259 OID 108864)
-- Name: users_ref_interests_id_idx; Type: INDEX; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE UNIQUE INDEX users_ref_interests_id_idx ON public.users_ref_interests USING btree (id);


--
-- TOC entry 3251 (class 1259 OID 108865)
-- Name: users_ref_interests_r_interests_id_idx; Type: INDEX; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE INDEX users_ref_interests_r_interests_id_idx ON public.users_ref_interests USING btree (r_interests_id);


--
-- TOC entry 3252 (class 1259 OID 108866)
-- Name: users_ref_interests_user_id_idx; Type: INDEX; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE INDEX users_ref_interests_user_id_idx ON public.users_ref_interests USING btree (user_id);


--
-- TOC entry 3257 (class 2620 OID 243636)
-- Name: users trigger_users; Type: TRIGGER; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

CREATE TRIGGER trigger_users AFTER INSERT ON public.users FOR EACH ROW EXECUTE FUNCTION public.tr_users_insert();


--
-- TOC entry 3253 (class 2606 OID 108774)
-- Name: users_data users_data_citys_fk; Type: FK CONSTRAINT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER TABLE ONLY public.users_data
    ADD CONSTRAINT users_data_citys_fk FOREIGN KEY (city_id) REFERENCES public.r_citys(id);


--
-- TOC entry 3254 (class 2606 OID 108722)
-- Name: users_data users_data_users_fk; Type: FK CONSTRAINT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER TABLE ONLY public.users_data
    ADD CONSTRAINT users_data_users_fk FOREIGN KEY (id) REFERENCES public.users(id);


--
-- TOC entry 3255 (class 2606 OID 108947)
-- Name: users_ref_interests users_ref_interests_r_interests_fk; Type: FK CONSTRAINT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER TABLE ONLY public.users_ref_interests
    ADD CONSTRAINT users_ref_interests_r_interests_fk FOREIGN KEY (r_interests_id) REFERENCES public.r_interests(id);


--
-- TOC entry 3256 (class 2606 OID 108942)
-- Name: users_ref_interests users_ref_interests_users_fk; Type: FK CONSTRAINT; Schema: public; Owner: c103814_otusha_all_exclusive_ru
--

ALTER TABLE ONLY public.users_ref_interests
    ADD CONSTRAINT users_ref_interests_users_fk FOREIGN KEY (user_id) REFERENCES public.users(id);


-- Completed on 2025-03-24 15:55:42

--
-- PostgreSQL database dump complete
--


