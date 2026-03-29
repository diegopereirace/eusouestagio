--
-- PostgreSQL database dump
--

\restrict EBQhAZ0a91L0xUEpOGHZqFGaksYrfLqiC1IaQNijhFfZgviZFfIJB2cGVIdrTxo

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
-- Data for Name: drupal_menu_link_content_revision; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_menu_link_content_revision (id, revision_id, langcode, revision_user, revision_created, revision_log_message, revision_default) VALUES (3, 3, 'pt-br', NULL, 1773504295, NULL, 1);
INSERT INTO public.drupal_menu_link_content_revision (id, revision_id, langcode, revision_user, revision_created, revision_log_message, revision_default) VALUES (7, 7, 'pt-br', NULL, 1773504590, NULL, 1);
INSERT INTO public.drupal_menu_link_content_revision (id, revision_id, langcode, revision_user, revision_created, revision_log_message, revision_default) VALUES (4, 4, 'pt-br', NULL, 1773504328, NULL, 1);
INSERT INTO public.drupal_menu_link_content_revision (id, revision_id, langcode, revision_user, revision_created, revision_log_message, revision_default) VALUES (5, 5, 'pt-br', NULL, 1773504345, NULL, 1);
INSERT INTO public.drupal_menu_link_content_revision (id, revision_id, langcode, revision_user, revision_created, revision_log_message, revision_default) VALUES (8, 8, 'pt-br', NULL, 1773668568, NULL, 1);
INSERT INTO public.drupal_menu_link_content_revision (id, revision_id, langcode, revision_user, revision_created, revision_log_message, revision_default) VALUES (1, 1, 'pt-br', NULL, 1773424961, NULL, 1);
INSERT INTO public.drupal_menu_link_content_revision (id, revision_id, langcode, revision_user, revision_created, revision_log_message, revision_default) VALUES (2, 2, 'pt-br', NULL, 1773425070, NULL, 1);
INSERT INTO public.drupal_menu_link_content_revision (id, revision_id, langcode, revision_user, revision_created, revision_log_message, revision_default) VALUES (6, 6, 'pt-br', NULL, 1773504574, NULL, 1);


--
-- Name: drupal_menu_link_content_revision_revision_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_menu_link_content_revision_revision_id_seq', 8, true);


--
-- PostgreSQL database dump complete
--

\unrestrict EBQhAZ0a91L0xUEpOGHZqFGaksYrfLqiC1IaQNijhFfZgviZFfIJB2cGVIdrTxo

