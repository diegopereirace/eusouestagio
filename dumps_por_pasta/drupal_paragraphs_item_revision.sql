--
-- PostgreSQL database dump
--

\restrict 5hZFJaNlf60VDw9m1NWPPejvgelX5KihxRHdrAc7d2ZFERXV9EKk05B0vTBdIIY

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
-- Data for Name: drupal_paragraphs_item_revision; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_paragraphs_item_revision (id, revision_id, langcode, revision_default) VALUES (1, 1, 'pt-br', 1);
INSERT INTO public.drupal_paragraphs_item_revision (id, revision_id, langcode, revision_default) VALUES (2, 2, 'pt-br', 1);
INSERT INTO public.drupal_paragraphs_item_revision (id, revision_id, langcode, revision_default) VALUES (3, 3, 'pt-br', 1);
INSERT INTO public.drupal_paragraphs_item_revision (id, revision_id, langcode, revision_default) VALUES (6, 6, 'pt-br', 1);
INSERT INTO public.drupal_paragraphs_item_revision (id, revision_id, langcode, revision_default) VALUES (10, 10, 'pt-br', 1);


--
-- Name: drupal_paragraphs_item_revision_revision_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_paragraphs_item_revision_revision_id_seq', 11, true);


--
-- PostgreSQL database dump complete
--

\unrestrict 5hZFJaNlf60VDw9m1NWPPejvgelX5KihxRHdrAc7d2ZFERXV9EKk05B0vTBdIIY

