--
-- PostgreSQL database dump
--

\restrict 4HN5pnPZGGhR65R2BRJ75WML8AEAhKx6g2IruaSceqLzeXeHKpbAMCIF9esJ6gr

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
-- Data for Name: drupal_shortcut_field_data; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_shortcut_field_data (id, shortcut_set, langcode, title, weight, link__uri, link__title, link__options, default_langcode) VALUES (1, 'default', 'pt-br', 'Add content', -20, 'internal:/node/add', NULL, 'a:0:{}', 1);
INSERT INTO public.drupal_shortcut_field_data (id, shortcut_set, langcode, title, weight, link__uri, link__title, link__options, default_langcode) VALUES (2, 'default', 'pt-br', 'All content', -19, 'internal:/admin/content', NULL, 'a:0:{}', 1);


--
-- PostgreSQL database dump complete
--

\unrestrict 4HN5pnPZGGhR65R2BRJ75WML8AEAhKx6g2IruaSceqLzeXeHKpbAMCIF9esJ6gr

