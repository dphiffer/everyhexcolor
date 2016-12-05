<?php

$this->query("
  CREATE TABLE color (
    hex VARCHAR(255),
    tweet_id INTEGER,
    faves INTEGER,
    retweets INTEGER,
    interactions INTEGER,
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

$this->query("
  CREATE INDEX popular_index ON color (
    hex, tweet_id, interactions
  );
");

?>
