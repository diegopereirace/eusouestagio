--
-- PostgreSQL database dump
--

\restrict ZNFyFYRzrccuKgq9PNIQBaltBQuOA3Sz6diYD0IX1ePZhELK0Lr7Pm6F6cPLyAe

-- Dumped from database version 16.13
-- Dumped by pg_dump version 16.13

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: drupal_users; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_users (uid, uuid, langcode) VALUES (0, '86001faf-8d7a-4686-b354-7814c5827d31', 'pt-br');
INSERT INTO public.drupal_users (uid, uuid, langcode) VALUES (1, '022caca1-a8c2-45b9-9e24-8ce60647a76c', 'pt-br');
INSERT INTO public.drupal_users (uid, uuid, langcode) VALUES (11, '625c1a90-dbab-4d4a-8b53-1b7b2c40b90d', 'pt-br');


--
-- Name: drupal_users_uid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_users_uid_seq', 11, true);


--
-- PostgreSQL database dump complete
--

\unrestrict ZNFyFYRzrccuKgq9PNIQBaltBQuOA3Sz6diYD0IX1ePZhELK0Lr7Pm6F6cPLyAe

