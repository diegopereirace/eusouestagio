--
-- PostgreSQL database dump
--

\restrict k8eRzfcAKSJrcuGPTWfvWNYwRbo2Cjjbe2Lg8HZhzCi5fgYHvlZGh3yRCcfFf4b

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
-- Data for Name: drupal_shortcut; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_shortcut (id, shortcut_set, uuid, langcode) VALUES (1, 'default', '74e592ba-0542-4c70-baf3-70f8cf9f3b0b', 'pt-br');
INSERT INTO public.drupal_shortcut (id, shortcut_set, uuid, langcode) VALUES (2, 'default', '9ab93dbd-83ae-49f8-a634-91bb51a05e12', 'pt-br');


--
-- Name: drupal_shortcut_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_shortcut_id_seq', 2, true);


--
-- PostgreSQL database dump complete
--

\unrestrict k8eRzfcAKSJrcuGPTWfvWNYwRbo2Cjjbe2Lg8HZhzCi5fgYHvlZGh3yRCcfFf4b

