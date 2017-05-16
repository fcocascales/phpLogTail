INSERT INTO web_inscripcions (nom, cognoms, professió, centre, adreça, telèfon, correu, nou) VALUES ('aa45', 'bb45', 'cc45', 'dd45', 'ee345\nee3b45', 'ff345', 'gg345', 1); -- 2011-07-22 12:42:54 — 192.168.2.4 — admin
UPDATE web_inscripcions SET adreça = 'C/Mayor 33', cp = '8000', població = 'Barcelona' WHERE id = '1'; -- 2011-07-25 16:02:57 — 192.168.2.4 — admin
UPDATE web_inscripcions SET cp = '08000' WHERE id = '1'; -- 2011-07-25 16:03:25 — 192.168.2.4 — admin
INSERT INTO web_inscripcions (nom, cognoms, professió, centre, adreça, cp, població, telèfon, correu, nou) VALUES ('n9', 'c9', 'p9', 'c9', 'ac9', 'cp9', 'p9', 't9', 'ce9', 1); -- 2011-07-25 16:28:44 — 192.168.2.4 — admin
DELETE FROM web_inscripcions WHERE id = '6'; -- 2011-07-25 16:29:05 — 192.168.2.4 — admin
DELETE FROM web_inscripcions WHERE id = '2'; -- 2011-07-25 16:29:12 — 192.168.2.4 — admin
DELETE FROM web_inscripcions WHERE id = '3'; -- 2011-07-25 16:29:17 — 192.168.2.4 — admin
DELETE FROM web_inscripcions WHERE id = '4'; -- 2011-07-25 16:29:22 — 192.168.2.4 — admin
DELETE FROM web_inscripcions WHERE id = '5'; -- 2011-07-25 16:29:29 — 192.168.2.4 — admin

