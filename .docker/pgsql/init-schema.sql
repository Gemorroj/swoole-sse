ALTER ROLE web IN DATABASE postgres SET search_path TO sse;
SET search_path TO sse;
CREATE SCHEMA sse AUTHORIZATION web;
