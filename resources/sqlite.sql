-- #! sqlite
-- #{ init
CREATE TABLE IF NOT EXISTS `money`
(
    `xuid` VARCHAR
(
    32
) NOT NULL , `pseudo` VARCHAR
(
    32
) NOT NULL , `money` FLOAT NOT NULL , PRIMARY KEY
(
    `xuid`
));
-- #}
-- #{   exist
-- #    :xuid string
SELECT *
FROM money
WHERE `xuid` = :xuid;
-- #}
-- #{   create
-- #    :xuid string
-- #    :pseudo string
-- #    :money float
INSERT INTO money (`xuid`, `pseudo`, `money`)
VALUES (:xuid, :pseudo, :money);
-- #}
-- #{ update
-- #    :xuid string
-- #    :money float
UPDATE money
SET `money`=:money
WHERE `xuid` = :xuid;
-- #}
-- #{ all
SELECT *
FROM money;
-- #}