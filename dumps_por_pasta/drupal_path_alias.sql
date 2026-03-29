--
-- PostgreSQL database dump
--

\restrict 3fnGchsPW3AgY1RrjInfmIIgNc7odE6bsOXwLYXjCAIhEXERaNjoTWQkdzt33pj

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
-- Data for Name: drupal_path_alias; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (1, 1, '2a244ae2-7f0b-4b90-a26d-aa7e7e4a4315', 'en', '/webform/contact', '/form/contact', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (2, 2, 'a0378c51-c347-4b82-8175-02339f2f0ce2', 'und', '/webform/contact', '/form/contact', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (3, 3, 'c158f486-6455-476c-8cc6-aeb8ed3f1d8d', 'en', '/webform/contact/confirmation', '/form/contact/confirmation', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (4, 4, '83d77018-a643-4af5-b1e7-2f59700214f2', 'und', '/webform/contact/confirmation', '/form/contact/confirmation', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (5, 5, '61e7c5a5-ff10-4a1c-b821-ae8699b10241', 'en', '/webform/contact/submissions', '/form/contact/submissions', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (6, 6, '86578475-bbe1-41ea-8494-a23bc7861ff8', 'und', '/webform/contact/submissions', '/form/contact/submissions', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (7, 7, 'e18c5e70-7b2e-438c-be3d-444f4cf65021', 'en', '/webform/contact/drafts', '/form/contact/drafts', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (8, 8, '6ceab0cf-6695-4323-b1b0-dcf891475b7f', 'und', '/webform/contact/drafts', '/form/contact/drafts', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (9, 9, '42192033-5823-4286-8cd2-85e0feccabf8', 'pt-br', '/node/1', '/vagas/estagiario-de-desenvolvimento-frontend', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (10, 10, 'fa05e40a-2efd-4d43-9dbb-dc5337e7bf31', 'pt-br', '/node/2', '/vagas/estagiario-de-marketing-digital', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (11, 11, '757ccc0f-49a3-4737-8d99-028e5d339d34', 'pt-br', '/node/3', '/vagas/estagiario-de-analise-de-dados', 1);
INSERT INTO public.drupal_path_alias (id, revision_id, uuid, langcode, path, alias, status) VALUES (12, 12, 'baa3036e-d17c-4dc3-901c-2d592dafcd11', 'pt-br', '/node/4', '/vagas/estagiario-de-recursos-humanos', 1);


--
-- Name: drupal_path_alias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_path_alias_id_seq', 12, true);


--
-- PostgreSQL database dump complete
--

\unrestrict 3fnGchsPW3AgY1RrjInfmIIgNc7odE6bsOXwLYXjCAIhEXERaNjoTWQkdzt33pj

