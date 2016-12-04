<?php

$this->query("
  CREATE TABLE color (
    hex VARCHAR(255),
    created DATETIME
  );
");

$this->query("
  CREATE INDEX color_index ON color (
    hex
  );
");

?>
