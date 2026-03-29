--
-- PostgreSQL database dump
--

\restrict f2KCNIozKWE0S47w5wJ3glSL5kyVZOJRl2hbEpWMv8nbL5syA11foCJaLh7Ctie

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
-- Data for Name: drupal_paragraphs_item; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_paragraphs_item (id, revision_id, type, uuid, langcode) VALUES (1, 1, 'icone_titulo_descricao', '87620426-a1e8-40da-a5bb-f278279e7b4b', 'pt-br');
INSERT INTO public.drupal_paragraphs_item (id, revision_id, type, uuid, langcode) VALUES (2, 2, 'icone_titulo_descricao', '4abb74d5-5d20-4d94-8284-688d997201be', 'pt-br');
INSERT INTO public.drupal_paragraphs_item (id, revision_id, type, uuid, langcode) VALUES (3, 3, 'icone_titulo_descricao', '33e507cf-0790-4174-a965-8af0c438974c', 'pt-br');
INSERT INTO public.drupal_paragraphs_item (id, revision_id, type, uuid, langcode) VALUES (6, 6, 'curso_extracurricular', '9548e82e-894e-49ed-be49-904096f88b54', 'pt-br');
INSERT INTO public.drupal_paragraphs_item (id, revision_id, type, uuid, langcode) VALUES (10, 10, 'curso_extracurricular', 'cd4f0183-dcc7-445e-8248-25ae1b3f1b18', 'pt-br');


--
-- Name: drupal_paragraphs_item_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_paragraphs_item_id_seq', 11, true);


--
-- PostgreSQL database dump complete
--

\unrestrict f2KCNIozKWE0S47w5wJ3glSL5kyVZOJRl2hbEpWMv8nbL5syA11foCJaLh7Ctie

