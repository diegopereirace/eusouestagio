--
-- PostgreSQL database dump
--

\restrict 2XaFec9DqgKWaMx17fGduk79x9p3bR79Zn4rpg1zlyJewbnjdNgaC1eW9jSWXEB

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
-- Data for Name: drupal_block_content; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_block_content (id, revision_id, type, uuid, langcode) VALUES (1, 1, 'basic', '1929b858-c43d-40a2-84fc-96909300bba4', 'pt-br');
INSERT INTO public.drupal_block_content (id, revision_id, type, uuid, langcode) VALUES (2, 2, 'footer', 'd0326db5-fc80-4cf5-a0b9-8f779dc4aeba', 'pt-br');
INSERT INTO public.drupal_block_content (id, revision_id, type, uuid, langcode) VALUES (3, 3, 'whatsapp', '1b69eb51-2716-4f7d-b59c-a2a7a687c8c9', 'pt-br');
INSERT INTO public.drupal_block_content (id, revision_id, type, uuid, langcode) VALUES (4, 4, 'banner', '80076c36-2af4-4269-9a5c-fc8ea4ffa132', 'pt-br');
INSERT INTO public.drupal_block_content (id, revision_id, type, uuid, langcode) VALUES (5, 5, 'destaque', '649e2229-5918-423d-9137-d14054c8e8d1', 'pt-br');
INSERT INTO public.drupal_block_content (id, revision_id, type, uuid, langcode) VALUES (6, 6, 'cto', '187f80ea-8ce2-496c-b3ef-32cb972aa41d', 'pt-br');
INSERT INTO public.drupal_block_content (id, revision_id, type, uuid, langcode) VALUES (7, 7, 'basic', '1e1afa90-ba36-4215-a3f9-051f99fa8428', 'pt-br');


--
-- Name: drupal_block_content_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_block_content_id_seq', 7, true);


--
-- PostgreSQL database dump complete
--

\unrestrict 2XaFec9DqgKWaMx17fGduk79x9p3bR79Zn4rpg1zlyJewbnjdNgaC1eW9jSWXEB

