--
-- PostgreSQL database dump
--

-- Dumped from database version 17.2 (Debian 17.2-1.pgdg120+1)
-- Dumped by pg_dump version 17.2 (Debian 17.2-1.pgdg120+1)

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
-- Name: device; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.device (
    id integer NOT NULL,
    device_name character varying(255) NOT NULL,
    type_id integer NOT NULL,
    address_ip character varying(255) NOT NULL,
    snmp_version_id integer NOT NULL,
    username character varying(255),
    password character varying(255),
    description text
);


ALTER TABLE public.device OWNER TO docker;

--
-- Name: device_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.device_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.device_id_seq OWNER TO docker;

--
-- Name: device_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.device_id_seq OWNED BY public.device.id;


--
-- Name: device_status; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.device_status (
    id integer NOT NULL,
    device_id integer,
    mac_address character varying(255),
    status character varying(20)
);


ALTER TABLE public.device_status OWNER TO docker;

--
-- Name: device_status_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.device_status_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.device_status_id_seq OWNER TO docker;

--
-- Name: device_status_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.device_status_id_seq OWNED BY public.device_status.id;


--
-- Name: doctrine_migration_versions; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.doctrine_migration_versions (
    version character varying(191) NOT NULL,
    executed_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    execution_time integer
);


ALTER TABLE public.doctrine_migration_versions OWNER TO docker;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.permissions (
    permission_id integer NOT NULL,
    role character varying(50)
);


ALTER TABLE public.permissions OWNER TO docker;

--
-- Name: permissions_permission_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.permissions_permission_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_permission_id_seq OWNER TO docker;

--
-- Name: permissions_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.permissions_permission_id_seq OWNED BY public.permissions.permission_id;


--
-- Name: snmp_version; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.snmp_version (
    snmp_version_id integer NOT NULL,
    snmp character varying(255) NOT NULL
);


ALTER TABLE public.snmp_version OWNER TO docker;

--
-- Name: snmp_version_snmp_version_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.snmp_version_snmp_version_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.snmp_version_snmp_version_id_seq OWNER TO docker;

--
-- Name: snmp_version_snmp_version_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.snmp_version_snmp_version_id_seq OWNED BY public.snmp_version.snmp_version_id;


--
-- Name: types; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.types (
    type_id integer NOT NULL,
    type character varying(255) NOT NULL
);


ALTER TABLE public.types OWNER TO docker;

--
-- Name: types_type_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.types_type_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.types_type_id_seq OWNER TO docker;

--
-- Name: types_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.types_type_id_seq OWNED BY public.types.type_id;


--
-- Name: user_details; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.user_details (
    id integer NOT NULL,
    user_id integer NOT NULL,
    address character varying(255),
    phone_number character varying(50)
);


ALTER TABLE public.user_details OWNER TO docker;

--
-- Name: user_details_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.user_details_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_details_id_seq OWNER TO docker;

--
-- Name: user_details_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.user_details_id_seq OWNED BY public.user_details.id;


--
-- Name: user_device; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.user_device (
    user_id integer NOT NULL,
    device_id integer NOT NULL
);


ALTER TABLE public.user_device OWNER TO docker;

--
-- Name: users; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.users (
    user_id integer NOT NULL,
    fullname character varying(100) NOT NULL,
    username character varying(50) NOT NULL,
    password character varying(255) NOT NULL,
    permission_id integer NOT NULL,
    email character varying(100) NOT NULL
);


ALTER TABLE public.users OWNER TO docker;

--
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.users_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_user_id_seq OWNER TO docker;

--
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.users_user_id_seq OWNED BY public.users.user_id;


--
-- Name: device id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.device ALTER COLUMN id SET DEFAULT nextval('public.device_id_seq'::regclass);


--
-- Name: device_status id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.device_status ALTER COLUMN id SET DEFAULT nextval('public.device_status_id_seq'::regclass);


--
-- Name: permissions permission_id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.permissions ALTER COLUMN permission_id SET DEFAULT nextval('public.permissions_permission_id_seq'::regclass);


--
-- Name: snmp_version snmp_version_id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.snmp_version ALTER COLUMN snmp_version_id SET DEFAULT nextval('public.snmp_version_snmp_version_id_seq'::regclass);


--
-- Name: types type_id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.types ALTER COLUMN type_id SET DEFAULT nextval('public.types_type_id_seq'::regclass);


--
-- Name: user_details id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.user_details ALTER COLUMN id SET DEFAULT nextval('public.user_details_id_seq'::regclass);


--
-- Name: users user_id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users ALTER COLUMN user_id SET DEFAULT nextval('public.users_user_id_seq'::regclass);


--
-- Data for Name: device; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.device (id, device_name, type_id, address_ip, snmp_version_id, username, password, description) FROM stdin;
65	Device2	1	192.168.1.2	2	user2	password2	Sample device 2
66	Device3	2	192.168.1.3	3	user3	password3	Sample device 3
67	Device4	2	192.168.1.4	1	user4	password4	Sample device 4
68	Device5	3	192.168.1.5	2	user5	password5	Sample device 5
69	Device6	3	192.168.1.6	3	user6	password6	Sample device 6
83	Device20	1	192.168.1.20	2	user20	password20	Sample device 20
64	Device1	1	192.168.1.1	4			Sample device 1
84	test	2	192.168.1.235	4			
\.


