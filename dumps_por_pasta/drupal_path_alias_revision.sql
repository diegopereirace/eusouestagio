--
-- PostgreSQL database dump
--

\restrict GSwGXgFTyFFibk4TRMoRBsTU9apvd45uGVJAvMfp8IOa1NUdKY2JglKkW95oAa5

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
-- Data for Name: drupal_path_alias_revision; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (1, 1, 'en', '/webform/contact', '/form/contact', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (2, 2, 'und', '/webform/contact', '/form/contact', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (3, 3, 'en', '/webform/contact/confirmation', '/form/contact/confirmation', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (4, 4, 'und', '/webform/contact/confirmation', '/form/contact/confirmation', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (5, 5, 'en', '/webform/contact/submissions', '/form/contact/submissions', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (6, 6, 'und', '/webform/contact/submissions', '/form/contact/submissions', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (7, 7, 'en', '/webform/contact/drafts', '/form/contact/drafts', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (8, 8, 'und', '/webform/contact/drafts', '/form/contact/drafts', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (9, 9, 'pt-br', '/node/1', '/vagas/estagiario-de-desenvolvimento-frontend', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (10, 10, 'pt-br', '/node/2', '/vagas/estagiario-de-marketing-digital', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (11, 11, 'pt-br', '/node/3', '/vagas/estagiario-de-analise-de-dados', 1, 1);
INSERT INTO public.drupal_path_alias_revision (id, revision_id, langcode, path, alias, status, revision_default) VALUES (12, 12, 'pt-br', '/node/4', '/vagas/estagiario-de-recursos-humanos', 1, 1);


--
-- Name: drupal_path_alias_revision_revision_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_path_alias_revision_revision_id_seq', 12, true);


--
-- PostgreSQL database dump complete
--

\unrestrict GSwGXgFTyFFibk4TRMoRBsTU9apvd45uGVJAvMfp8IOa1NUdKY2JglKkW95oAa5

