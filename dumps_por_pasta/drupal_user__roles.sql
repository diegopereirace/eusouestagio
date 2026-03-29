--
-- PostgreSQL database dump
--

\restrict ofZFIl2ElwDz3bTXq6wC47X3wbumAJPS2YxzUNiK2YGdeZysh4oMuxxyr0wQ9S5

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
-- Data for Name: drupal_user__roles; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_user__roles (bundle, deleted, entity_id, revision_id, langcode, delta, roles_target_id) VALUES ('user', 0, 1, 1, 'pt-br', 0, 'administrator');
INSERT INTO public.drupal_user__roles (bundle, deleted, entity_id, revision_id, langcode, delta, roles_target_id) VALUES ('user', 0, 11, 11, 'pt-br', 0, 'empresa');


--
-- PostgreSQL database dump complete
--

\unrestrict ofZFIl2ElwDz3bTXq6wC47X3wbumAJPS2YxzUNiK2YGdeZysh4oMuxxyr0wQ9S5

