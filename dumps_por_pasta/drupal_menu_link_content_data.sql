--
-- PostgreSQL database dump
--

\restrict SFPM0bSRCkCJjsdAMBveXdw6UprzI13BHlni6qaQJeA8UMr8L1CO4UQEdxUq8Ic

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
-- Data for Name: drupal_menu_link_content_data; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.drupal_menu_link_content_data (id, revision_id, bundle, langcode, enabled, title, description, menu_name, link__uri, link__title, link__options, external, rediscover, weight, expanded, parent, changed, default_langcode, revision_translation_affected) VALUES (3, 3, 'menu_link_content', 'pt-br', 1, 'Home', NULL, 'links-rapidos', 'internal:/', '', 'a:0:{}', 0, 1, 0, 0, NULL, 1773504295, 1, 1);
INSERT INTO public.drupal_menu_link_content_data (id, revision_id, bundle, langcode, enabled, title, description, menu_name, link__uri, link__title, link__options, external, rediscover, weight, expanded, parent, changed, default_langcode, revision_translation_affected) VALUES (7, 7, 'menu_link_content', 'pt-br', 1, 'Publicar Vaga', NULL, 'para-empresas', 'internal:/', '', 'a:0:{}', 0, 1, 0, 0, NULL, 1773506314, 1, 1);
INSERT INTO public.drupal_menu_link_content_data (id, revision_id, bundle, langcode, enabled, title, description, menu_name, link__uri, link__title, link__options, external, rediscover, weight, expanded, parent, changed, default_langcode, revision_translation_affected) VALUES (4, 4, 'menu_link_content', 'pt-br', 1, 'Buscar Vagas', NULL, 'links-rapidos', 'internal:/', '', 'a:0:{}', 0, 1, 0, 0, NULL, 1773665772, 1, 1);
INSERT INTO public.drupal_menu_link_content_data (id, revision_id, bundle, langcode, enabled, title, description, menu_name, link__uri, link__title, link__options, external, rediscover, weight, expanded, parent, changed, default_langcode, revision_translation_affected) VALUES (5, 5, 'menu_link_content', 'pt-br', 1, 'Sobre Nós', NULL, 'links-rapidos', 'internal:/', '', 'a:0:{}', 0, 1, 0, 0, NULL, 1773665788, 1, 1);
INSERT INTO public.drupal_menu_link_content_data (id, revision_id, bundle, langcode, enabled, title, description, menu_name, link__uri, link__title, link__options, external, rediscover, weight, expanded, parent, changed, default_langcode, revision_translation_affected) VALUES (8, 8, 'menu_link_content', 'pt-br', 1, 'Contato', NULL, 'main', 'internal:/', '', 'a:0:{}', 0, 1, -47, 0, NULL, 1773668611, 1, 1);
INSERT INTO public.drupal_menu_link_content_data (id, revision_id, bundle, langcode, enabled, title, description, menu_name, link__uri, link__title, link__options, external, rediscover, weight, expanded, parent, changed, default_langcode, revision_translation_affected) VALUES (1, 1, 'menu_link_content', 'pt-br', 1, 'Quem Somos', NULL, 'main', 'internal:/', '', 'a:0:{}', 0, 1, -49, 0, NULL, 1773668611, 1, 1);
INSERT INTO public.drupal_menu_link_content_data (id, revision_id, bundle, langcode, enabled, title, description, menu_name, link__uri, link__title, link__options, external, rediscover, weight, expanded, parent, changed, default_langcode, revision_translation_affected) VALUES (2, 2, 'menu_link_content', 'pt-br', 1, 'Vagas', NULL, 'main', 'internal:/', '', 'a:0:{}', 0, 1, -48, 0, NULL, 1773668611, 1, 1);
INSERT INTO public.drupal_menu_link_content_data (id, revision_id, bundle, langcode, enabled, title, description, menu_name, link__uri, link__title, link__options, external, rediscover, weight, expanded, parent, changed, default_langcode, revision_translation_affected) VALUES (6, 6, 'menu_link_content', 'pt-br', 1, 'Cadastrar Empresa', NULL, 'para-empresas', 'internal:/cadastro/empresa', '', 'a:0:{}', 0, 1, 0, 0, NULL, 1774625416, 1, 1);


--
-- PostgreSQL database dump complete
--

\unrestrict SFPM0bSRCkCJjsdAMBveXdw6UprzI13BHlni6qaQJeA8UMr8L1CO4UQEdxUq8Ic

