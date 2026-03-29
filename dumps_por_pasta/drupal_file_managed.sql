--
-- PostgreSQL database dump
--

\restrict IPzAw7MhkTL47jxTYKEiRnEawcmMUx3r0eqWlztirhzyXXP8tFDNrfa13Gm2Is9

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
-- Data for Name: drupal_file_managed; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_file_managed (fid, uuid, langcode, uid, filename, uri, filemime, filesize, status, created, changed) VALUES (7, '550e11b4-4899-45fe-8fc4-feac19b5a858', 'pt-br', 1, 'hero-bg-WLfSbcCuteWuFySw75SYia.webp', 'public://block/banner/image-desktop/2026-03/hero-bg-WLfSbcCuteWuFySw75SYia.webp', 'image/webp', 36262, 1, 1773670966, 1773670976);
INSERT INTO public.drupal_file_managed (fid, uuid, langcode, uid, filename, uri, filemime, filesize, status, created, changed) VALUES (8, '26c14935-0046-4351-b8a5-a8de6fc0a8f7', 'pt-br', 1, '150x150.png', 'public://paragraph/image/2026-03/150x150.png', 'image/png', 373, 1, 1773682750, 1773682803);
INSERT INTO public.drupal_file_managed (fid, uuid, langcode, uid, filename, uri, filemime, filesize, status, created, changed) VALUES (9, '86a8d6fc-1335-489a-9c0c-eeae54027f3b', 'pt-br', 1, '300x300.png', 'public://paragraph/image/2026-03/300x300.png', 'image/png', 1132, 1, 1773682777, 1773682803);
INSERT INTO public.drupal_file_managed (fid, uuid, langcode, uid, filename, uri, filemime, filesize, status, created, changed) VALUES (10, '4e65a8df-4e3e-4778-a3ce-d46d6b996d48', 'pt-br', 1, '300x300-maroon.png', 'public://paragraph/image/2026-03/300x300-maroon.png', 'image/png', 1132, 1, 1773682797, 1773682803);
INSERT INTO public.drupal_file_managed (fid, uuid, langcode, uid, filename, uri, filemime, filesize, status, created, changed) VALUES (11, '4267eb2b-8e7d-44b0-bd5c-703d550b98d5', 'pt-br', 1, 'icon-1.webp', 'public://paragraph/image/2026-03/icon-1.webp', 'image/webp', 20532, 1, 1773752175, 1773752188);
INSERT INTO public.drupal_file_managed (fid, uuid, langcode, uid, filename, uri, filemime, filesize, status, created, changed) VALUES (12, '44d2d742-2971-4775-bf24-27d0a788964e', 'pt-br', 1, 'icon-2.webp', 'public://paragraph/image/2026-03/icon-2.webp', 'image/webp', 22956, 1, 1773752335, 1773752361);
INSERT INTO public.drupal_file_managed (fid, uuid, langcode, uid, filename, uri, filemime, filesize, status, created, changed) VALUES (13, '999a1dc0-3b78-43b8-85ad-97cf66455ba4', 'pt-br', 1, 'icon-3.webp', 'public://paragraph/image/2026-03/icon-3.webp', 'image/webp', 25072, 1, 1773752500, 1773752513);
INSERT INTO public.drupal_file_managed (fid, uuid, langcode, uid, filename, uri, filemime, filesize, status, created, changed) VALUES (14, '1e73a408-128d-4e52-a51a-478eff7cef8a', 'pt-br', 1, 'cta-bg-WL4TmXXDpoKLusR9HtJM9q.webp', 'public://block/banner/image-desktop/2026-03/cta-bg-WL4TmXXDpoKLusR9HtJM9q.webp', 'image/webp', 52936, 1, 1773765530, 1773765540);


--
-- Name: drupal_file_managed_fid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.drupal_file_managed_fid_seq', 14, true);


--
-- PostgreSQL database dump complete
--

\unrestrict IPzAw7MhkTL47jxTYKEiRnEawcmMUx3r0eqWlztirhzyXXP8tFDNrfa13Gm2Is9

