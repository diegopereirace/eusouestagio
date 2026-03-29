--
-- PostgreSQL database dump
--

\restrict ZG3TRCyvg5vkoxbNzfhxxLdZvDbT4TypEdOOwxRFJrIHr4DZmJjaSRTCmGIZnXj

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
-- Data for Name: drupal_node_revision; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_node_revision (nid, vid, langcode, revision_uid, revision_timestamp, revision_log, revision_default) VALUES (1, 1, 'pt-br', 1, 1773753803, NULL, 1);
INSERT INTO public.drupal_node_revision (nid, vid, langcode, revision_uid, revision_timestamp, revision_log, revision_default) VALUES (2, 2, 'pt-br', 1, 1773769516, NULL, 1);
INSERT INTO public.drupal_node_revision (nid, vid, langcode, revision_uid, revision_timestamp, revision_log, revision_default) VALUES (3, 3, 'pt-br', 1, 1773769752, NULL, 1);
INSERT INTO public.drupal_node_revision (nid, vid, langcode, revision_uid, revision_timestamp, revision_log, revision_default) VALUES (4, 4, 'pt-br', 1, 1773769884, NULL, 1);


--
-- Name: drupal_node_revision_vid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_node_revision_vid_seq', 4, true);


--
-- PostgreSQL database dump complete
--

\unrestrict ZG3TRCyvg5vkoxbNzfhxxLdZvDbT4TypEdOOwxRFJrIHr4DZmJjaSRTCmGIZnXj

