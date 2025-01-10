<?php
include 'common.php';
if (!is_user_logged_in()) {
    die("Utilizador desconectado!");
}

if (!current_user_can("manage_items")) {
    die("Não tem autorização para aceder a esta página");
}
$query_tipo_de_itens="SELECT name AS nome_do_tipo, id
            FROM item_type
            ORDER BY nome_do_tipo";
$tipo_de_itens = mysqli_query($link, $query_tipo_de_itens);

$n_itens = mysqli_num_rows($tipo_de_itens);
if($n_itens>0){
    if (!isset($_REQUEST['estado'])){
    echo "<table class='custom-table'>";
    echo"<thead>";
        echo "<tr>
                <th>tipo do item</th>
                <th>id</th>
                <th>nome do item</th>
                <th>estado</th>
                <th>ação</th>
            </tr>";
    echo"</thead>";
    while ($itens = mysqli_fetch_assoc($tipo_de_itens)) {
        $query_itens = "SELECT item.id, item.name AS nome, item.state AS estado 
                        FROM item 
                        WHERE item.item_type_id = " . $itens["id"] . " 
                        ORDER BY item.id";
        $resultado_itens = mysqli_query($link, $query_itens);
        $n_de_itens = mysqli_num_rows($resultado_itens);
        if ($n_de_itens > 0) {
            
            echo "<tr>";
            $nome_sem_underlines = str_replace('_', ' ', $itens['nome_do_tipo']);
            echo "<td rowspan='" . $n_de_itens . "'>" . $nome_sem_underlines . "</td>";
            
            
            while ($item = mysqli_fetch_assoc($resultado_itens)) {
                
                echo "<td class='id_color'>" . $item['id'] . "</td>";
                echo "<td >" . $item['nome'] . "</td>";
                echo "<td class='id_color'>" . $item['estado'] . "</td>";
                
                if ($item['estado'] == 'active') {
                    echo "<td>
                            <span id='blue'>";
                                echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=editar&tipo=itens&nome=' . $itens['nome_do_tipo'] . '&id=' . $item['id'] . '">[editar]</a>';
                                echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=desativar&tipo=itens&nome=' . $itens['nome_do_tipo'] . '&id=' . $item['id'] . '">[desativar]</a>';
                                echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=apagar&tipo=itens&nome=' . $itens['nome_do_tipo'] . '&id=' . $item['id'] . '">[apagar]</a>';
                                echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=historico&tipo=itens&nome=' . $itens['nome_do_tipo'] . '&id=' . $item['id'] . '">[historico]</a>
                                </span>
                        </td>';
                } 
                else {
                    echo "<td>
                            <span id='blue'>";
                                echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=editar&tipo=itens&nome=' . $itens['nome_do_tipo'] . '&id=' . $item['id'] . '">[editar]</a>';
                                echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=ativar&tipo=itens&nome=' . $itens['nome_do_tipo'] . '&id=' . $item['id'] . '">[ativar]</a>';
                                echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=apagar&tipo=itens&nome=' . $itens['nome_do_tipo'] . '&id=' . $item['id'] . '">[apagar]</a>';
                                echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=historico&tipo=itens&nome=' . $itens['nome_do_tipo'] . '&id=' . $item['id'] . '">[historico]</a>
                            </span>
                        </td>';
                }
                echo "</tr>"; 
                    
            }
        }
        else{
            
            echo "<tr><td>" . $itens["nome_do_tipo"] . "</td>";
            echo "<td id = 'NoArgumentsColor'colspan='4'><strong>Não existem itens para este tipo de item</strong></td></tr>";
            
        }
    }
        echo "</table>";
    echo '<strong><span id="black">Histórico de itens apagados: </span></strong>';
    echo '<span id="black"><a href="' . get_site_url() . '/edicao-de-dados?estado=historicoapagar&tipo=itens">Itens apagados</a></span>';
    echo "<h3><strong>Gestão de itens - introdução</strong></h3>";
    echo "<span id='red'><strong> * Obrigatório</strong></span></strong><br>";

    echo '<form method="post" action="">
    <span id="black"><strong>Nome:</strong></span>
    <span id="red"><strong>*</strong></span>
    <input type="text" name="nome_do_item"><br><br>

    <span id="black"><strong>Tipo:</strong></span>
    <span id="red"><strong>*</strong></span>';
    mysqli_data_seek($tipo_de_itens, 0);
    while ($itens = mysqli_fetch_assoc($tipo_de_itens)) {
        $item = test_input($itens['nome_do_tipo']); 
        $nome_sem_underlines = str_replace('_', ' ', $itens['nome_do_tipo']);
        echo '<input type="radio" name="tipo_do_item" value="' . test_input($item) . '"> ' . test_input($item);
    }
    

    echo '<span id="black"><strong> Estado:</strong></span>
    <span id="red"><strong>*</strong></span>';
    $estado = get_enum_values($link, "item", "state");
      if ($estado && is_array($estado)) {          
          foreach ($estado as $estados) {
            echo "<input type='radio' name='estado_do_item' value='" . test_input($estados) . "'> " . test_input($estados) ;
          }

      } 
      else {
          echo "Nenhum valor encontrado.";
      }

    echo  '<input type="hidden" name="estado" value="inserir">
    <button type="submit" id="InsertOrSimilar">Submeter</button><br>
    </form>';

    echo "</div>";
    } 
    else if ($_REQUEST['estado'] == 'inserir'){
        $nome_do_item=$tipo_do_item=$estado_do_item="";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nome_do_item = test_input($_POST["nome_do_item"]);
            if (!preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/", $nome_do_item)) {
                
                echo "Erro: O campo Nome deve conter apenas letras e espaços.<br>";
                gerar_link_voltar(); 
                die("");
            }
            if (isset($_POST["tipo_do_item"])) {
                $tipo_do_item = test_input($_POST["tipo_do_item"]);
            } 
            else {
                $tipo_do_item = '';
            }
            
            if (isset($_POST["estado_do_item"])) {
                $estado_do_item = test_input($_POST["estado_do_item"]);
            } 
            else {
                $estado_do_item = '';
            }

            if (empty($nome_do_item)) {
                echo '<strong>Erro: Insira o nome do item</strong><br>';
                gerar_link_voltar(); 
            }
            else if (empty($tipo_do_item)) {
                echo '<strong>Erro: Falta o tipo do item</strong><br>';
                gerar_link_voltar(); 
            }
            else if (empty($estado_do_item)) {
                echo '<strong>Erro: Falta o estado do item</strong><br>';
                gerar_link_voltar(); 
            } 
            else {
                try { 
                    $query_inserir_valores_introduzidos = sprintf(
                        
                        "INSERT INTO `item` (`name`, `item_type_id`, `state`) VALUES ('%s', (SELECT id FROM `item_type` WHERE `name` = '%s'), '%s');",
                        mysqli_real_escape_string($link, $nome_do_item),
                        mysqli_real_escape_string($link, $tipo_do_item),
                        mysqli_real_escape_string($link, $estado_do_item)
                    );
                    
                    mysqli_query($link,$query_inserir_valores_introduzidos);
                    mysqli_commit($link); 
                }
                   catch (mysqli_sql_exception $exception) {
                    mysqli_rollback($link); 
                    throw $exception;
                }

                echo'<h3 >Gestão de itens - inserção</h3>';
                echo'<h3 >Inseriu os dados de novo item com sucesso.</h3>';
                echo "<form action='$current_page'>";
                echo '<button type="submit" id="InsertOrSimilar">Continuar</button>';
                            
            }
        }   
        
    }
}
else{
    echo "Não há itens";
}

mysqli_close($link);
?>
