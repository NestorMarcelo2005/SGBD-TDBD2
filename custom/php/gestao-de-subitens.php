<?php
include 'common.php';

if (!is_user_logged_in()) {
  die("Utilizador desconectado!");
}

if (!current_user_can("manage_subitems")) {
  die("Não tem autorização para aceder a esta página");
}

$query_nome_de_itens="SELECT name AS nome_do_item, id
FROM item
ORDER BY nome_do_item";

$nome_de_itens = mysqli_query($link, $query_nome_de_itens);
$n_itens = mysqli_num_rows($nome_de_itens);
if($n_itens>0){
  if (!isset($_REQUEST['estado']))
  {
    echo "<table class='custom-table'>";
    echo "<thead>";
    echo "<tr>
              <th>item</th>
              <th>id</th>
              <th>subitem</th>
              <th>tipo de valor</th>
              <th>nome do campo no formulário</th>
              <th>tipo do campo no formulário</th>
              <th>tipo de unidade</th>
              <th>ordem do campo no formulário</th>
              <th>obrigatório</th>
              <th>estado</th>
              <th>ação</th>
            </tr>";
    echo"</thead>";
    while ($itens = mysqli_fetch_assoc($nome_de_itens))
    {

      $query_subitens = "SELECT subitem.state AS estado,subitem.form_field_order AS ordemsbitens,subitem.form_field_type AS tipo_do_campo,subitem.form_field_name AS nome_do_campo,subitem.value_type AS tipo_valor,subitem.name AS nome_sb,subitem.id AS id_subitem,subitem.mandatory AS obrigação,subitem_unit_type.name AS nome_tipo_unidade
            FROM subitem
            LEFT JOIN subitem_unit_type ON subitem.unit_type_id = subitem_unit_type.id
            WHERE subitem.item_id = " . $itens["id"] . "
            ORDER BY subitem.item_id, subitem.name";
      $subitens = mysqli_query($link, $query_subitens);
      $n_de_subitens = mysqli_num_rows($subitens);

      if ($n_de_subitens > 0) 
      {
        echo "<tr>";
        echo "<td rowspan='" . $n_de_subitens . "'>" . $itens["nome_do_item"] . "</td>";
        while ($subitensper = mysqli_fetch_assoc($subitens)) 
        {
          
          echo "<td class='id_color'>" . $subitensper['id_subitem'] . "</td>";
          echo "<td>" . $subitensper['nome_sb'] . "</td>";
          echo "<td class='id_color'>" . $subitensper['tipo_valor'] . "</td>";

          echo "<td>" . $subitensper['nome_do_campo'] . "</td>";
          echo "<td class='id_color'>" . $subitensper['tipo_do_campo'] . "</td>";
          
          
          if(empty($subitensper['nome_tipo_unidade']))
          {
            echo "<td>-</td>";
          }
          else{
            echo "<td>" . $subitensper['nome_tipo_unidade'] . "</td>";
            
          }
            
          echo "<td class='id_color'>" . $subitensper['ordemsbitens'] . "</td>";

          if ($subitensper['obrigação'] == 0) {
            echo "<td>não</td>";
            
        } 
        else {
            echo "<td>sim</td>";
            
        }

          echo "<td class='id_color'>" . $subitensper['estado'] . "</td>";

          if ($subitensper['estado'] == 'active') 
          {
            echo "<td>
                    <span id='blue'>";
                        echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=editar&tipo=subitens&nome=' . $subitensper['nome_sb'] . '&id=' . $subitensper['id_subitem'] . '">[editar]</a>';
                        echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=desativar&tipo=subitens&nome=' . $subitensper['nome_sb'] . '&id=' . $subitensper['id_subitem'] . '">[desativar]</a>';
                        echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=apagar&tipo=subitens&nome=' . $subitensper['nome_sb'] . '&id=' . $subitensper['id_subitem'] . '">[apagar]</a>';
                        echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=historico&tipo=subitens&nome=' . $subitensper['nome_sb'] . '&id=' . $subitensper['id_subitem'] . '">[historico]</a>
                    </span>
                </td>';
          }
          else {
            echo "<td>
                    <span id='blue'>";
                        echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=editar&tipo=subitens&nome=' . $subitensper['nome_sb'] . '&id=' . $subitensper['id_subitem'] . '">[editar]</a>';
                        echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=ativar&tipo=subitens&nome=' . $subitensper['nome_sb'] . '&id=' . $subitensper['id_subitem'] . '">[ativar]</a>';
                        echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=apagar&tipo=subitens&nome=' . $subitensper['nome_sb'] . '&id=' . $subitensper['id_subitem'] . '">[apagar]</a>';
                        echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=historico&tipo=subitens&nome=' . $subitensper['nome_sb'] . '&id=' . $subitensper['id_subitem'] . '">[historico]</a>
                    </span>
                </td>';
          }
            echo "</tr>";
          }
          
      }
      else {
          echo "<tr>";
          echo "<td>" . $itens["nome_do_item"] . "</td>";
          echo "<td id = 'NoArgumentsColor'colspan='10'><span id='centro'>este item não tem subitens</span></td>";
          echo "</tr>";
      }
    }

    echo "</table>";
    echo '<strong><span id="black">Histórico de subitens apagados: </span></strong>';
    echo '<span id="black"><a href="' . get_site_url() . '/edicao-de-dados?estado=historicoapagar&tipo=subitens">Subitens apagados</a></span>';
    echo "<h3><strong>Gestão de subitens - introdução</strong></h3>";
    echo '<span id="red"><strong> * Obrigatório</strong></span>';
    echo '<form method="post" action="">
      <span id="black"><strong>Nome do subitem:</strong></span>
      <span id="red"><strong>*</strong></span>
      <input type="text" name="nome_do_subitem"><br>

      <span id="black"><strong>Tipo de valor:</strong></span>
      <span id="red"><strong>*</strong></span>';
      $valores = get_enum_values($link, "subitem", "value_type");
      if ($valores && is_array($valores)) {          
          foreach ($valores as $valor) {

            echo "<input type='radio' name='tipo_do_valor' value='" . test_input($valor) . "'> " . test_input($valor) ;

          }

      } else {
          echo "Nenhum valor encontrado.";
      }
      
      echo '<br><span id="black"><strong>Item:</strong></span>';
      echo '<span id="red"><strong>*</strong></span>';
      echo '<select id="Item" name="item">'; 

      echo '<option value="Selecione uma das opções">Selecione uma das opções</option>'; 
      mysqli_data_seek($nome_de_itens, 0);
      while ($itens = mysqli_fetch_assoc($nome_de_itens)) {
        $item = test_input($itens['nome_do_item']); 
        echo '<option value="' . $item . '">' . $item . '</option>';
      }

      echo '</select><br>';

    echo '<span id="black"><strong>Tipo do campo do formulário:</strong></span>
      <span id="red"><strong>*</strong></span>';

      $campo_tipo = get_enum_values($link, "subitem", "form_field_type");
      if ($campo_tipo && is_array($campo_tipo)) {          
          foreach ($campo_tipo as $tipos_campo) {
            echo "<input type='radio' name='tipo_do_form' value='" . test_input($tipos_campo) . "'> " . test_input($tipos_campo) ;
          }
          echo '<br>';
      } else {
          echo "Nenhum valor encontrado.";
      }


  echo '<span id="black"><strong>Tipo de unidade:</strong></span>';
  
  echo '<select name="tipo_de_unidade">';
  echo '<option value=""> </option>';
  $query_unidades="SELECT name AS nome_unidade
  FROM subitem_unit_type"; 
  $unidades = [];
  $resultado_unidades = mysqli_query($link, $query_unidades);
    while ($nome_unidade = mysqli_fetch_assoc($resultado_unidades)) {
      $unit = test_input($nome_unidade['nome_unidade']);
      if (!is_null($unit) && !in_array($unit, $unidades)) {
          $unidades[] = $unit;
          echo '<option value="' . test_input($unit) . '">' . test_input($unit) . '</option>';
      }
  }
  
  echo '</select><br>';
      
    echo '<span id="black"><strong>Ordem do campo no formulário:</strong></span>';
    echo '<span id="red"><strong>*</strong></span>';
    echo '<input type="text" name="ordem_campo_form"><br>';
    echo 'Observação: Insira apenas números<br>';
    echo'<span id="black"><strong>Obrigatório:</strong></span>
    <span id="red"><strong>*</strong></span>';

    $obrigatorioo = get_enum_values($link, "subitem", "state");
    if ($obrigatorioo && is_array($obrigatorioo)) {          
      foreach ($obrigatorioo as $obrigatoriedade) {
        if ($obrigatoriedade == 'active') {
            echo "<input type='radio' name='obrigatorio' value='active'> Sim";
        } else {
            echo "<input type='radio' name='obrigatorio' value='inactive'> Não";
        }
    }
    
    } else {
        echo "Nenhum valor encontrado.";
    }
  echo '<br>';
  echo'<input type="hidden" name="estado" value="inserir">
      <button type="submit" id="InsertOrSimilar">Submeter</button><br>
    </form>';

  }
  else if ($_REQUEST['estado'] == 'inserir')
  {
    $nome_dosubitem=$tipo_devalor=$nome_doitem=$tipo_do_campoformulario=$tipo_unidade=$ordem_do_campo_formulario=$obrigatorio="";
    if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
      
      

      if (empty($_POST["nome_do_subitem"])) {
        echo "<strong>Erro: Insira o nome do subitem.</strong><br>";
        gerar_link_voltar(); 
        die("");
      } else {
        $nome_dosubitem = test_input($_POST["nome_do_subitem"]);
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/u', $nome_dosubitem)) {
            echo "<strong>Erro: O nome do subitem deve conter apenas letras e espaços.</strong><br>";
            gerar_link_voltar(); 
            die("");
        } 
      }
      
      if (isset($_POST["tipo_do_valor"])) {
        $tipo_devalor = test_input($_POST["tipo_do_valor"]);
      } 
      else {
        $tipo_devalor = null; 
        echo '<strong>Erro: Insira o tipo do valor</strong><br>';
        gerar_link_voltar(); 
        die("");
      }

      if (empty($_POST["item"])) {
          echo "<strong>Erro: Insira o nome do item.</strong><br>";
          gerar_link_voltar(); 
          die("");
      } 
      else {
          $nome_doitem = test_input($_POST["item"]);
      }
      
      if (empty($_POST["tipo_do_form"])) {
          echo "<strong>Erro: Insira o tipo de campo do formulário.</strong><br>";
          gerar_link_voltar(); 
          die("");
      } 
      else {
          $tipo_do_campoformulario = test_input($_POST["tipo_do_form"]);
      }
      
      if (empty($_POST["ordem_campo_form"])) {
          echo "<strong>Erro: Insira a ordem do campo do formulário.</strong><br>";
          gerar_link_voltar(); 
          die("");
      } else {
          $ordem_do_campo_formulario = test_input($_POST["ordem_campo_form"]);
          if (!preg_match('/^\d+$/', $ordem_do_campo_formulario)) {
              echo "<strong>Erro: A ordem do campo do formulário deve ser um número inteiro positivo.</strong><br>";
              gerar_link_voltar(); 
              die("");
          } 
      }
      
      
      if (!isset($_POST["obrigatorio"])) { 
        echo "<strong>Erro: Insira a obrigatoriedade do campo.</strong><br>";
        gerar_link_voltar(); 
        die("");
    } else {
        $obrigatorio = 1; 
        if (test_input($_POST["obrigatorio"]) == "não") {
            $obrigatorio = 0;
        }
    }
    
    if (isset($_POST["tipo_de_unidade"])) {
      $tipo_unidade = test_input($_POST["tipo_de_unidade"]);
      if ($tipo_unidade == '') {
          $tipo_unidade = "-";
      } 
      else {
          $tipo_unidade = test_input($tipo_unidade);
      }
    } 
    else {
        $tipo_unidade = "-";
    }
    if(isset($nome_dosubitem)&&isset($tipo_devalor)&&isset($nome_doitem)&&isset($tipo_do_campoformulario)&&isset($ordem_do_campo_formulario)&&is_numeric($ordem_do_campo_formulario)&&isset($obrigatorio)){
      mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
      try {
        mysqli_begin_transaction($link, MYSQLI_TRANS_START_READ_WRITE);
        $query_inserir_formulario = sprintf(
            "INSERT INTO `subitem` 
            (`name`, `value_type`, `form_field_name`, `form_field_type`, `form_field_order`, `mandatory`, `unit_type_id`, `item_id`, `state`) 
            VALUES 
            ('%s', '%s', '', '%s', '%s', '%s', 
            (SELECT `id` FROM `subitem_unit_type` WHERE `name` = '%s'), 
            (SELECT `id` FROM `item` WHERE `name` = '%s'), 
            'active');",
            mysqli_real_escape_string($link, $nome_dosubitem),
            mysqli_real_escape_string($link, $tipo_devalor),
            mysqli_real_escape_string($link, $tipo_do_campoformulario),
            mysqli_real_escape_string($link, $ordem_do_campo_formulario),
            mysqli_real_escape_string($link, $obrigatorio),
            mysqli_real_escape_string($link, $tipo_unidade),
            mysqli_real_escape_string($link, $nome_doitem));
    
        $adicionar_formulario = mysqli_query($link, $query_inserir_formulario);

        if (!$adicionar_formulario) {
            throw new Exception("Erro ao inserir subitem: " . mysqli_error($link));
        }
    
        $id_novo_subitem = mysqli_insert_id($link);
        if (!$id_novo_subitem) {
            throw new Exception("Erro ao obter ID do novo subitem.");
        }
    
        $inicio_campo = substr($nome_doitem, 0, 3); 
        $nome_do_camp = $inicio_campo . '-' . $id_novo_subitem . '-' . $nome_dosubitem;
    
        $query_inserir_nome_campo = sprintf(
            "UPDATE `subitem` 
            SET `form_field_name` = '%s' 
            WHERE `id` = %d;",
            mysqli_real_escape_string($link, $nome_do_camp),
            $id_novo_subitem
        );
    
        $atualizar_formulario = mysqli_query($link, $query_inserir_nome_campo);
    
        if (!$atualizar_formulario) {
            throw new Exception("Erro ao atualizar subitem: " . mysqli_error($link));
        }
        mysqli_commit($link);
    
        } catch (Exception $exception) {
            mysqli_rollback($link);
        
            throw $exception;
        }
      }
    }
    if (isset($obrigatorio) &&!empty($ordem_do_campo_formulario) &&!empty($tipo_do_campoformulario) &&!empty($nome_doitem) &&!empty($nome_dosubitem)&&is_numeric($ordem_do_campo_formulario) &&isset($tipo_devalor)) 
    {
        echo '<h3>Gestão de subitens - inserção</h3>';
        echo '<h3>Inseriu os dados de novo subitem com sucesso.</h3>';
        echo "<form action='$current_page'>";
        echo '<button type="submit" id="InsertOrSimilar">Continuar</button>';
    } 
    else {
        gerar_link_voltar();
    }
  }
}
mysqli_close($link);
?>
