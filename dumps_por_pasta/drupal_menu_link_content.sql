--
-- PostgreSQL database dump
--

\restrict rJVxAbuFBq3ZyyCIqdvrWtOI89uenTOqyEcPAhhuc68OeuSXfvHNSTw94yVC37k

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
-- Data for Name: drupal_menu_link_content; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_menu_link_content (id, revision_id, bundle, uuid, langcode) VALUES (3, 3, 'menu_link_content', '5e9b5321-50eb-47be-92d7-fc65b6e609fa', 'pt-br');
INSERT INTO public.drupal_menu_link_content (id, revision_id, bundle, uuid, langcode) VALUES (7, 7, 'menu_link_content', '11337ff6-b8a5-427c-8cbb-578a8809c274', 'pt-br');
INSERT INTO public.drupal_menu_link_content (id, revision_id, bundle, uuid, langcode) VALUES (4, 4, 'menu_link_content', '0ceba6f7-d1ec-4073-abcd-5af927108304', 'pt-br');
INSERT INTO public.drupal_menu_link_content (id, revision_id, bundle, uuid, langcode) VALUES (5, 5, 'menu_link_content', 'e4b535e1-6592-495e-a59c-cf9acb621db2', 'pt-br');
INSERT INTO public.drupal_menu_link_content (id, revision_id, bundle, uuid, langcode) VALUES (8, 8, 'menu_link_content', '2bb95157-f5e8-4ff9-9acf-81ec060ccb5a', 'pt-br');
INSERT INTO public.drupal_menu_link_content (id, revision_id, bundle, uuid, langcode) VALUES (1, 1, 'menu_link_content', '67d74e44-68ca-4559-8304-538471255d61', 'pt-br');
INSERT INTO public.drupal_menu_link_content (id, revision_id, bundle, uuid, langcode) VALUES (2, 2, 'menu_link_content', '35d18746-0c61-45ce-9fe9-64918df7586b', 'pt-br');
INSERT INTO public.drupal_menu_link_content (id, revision_id, bundle, uuid, langcode) VALUES (6, 6, 'menu_link_content', '4601a050-b193-4297-8ed5-ae77c78843ac', 'pt-br');


--
-- Name: drupal_menu_link_content_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_menu_link_content_id_seq', 8, true);


--
-- PostgreSQL database dump complete
--

\unrestrict rJVxAbuFBq3ZyyCIqdvrWtOI89uenTOqyEcPAhhuc68OeuSXfvHNSTw94yVC37k

