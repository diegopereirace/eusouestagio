--
-- PostgreSQL database dump
--

\restrict t5CNWbSb0VSpKn9zDVdnlpoPvG9qRkBShB8uLBqHXFB2VsZ97Trs8A0zPVZtgvv

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
-- Data for Name: drupal_node; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_node (nid, vid, type, uuid, langcode) VALUES (1, 1, 'vagas', '5fffae94-0756-48bf-95f8-8ebb4ca92f6e', 'pt-br');
INSERT INTO public.drupal_node (nid, vid, type, uuid, langcode) VALUES (2, 2, 'vagas', '7f808daa-e310-4c59-be15-d12c063e680a', 'pt-br');
INSERT INTO public.drupal_node (nid, vid, type, uuid, langcode) VALUES (3, 3, 'vagas', 'acd5aa7d-1020-4160-884a-0eedab4a0ac1', 'pt-br');
INSERT INTO public.drupal_node (nid, vid, type, uuid, langcode) VALUES (4, 4, 'vagas', 'b2b981e6-8d2d-41b3-9b27-c7021ffb009e', 'pt-br');


--
-- Name: drupal_node_nid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_node_nid_seq', 4, true);


--
-- PostgreSQL database dump complete
--

\unrestrict t5CNWbSb0VSpKn9zDVdnlpoPvG9qRkBShB8uLBqHXFB2VsZ97Trs8A0zPVZtgvv

