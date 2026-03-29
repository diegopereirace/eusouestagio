--
-- PostgreSQL database dump
--

\restrict 9jCEyv4WF6gjhEp65mxVrNQrjgTtba9aT1nOof6GqX4WyGjUPpjVH9USKYHE4qL

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
-- Data for Name: drupal_users_field_data; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_users_field_data (uid, langcode, preferred_langcode, preferred_admin_langcode, name, pass, mail, timezone, status, created, changed, access, login, init, default_langcode) VALUES (0, 'pt-br', 'pt-br', NULL, '', NULL, NULL, NULL, 0, 1773421442, 1773421442, 0, 0, NULL, 1);
INSERT INTO public.drupal_users_field_data (uid, langcode, preferred_langcode, preferred_admin_langcode, name, pass, mail, timezone, status, created, changed, access, login, init, default_langcode) VALUES (11, 'pt-br', 'pt-br', NULL, 'degoempresa', '$2y$10$YSRoGvdi3XqbpNdr68BmUuRP4NT7BvkE3QrGoqMb9IMDKErL3lLjy', 'dieps1.0@gmail.com', 'America/Fortaleza', 1, 1774625872, 1774625872, 0, 0, 'dieps1.0@gmail.com', 1);
INSERT INTO public.drupal_users_field_data (uid, langcode, preferred_langcode, preferred_admin_langcode, name, pass, mail, timezone, status, created, changed, access, login, init, default_langcode) VALUES (1, 'pt-br', 'pt-br', NULL, 'admin', '$2y$10$SXhyOWEvPew5RARb00p6oOWy2I4fovPIaMdfxSk/FtFh9ZvI/WyO.', 'diegopereirace@gmail.com', 'America/Fortaleza', 1, 1773421442, 1773421629, 1774716320, 1774533239, 'diegopereirace@gmail.com', 1);


--
-- PostgreSQL database dump complete
--

\unrestrict 9jCEyv4WF6gjhEp65mxVrNQrjgTtba9aT1nOof6GqX4WyGjUPpjVH9USKYHE4qL

