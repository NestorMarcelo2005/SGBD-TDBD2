<?php
$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
  }


function test_input($data) {
  $data = trim($data);                     
  $data = htmlspecialchars($data);   
  return $data;
}

global $current_page; $current_page = get_site_url().'/'.basename(get_permalink());

function gerar_link_voltar() {
  echo "<script type='text/javascript'>
      document.write('<strong><a id=\"retornar\" href=\"javascript:history.back()\" class=\"backLink\" title=\"Voltar atrás\">VOLTAR ATRÁS</a></strong>');
  </script>
  <noscript>
      <a href='" . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '#') . "' class='backLink' title='Voltar atrás'>Voltar atrás</a>
  </noscript>";
}

function get_enum_values($connection, $table, $column )
{
    $query = " SHOW COLUMNS FROM $table LIKE '$column' ";
    $result = mysqli_query($connection, $query );
    $row = mysqli_fetch_array($result , MYSQLI_NUM );
    $regex = "/'(.*?)'/";
    preg_match_all( $regex , $row[1], $enum_array );
    $enum_fields = $enum_array[1];
    return( $enum_fields );
}

require 'vendor/autoload.php';


?>
