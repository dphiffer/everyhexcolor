<?php

$this->query("
  CREATE TABLE color (
    hex VARCHAR(255),
    tweet_id INTEGER,
    created DATETIME
  );
");

$this->query("
  CREATE INDEX color_index ON color (
    hex
  );
");

$this->query("
  CREATE INDEX tweet_id_index ON color (
    tweet_id
  );
");

?>