--
-- Data for Name: device_status; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.device_status (id, device_id, mac_address, status) FROM stdin;
89	83	N/A	Offline
90	84	N/A	Offline
70	64	N/A	Online
71	65	N/A	Offline
72	66	N/A	Offline
73	67	N/A	Offline
74	68	N/A	Offline
75	69	N/A	Offline
\.


--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.doctrine_migration_versions (version, executed_at, execution_time) FROM stdin;
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.permissions (permission_id, role) FROM stdin;
1	administrator
2	technician
3	operator
\.


--
-- Data for Name: snmp_version; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.snmp_version (snmp_version_id, snmp) FROM stdin;
1	SNMPv1
2	SNMPv2c
3	SNMPv3
4	ICMP
\.


--
-- Data for Name: types; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.types (type_id, type) FROM stdin;
1	Router
2	Switch
3	PC
4	Printer
5	Phone
6	TV
\.


--
-- Data for Name: user_details; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.user_details (id, user_id, address, phone_number) FROM stdin;
\.


--
-- Data for Name: user_device; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.user_device (user_id, device_id) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.users (user_id, fullname, username, password, permission_id, email) FROM stdin;
11	technician	tech	Y1BUeFpvK2V2U3hhWE1BL3pWdzFRQT09OjpYzTGrl2VurMC1ZL6HPh3Q	2	test@com
12	operator	ope	U1Z2K3IrMFg5TDVrYkUzZGJrK1NYZz09Ojp2Gdv5Zd1VCmCrTdmHnLV7	3	test@com1
10	admin	admin	SVEwSXVBdzJRSkxBc3ZRSkVidDdUUT09Ojqlsg2SJWv4qKExqjuvRyWV	1	admin@example.com
\.


--
-- Name: device_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.device_id_seq', 84, true);


--
-- Name: device_status_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.device_status_id_seq', 90, true);


--
-- Name: permissions_permission_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.permissions_permission_id_seq', 3, true);


--
-- Name: snmp_version_snmp_version_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.snmp_version_snmp_version_id_seq', 1, true);


--
-- Name: types_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.types_type_id_seq', 1, false);


--
-- Name: user_details_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.user_details_id_seq', 1, false);


--
-- Name: users_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.users_user_id_seq', 13, true);


--
-- Name: device device_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.device
    ADD CONSTRAINT device_pkey PRIMARY KEY (id);


--
-- Name: device_status device_status_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.device_status
    ADD CONSTRAINT device_status_pkey PRIMARY KEY (id);


--
-- Name: doctrine_migration_versions doctrine_migration_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.doctrine_migration_versions
    ADD CONSTRAINT doctrine_migration_versions_pkey PRIMARY KEY (version);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (permission_id);


--
-- Name: permissions permissions_role_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_role_key UNIQUE (role);


--
-- Name: snmp_version snmp_version_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.snmp_version
    ADD CONSTRAINT snmp_version_pkey PRIMARY KEY (snmp_version_id);


--
-- Name: types types_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.types
    ADD CONSTRAINT types_pkey PRIMARY KEY (type_id);


--
-- Name: user_details user_details_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.user_details
    ADD CONSTRAINT user_details_pkey PRIMARY KEY (id);


--
-- Name: user_device user_device_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.user_device
    ADD CONSTRAINT user_device_pkey PRIMARY KEY (user_id, device_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: device_status device_status_device_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.device_status
    ADD CONSTRAINT device_status_device_id_fkey FOREIGN KEY (device_id) REFERENCES public.device(id) ON DELETE CASCADE;


--
-- Name: device fk_device_snmp_version; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.device
    ADD CONSTRAINT fk_device_snmp_version FOREIGN KEY (snmp_version_id) REFERENCES public.snmp_version(snmp_version_id);


--
-- Name: device_status fk_device_status_device; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.device_status
    ADD CONSTRAINT fk_device_status_device FOREIGN KEY (device_id) REFERENCES public.device(id);


--
-- Name: device fk_device_type; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.device
    ADD CONSTRAINT fk_device_type FOREIGN KEY (type_id) REFERENCES public.types(type_id);


--
-- Name: users fk_users_permission; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT fk_users_permission FOREIGN KEY (permission_id) REFERENCES public.permissions(permission_id);


--
-- Name: user_details user_details_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.user_details
    ADD CONSTRAINT user_details_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: user_device user_device_device_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.user_device
    ADD CONSTRAINT user_device_device_id_fkey FOREIGN KEY (device_id) REFERENCES public.device(id);


--
-- Name: user_device user_device_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.user_device
    ADD CONSTRAINT user_device_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: users users_permission_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_permission_id_fkey FOREIGN KEY (permission_id) REFERENCES public.permissions(permission_id);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;


--
-- PostgreSQL database dump complete
--

