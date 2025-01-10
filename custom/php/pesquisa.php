<?php
include "common.php";

if(is_user_logged_in()){
    if (!current_user_can("search")){
        die(" Não tem autorização para aceder a esta página "); 
    }
}else{
    die(" Utilizador desconectado! ");
}

if(!isset($_REQUEST['estado'])){
    echo"<br>";
    echo"<strong>Pesquisa - escolher item </strong>";
    echo "<br>";
    echo "<ul>";

    $query_tipo_item = "
            SELECT id, name 
            FROM item_type";
    $result_tipo_item = $link->query($query_tipo_item);

    if ($result_tipo_item->num_rows > 0){
        while($tipo_item = $result_tipo_item->fetch_assoc()){
            $id_tipo_item = $tipo_item['id'];
            $nome_tipo_item = $tipo_item['name'];
            $query_item = "
                SELECT id, name 
                FROM item 
                WHERE item_type_id = $id_tipo_item AND state = 'active'";
            $result_item = $link->query($query_item);   

            if ($result_item->num_rows > 0) {
                echo "<li><strong>". $nome_tipo_item."</strong><br>";
                echo"<ul>";
                while ($item = $result_item->fetch_assoc()){
                    $id_item = $item['id'];
                    $nome_item = $item['name'];
                    $query_subitem = "
                        SELECT id
                        FROM subitem
                        WHERE item_id = $id_item";
                    $result_subitem = $link->query($query_subitem);
                    if($result_subitem->num_rows > 0){
                        $link_item = "pesquisa?estado=escolha&item=". $id_item;
                        echo "<li><a href=\"$link_item\">[". $nome_item. "]</a></li>";
                    }    
                }
                echo"</ul>";
                echo"</li>";
                echo"<br>";
            }
        }
    }
    echo "</ul>";
} elseif ($_REQUEST['estado'] == 'escolha') {
    $id_item = $_REQUEST['item'];
    $_SESSION['item_id'] = $id_item;

    $query_nome_item = "
        SELECT name 
        FROM item 
        WHERE id = $id_item";
    $result_nome_item = $link->query($query_nome_item);

    if ($result_nome_item->num_rows > 0) {
        $nome_item = $result_nome_item->fetch_assoc()['name'];
        $_SESSION['item_name'] = $nome_item;
    }

    $query_subitem = "
        SELECT id, name, value_type
        FROM subitem 
        WHERE item_id = $id_item";
    $result_subitem = $link->query($query_subitem);

    echo '<form method="post" action=" ">';
    echo "<table border='1'>";

    echo "<tr><th>Nome</th><th>Obter</th><th>Filtro</th></tr>";

    $atributos_child = ['id', 'nome', 'Data de Nascimento', 'Nome do Tutor', 'Telefone do Tutor', 'Email do Tutor'];
    foreach ($atributos_child as $atributo) {
        echo "<tr><td>$atributo</td><td><input type='checkbox' name='obter[]' value='$atributo'></td><td><input type='checkbox' name='filtro[]' value='$atributo'></td></tr>";
    }
    echo "</table>";

    echo "<table border='1'>";
    echo "<tr><th>Nome</th><th>Obter</th><th>Filtro</th></tr>";

    $erro_enum = false;
    if ($result_subitem->num_rows > 0) {
        while ($subitem = $result_subitem->fetch_assoc()) {
            $nome_subitem = $subitem['name'];
            $tipo_valor = $subitem['value_type'];
            echo "<tr>";
            echo "<td>$nome_subitem</td>";
            echo "<td><input type='checkbox' name='subitems[obter][]' value='$nome_subitem'></td>";
            echo "<td><input type='checkbox' name='subitems[filtro][]' value='$nome_subitem'></td>";
            echo "</tr>";
            if ($tipo_valor == 'enum') {
                $query_enum_values = "
                    SELECT value 
                    FROM subitem_allowed_value 
                    WHERE subitem_id = " . $subitem['id'];
                $result_enum_values = $link->query($query_enum_values);
                
                if ($result_enum_values->num_rows == 0) {
                    $erro_enum = true;
                    echo "<tr><td colspan='3' style='color: red;'>Este subitem não tem valores permitidos associados.</td></tr>";
                }
            }
        }
    }
    echo "</table>";

    echo "<input type='hidden' name='estado' value='escolher_filtros'>";
    echo "<button type='submit'id='InsertOrSimilar'>Submeter</button>";
    echo "</form>";
    gerar_link_voltar();

} elseif ($_REQUEST['estado'] == 'escolher_filtros') {

    echo"<span id='red'><strong>* Obrigatório</strong></span><br>";
    echo "<strong>Irá ser realizada uma pesquisa que irá obter, como resultado, uma listagem, para cada criança, dos seguintes dados pessoais escolhidos:</strong>";
    echo "<table border='1'><tr><th>Nome do Atributo</th><th>Operador <span id='red'><strong> * </strong></span></th><th>Valor <span id='red'><strong> * </strong></span></th></tr>";

    $obter = isset($_REQUEST['obter']) ? $_REQUEST['obter'] : [];
    $filtro = isset($_REQUEST['filtro']) ? $_REQUEST['filtro'] : [];

    $atributos_child = ['id', 'nome', 'Data de Nascimento', 'Nome do Tutor', 'Telefone do Tutor', 'Email do Tutor'];

    foreach ($atributos_child as $atributo) {
        if (in_array($atributo, $obter) && in_array($atributo, $filtro)) {
            echo "<tr><td>$atributo</td>";

            echo "<td><select name='filtro_{$atributo}_operador'>";
            $operadores = ['=' => '=', '!=' => '!='];
            foreach ($operadores as $op => $label) {
                echo "<option value='$op'>$label</option>";
            }
            echo "</select></td>";

            echo "<td><input type='text' name='filtro_{$atributo}_valor'></td>";
            echo "</tr>";
        }
    }

    echo "</table>";

    echo "<strong>e do item: " . $_SESSION['item_name'] . " uma listagem dos valores dos subitens:</strong>";
    echo "<table border='1'><tr><th>Nome do Subitem</th><th>Operador <span id='red'><strong> * </strong></span></th><th>Valor <span id='red'><strong> * </strong></span></th></tr>";

    $id_item = $_SESSION['item_id'];
    $query_subitem = "
        SELECT id, name, value_type
        FROM subitem
        WHERE item_id = $id_item";
    $result_subitem = $link->query($query_subitem);

    $subitems_obter = isset($_REQUEST['subitems']['obter']) ? $_REQUEST['subitems']['obter'] : [];
    $subitems_filtro = isset($_REQUEST['subitems']['filtro']) ? $_REQUEST['subitems']['filtro'] : [];

    if ($result_subitem->num_rows > 0) {
        while ($subitem = $result_subitem->fetch_assoc()) {
            $nome_subitem = $subitem['name'];
            $tipo_valor = $subitem['value_type'];

            if (in_array($nome_subitem, $subitems_obter) && in_array($nome_subitem, $subitems_filtro)) {
                echo "<tr><td>$nome_subitem</td>";

                echo "<td><select name='subitem_{$nome_subitem}_operador'>";
                if ($tipo_valor == 'text') {
                    echo "<option value='='>=</option><option value='!='>!=</option><option value='LIKE'>LIKE</option>";
                } elseif ($tipo_valor == 'int') {
                    echo "<option value='>'>></option><option value='>='>>=</option><option value='='>=</option>
                          <option value='<'><</option><option value='<='><=</option><option value='!='>!=</option>";                                   
                } elseif ($tipo_valor == 'double') {
                    echo "<option value='>'>></option><option value='>='>>=</option><option value='='>=</option>
                          <option value='<'><</option><option value='<='><=</option><option value='!='>!=</option>";                   
                } elseif ($tipo_valor == 'enum') {
                    echo "<option value='='>=</option><option value='!='>!=</option>";
                }
                echo "</select></td>";

                echo "<td><input type='text' name='subitem_{$nome_subitem}_valor'></td></tr>";
            }
        }
    }
    echo "</table>";

    echo "<input type='hidden' name='estado' value='execucao'>";
    echo "<button type='submit'id='InsertOrSimilar'>Submeter</button>";
    gerar_link_voltar();

}


$link->close();
?>