--
-- PostgreSQL database dump
--

\restrict bYVFlBBeRIWQOzVgGFrKCw5EvfIH1ZZcwJLEQcURwTm472Ccr6VPWdfdtJgcxkb

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
-- Data for Name: drupal_node_access; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_node_access (nid, langcode, fallback, gid, realm, grant_view, grant_update, grant_delete) VALUES (0, '', 1, 0, 'all', 1, 0, 0);


--
-- PostgreSQL database dump complete
--

\unrestrict bYVFlBBeRIWQOzVgGFrKCw5EvfIH1ZZcwJLEQcURwTm472Ccr6VPWdfdtJgcxkb

