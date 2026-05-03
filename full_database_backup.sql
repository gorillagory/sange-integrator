--
-- PostgreSQL database dump
--

\restrict nukMbyGZ0BemdoUz5V3BpYg9RLXzoPqgbEBkg7xuTxS1FQbQ63IVlzB6T5netUh

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO sail;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO sail;

--
-- Name: clients; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.clients (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    registration_number character varying(255),
    logo_path character varying(255),
    hq_contact_person character varying(255),
    hq_contact_email character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.clients OWNER TO sail;

--
-- Name: clients_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.clients_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.clients_id_seq OWNER TO sail;

--
-- Name: clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.clients_id_seq OWNED BY public.clients.id;


--
-- Name: companies; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.companies (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    subdomain character varying(255) NOT NULL,
    db_name character varying(255) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    industry character varying(255) DEFAULT 'general'::character varying NOT NULL,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.companies OWNER TO sail;

--
-- Name: companies_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.companies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.companies_id_seq OWNER TO sail;

--
-- Name: companies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.companies_id_seq OWNED BY public.companies.id;


--
-- Name: company_user; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.company_user (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    company_id bigint NOT NULL,
    role character varying(255) DEFAULT 'employee'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.company_user OWNER TO sail;

--
-- Name: company_user_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.company_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.company_user_id_seq OWNER TO sail;

--
-- Name: company_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.company_user_id_seq OWNED BY public.company_user.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO sail;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO sail;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO sail;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO sail;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO sail;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO sail;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO sail;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO sail;

--
-- Name: service_schemas; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.service_schemas (
    id bigint NOT NULL,
    industry character varying(255) NOT NULL,
    service_type character varying(255) NOT NULL,
    display_name character varying(255) NOT NULL,
    schema_payload jsonb NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.service_schemas OWNER TO sail;

--
-- Name: service_schemas_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.service_schemas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.service_schemas_id_seq OWNER TO sail;

--
-- Name: service_schemas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.service_schemas_id_seq OWNED BY public.service_schemas.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO sail;

--
-- Name: users; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO sail;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO sail;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: clients id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.clients ALTER COLUMN id SET DEFAULT nextval('public.clients_id_seq'::regclass);


--
-- Name: companies id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.companies ALTER COLUMN id SET DEFAULT nextval('public.companies_id_seq'::regclass);


--
-- Name: company_user id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.company_user ALTER COLUMN id SET DEFAULT nextval('public.company_user_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: service_schemas id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.service_schemas ALTER COLUMN id SET DEFAULT nextval('public.service_schemas_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: clients; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.clients (id, name, registration_number, logo_path, hq_contact_person, hq_contact_email, created_at, updated_at) FROM stdin;
1	Petroliam Nasional Berhad	123552-X	\N	Finance	finance@petronas.com	2026-04-25 14:55:13	2026-04-25 14:55:13
2	Pengerang Refinery And Chemical Sdn Bhd	251553-5	\N	Finance Department	finance@petronas.com	2026-04-25 15:03:06	2026-04-25 15:03:06
\.


--
-- Data for Name: companies; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.companies (id, name, subdomain, db_name, is_active, created_at, updated_at, industry, deleted_at) FROM stdin;
1	Bayam Travel	bt	sange_tenant_bt	t	2026-04-24 11:50:29	2026-04-24 11:50:29	travel	\N
\.


--
-- Data for Name: company_user; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.company_user (id, user_id, company_id, role, created_at, updated_at) FROM stdin;
1	1	1	Super Administrator	2026-04-24 12:24:53	2026-04-24 12:24:53
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
6	0001_01_01_000002_create_jobs_table	2
7	2026_04_16_203910_create_companies_table	2
8	2026_04_17_110028_create_company_user_table	3
9	2026_04_24_131732_create_service_schemas_table	4
10	2026_04_25_141523_create_global_clients_table	5
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: service_schemas; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.service_schemas (id, industry, service_type, display_name, schema_payload, created_at, updated_at) FROM stdin;
2	travel	flight_tickets	Flight Tickets	{"fields": [{"key": "booking_e_ticket", "type": "string", "label": "Booking/E-ticket", "order": 0, "rules": ["required"], "is_array": false, "grid_span": 1, "ui_component": "text_input", "text_transform": "uppercase"}, {"key": "class", "type": "string", "label": "Class", "order": 1, "rules": ["required"], "is_array": false, "grid_span": 1, "ui_component": "text_input", "text_transform": "uppercase"}, {"key": "date", "type": "date", "label": "Date", "order": 2, "rules": ["required"], "is_array": false, "grid_span": 1, "ui_component": "date"}, {"key": "route", "type": "string", "label": "Route", "order": 3, "rules": ["required"], "is_array": false, "grid_span": 1, "ui_component": "typeahead", "text_transform": "uppercase"}, {"key": "departure", "type": "time", "label": "Departure", "order": 4, "rules": [], "is_array": false, "grid_span": 1, "ui_component": "time"}, {"key": "arrival", "type": "string", "label": "Arrival", "order": 5, "rules": [], "is_array": false, "grid_span": 1, "ui_component": "text_input"}, {"key": "passenger_name", "type": "string", "label": "Passenger Name", "order": 6, "rules": ["required"], "is_array": true, "grid_span": 2, "ui_component": "text_input", "text_transform": "capitalize"}, {"key": "remarks", "type": "string", "label": "Remarks", "order": 7, "rules": [], "is_array": false, "grid_span": 1, "ui_component": "textarea"}, {"key": "attachment", "type": "file", "label": "Attachment", "order": 8, "rules": [], "is_array": true, "grid_span": 2, "file_options": {"allowed_types": "*"}, "ui_component": "file"}], "pricing_units": ["ticket", "booking", "pax"], "document_output": "<h3><strong><u>Booking Details(</u></strong><span id=\\"booking_e_ticket\\" data-variable=\\"booking_e_ticket\\" class=\\"variable-pill\\">{{ booking_e_ticket }}</span> <strong><u>)</u></strong></h3><p><u>Date</u></p><p><span id=\\"date\\" data-variable=\\"date\\" class=\\"variable-pill\\">{{ date }}</span> :<span id=\\"route\\" data-variable=\\"route\\" class=\\"variable-pill\\">{{ route }}</span></p><p></p><p><u>Departure/Arrival</u></p><p><span id=\\"departure\\" data-variable=\\"departure\\" class=\\"variable-pill\\">{{ departure }}</span> -<span id=\\"arrival\\" data-variable=\\"arrival\\" class=\\"variable-pill\\">{{ arrival }}</span></p><p></p><p><u>Class</u></p><p><span id=\\"class\\" data-variable=\\"class\\" class=\\"variable-pill\\">{{ class }}</span></p><p></p><p><u>Passengers</u></p><p><span id=\\"passenger_name\\" data-variable=\\"passenger_name\\" class=\\"variable-pill\\">{{ passenger_name }}</span></p>"}	2026-04-29 10:50:52	2026-05-01 18:09:14
3	travel	ground_transportation	Ground Transportation	{"fields": [{"key": "guest_name", "type": "string", "label": "Guest Name", "order": 0, "rules": ["required"], "is_array": true, "grid_span": 2, "ui_component": "text_input"}, {"key": "date", "type": "date", "label": "Date", "order": 1, "rules": ["required"], "is_array": false, "grid_span": 1, "ui_component": "date"}, {"key": "flight_manifest", "type": "string", "label": "Flight / Manifest", "order": 2, "rules": [], "is_array": false, "grid_span": 1, "ui_component": "text_input"}, {"key": "origin", "type": "string", "label": "Origin", "order": 3, "rules": [], "is_array": false, "grid_span": 2, "ui_component": "text_input"}, {"key": "destination", "type": "string", "label": "Destination", "order": 4, "rules": [], "is_array": false, "grid_span": 2, "ui_component": "text_input"}, {"key": "time", "type": "time", "label": "Time", "order": 5, "rules": [], "is_array": false, "grid_span": 2, "ui_component": "time"}, {"key": "attachment", "type": "file", "label": "Attachment", "order": 6, "rules": [], "is_array": true, "grid_span": 2, "file_options": {"max_count": 1, "max_size_mb": 5, "allowed_types": "*", "enable_preview": false}, "ui_component": "file"}, {"key": "remarks", "type": "string", "label": "Remarks", "order": 7, "rules": [], "is_array": false, "grid_span": 2, "ui_component": "textarea"}], "pricing_units": ["fare", "pax", "unit"], "document_output": "<h3><u>Ground Transportation</u></h3><p><span id=\\"date\\" data-variable=\\"date\\" class=\\"variable-pill\\">{{ date }}</span> (<span id=\\"time\\" data-variable=\\"time\\" class=\\"variable-pill\\">{{ time }}</span> )</p><p><span id=\\"origin\\" data-variable=\\"origin\\" class=\\"variable-pill\\">{{ origin }}</span> --&gt;<span id=\\"destination\\" data-variable=\\"destination\\" class=\\"variable-pill\\">{{ destination }}</span> </p><p><strong><u>Guest Name</u></strong></p><p><span id=\\"guest_name\\" data-variable=\\"guest_name\\" class=\\"variable-pill\\">{{ guest_name }}</span> </p>"}	2026-05-01 19:01:47	2026-05-01 19:01:47
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
aQyqGMF0TmyYOesg4rpwxX94WCaCQuqAXcWkXBGR	1	172.22.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJvWWdIR2c2bzkyMHY1QUhMMkVEZHJYbGF3M2ZJREE4RU1CM3ZhOWlRIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2J0LmJheWFtLnRlc3Q6ODAwMFwvYm9va2luZ3NcL2NyZWF0ZSIsInJvdXRlIjoiYm9va2luZ3MuY3JlYXRlIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjF9	1777696820
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
1	Genesis Admin	admin@bayam.test	\N	$2y$12$G70bgoYdYhMOIIYCf0SwXe.vi7CTTs75QcAfYi4X4LqL75pEvWKgW	\N	2026-04-17 10:56:14	2026-04-17 10:56:14
\.


--
-- Name: clients_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.clients_id_seq', 2, true);


--
-- Name: companies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.companies_id_seq', 1, true);


--
-- Name: company_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.company_user_id_seq', 1, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.migrations_id_seq', 10, true);


--
-- Name: service_schemas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.service_schemas_id_seq', 3, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: clients clients_name_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_name_unique UNIQUE (name);


--
-- Name: clients clients_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_pkey PRIMARY KEY (id);


--
-- Name: companies companies_db_name_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_db_name_unique UNIQUE (db_name);


--
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- Name: companies companies_subdomain_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_subdomain_unique UNIQUE (subdomain);


--
-- Name: company_user company_user_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.company_user
    ADD CONSTRAINT company_user_pkey PRIMARY KEY (id);


--
-- Name: company_user company_user_user_id_company_id_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.company_user
    ADD CONSTRAINT company_user_user_id_company_id_unique UNIQUE (user_id, company_id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: service_schemas service_schemas_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.service_schemas
    ADD CONSTRAINT service_schemas_pkey PRIMARY KEY (id);


--
-- Name: service_schemas service_schemas_service_type_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.service_schemas
    ADD CONSTRAINT service_schemas_service_type_unique UNIQUE (service_type);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: service_schemas_industry_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX service_schemas_industry_index ON public.service_schemas USING btree (industry);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: company_user company_user_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.company_user
    ADD CONSTRAINT company_user_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: company_user company_user_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.company_user
    ADD CONSTRAINT company_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict nukMbyGZ0BemdoUz5V3BpYg9RLXzoPqgbEBkg7xuTxS1FQbQ63IVlzB6T5netUh

