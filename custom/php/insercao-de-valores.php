<?php
include "common.php";

if(is_user_logged_in()){
    if (!current_user_can("insert_values")){
        die(" Não tem autorização para aceder a esta página "); 
    }
}else{
    die(" Utilizador desconectado! ");
}

if(!isset($_REQUEST['estado']) || $_REQUEST['estado'] == 'procurar_crianca'){
    echo"<br><br>";
    echo"<strong>Inserção de valores - criança - procurar</strong>";
    echo"<br><br>";
    echo"<strong>Introduza um dos nomes da criança a encontrar e/ou a data de nascimento dela</strong>";
    echo"<br>";
    echo'<form method="post" action=" ">';
    echo'<label for="nome"><strong>Nome</strong></label>';
    echo'<input type="text" id="nome" name="nome"><br>';
    
    echo'<label for="data_nascimento"><strong>Data de nascimento - (no formato AAAA-MM-DD)</strong></label>';
    echo'<input type="text" id="data_nascimento" name="data_nascimento">';
    echo"<br>";
    echo'<input type="hidden" name="estado" value="escolher_crianca">';
    echo'<button type="submit" id="InsertOrSimilar">Submeter</button>';
    echo'</form>';
} elseif($_REQUEST['estado'] == 'escolher_crianca') {
    echo "<strong>Inserção de valores - criança - escolher</strong>";
    echo"<br>";

    $query_dados_crianca = "
        SELECT id, name, birth_date 
        FROM child";
    $valor_inserido = [];

    if (!empty($_REQUEST['nome'])) {
        $nome_crianca = $link->real_escape_string($_REQUEST['nome']);
        $valor_inserido[] = "name LIKE '%$nome_crianca%'";
    }

    if (!empty($_REQUEST['data_nascimento'])) {
        $data_nascimento_crianca = $link->real_escape_string($_REQUEST['data_nascimento']);
        $valor_inserido[] = "birth_date = '$data_nascimento_crianca'";
    }

    if(count($valor_inserido) > 0){
        $query_dados_crianca .= " WHERE ". implode(" AND ", $valor_inserido);
    }

    $result_dados_crianca = $link->query($query_dados_crianca);
    echo "<br>";

    if ($result_dados_crianca->num_rows > 0){
        while ($row = $result_dados_crianca->fetch_assoc()){
            $link_crianca = "insercao-de-valores?estado=escolher_item&crianca=" . $row["id"];
            echo "<a href=\"$link_crianca\">[". $row["name"]. "]</a>(". $row["birth_date"]. ") <br>"; 
        }
    } else {
        echo "0 resultados";
    }
    gerar_link_voltar();

} elseif($_REQUEST['estado'] == 'escolher_item') {
    echo "<strong>Inserção de valores - escolher item</strong>";
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
                        $link_item = "?estado=introducao&item=". $id_item;
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
    gerar_link_voltar();
    $_SESSION['child_id'] = $_REQUEST['crianca'];
} elseif ($_REQUEST['estado'] == 'introducao') {
    if (isset($_REQUEST['item'])) {
        $id_item = $_REQUEST['item'];
        $query_item = "
            SELECT name, item_type_id
            FROM item
            WHERE id = $id_item AND state = 'active'";
        $result_item = $link->query($query_item);
        if ($result_item->num_rows > 0){
            $item = $result_item->fetch_assoc();
            $nome_item = $item['name'];
            $id_item_type = $item['item_type_id'];

            $_SESSION['item_id'] = $id_item;
            $_SESSION['item_name'] = $nome_item;
            $_SESSION['item_type_id'] = $id_item_type;

            echo"<strong>Inserção de valores - ". htmlspecialchars($_SESSION['item_name']). "</strong>";
            echo"<br><br>";

            echo'<form name="item_type_'. $id_item_type. '_item_'. $id_item. '" method="post" action="?estado=validar&item='. $id_item.'">';
            echo"<input type ='hidden' name='estado' value='validar'>";
            echo"<span id='red'><strong>* Obrigatório</strong></span>";

            $query_subitens = "
                SELECT id, name, value_type, form_field_name, form_field_type, unit_type_id, form_field_order
                FROM subitem
                WHERE item_id = $id_item AND state = 'active'
                ORDER BY form_field_order
                ";
            $result_subitens = $link->query($query_subitens);

            if($result_subitens->num_rows > 0){
                while($subitem = $result_subitens->fetch_assoc()){
                    $id_subitem = $subitem['id'];
                    $nome_subitem = $subitem['name'];
                    $tipo_valor_subitem = $subitem['value_type'];
                    $nome_form_field = $subitem['form_field_name'];
                    $tipo_form_field = $subitem['form_field_type'];
                    $ordem_form_field = $subitem['form_field_order'];
                    $id_unit_type = $subitem['unit_type_id'];
                    
                    if($id_unit_type){
                        $query_subitem_unit_type = "
                            SELECT name
                            FROM subitem_unit_type
                            WHERE id = $id_unit_type
                            ";
                        $result_subitem_unit_type = $link->query($query_subitem_unit_type);
                        if($result_subitem_unit_type->num_rows > 0){
                            while($tipo_unidade = $result_subitem_unit_type->fetch_assoc()){
                                $nome_tipo_unidade = $tipo_unidade['name'];
                            }
                        }
                    }

                    echo "<br><strong>". htmlspecialchars($nome_subitem). "</strong>";
                    
                    $query_enum_values = "
                        SELECT value
                        FROM subitem_allowed_value
                        WHERE subitem_id = $id_subitem AND state = 'active'
                        ";
                    $result_enum_values = $link->query($query_enum_values);
                    $valores_enum = [];
                    while ($valor = $result_enum_values->fetch_assoc()){
                        $valores_enum[] = $valor['value'];
                    }
                    
                    switch($tipo_valor_subitem){
                        case'int':
                            echo"<span id='red'><strong> * </strong></span>";
                            echo"<input type='text' id='input_" . $nome_form_field . "_" . $id_subitem . "' name='" . $nome_form_field . "'>";
                            if (isset($nome_tipo_unidade) && $nome_tipo_unidade) {
                                echo "<strong>". test_input($nome_tipo_unidade). "</strong>";
                            }
                            break;

                        case'double':
                            echo"<span id='red'><strong> * </strong></span>";
                            echo"<input type='text' id='input_" . $nome_form_field . "_" . $id_subitem . "' name='" . $nome_form_field . "'>";
                            if (isset($nome_tipo_unidade) && $nome_tipo_unidade) {
                                echo "<strong>". test_input($nome_tipo_unidade). "</strong>";
                            }
                            break;

                        case'text':
                            if($tipo_form_field == 'textbox'){
                                echo"<span id='red'><strong> * </strong></span>";
                                echo"<textarea id='input_". $nome_form_field. "_". $id_subitem. "'name='". $nome_form_field. "'></textarea><br>";
                            }else{
                                echo"<span id='red'><strong> * </strong></span>";
                                echo "<input type='text' id='input_". $nome_form_field. "_". $id_subitem. "' name='". $nome_form_field. "'>";
                            }
                            break;

                        case 'bool':
                            echo "<div>";
                            echo"<span id='red'><strong> * </strong></span>";
                            echo "<input type='radio' id='bool_". $nome_form_field. "_1' name='". $nome_form_field. "' value='1'>";
                            echo "<label for='bool_". $nome_form_field. "_1'>Sim</label>";
                            echo "<input type='radio' id='bool_". $nome_form_field. "_0' name='". $nome_form_field. "' value='0'>";
                            echo "<label for='bool_". $nome_form_field. "_0'>Não</label>";
                            echo "</div><br>";
                            break;
                        
                        case'enum':
                            if (count($valores_enum) > 0) {
                                if ($tipo_form_field == 'selectbox'){
                                    echo"<span id='red'><strong> * </strong></span>";
                                    echo"<select id='". $nome_form_field. "' name='". $nome_form_field. "'>";
                                    foreach($valores_enum as $valor){
                                        echo "<option value= '". htmlspecialchars($valor). "'>". htmlspecialchars($valor). "</option>";
                                    }
                                    echo"</select><br>";
                                }elseif ($tipo_form_field == 'radio'){
                                    echo"<span id='red'><strong> * </strong></span>";
                                    echo "<div class='radio-group'>";
                                    foreach($valores_enum as $valor){
                                        echo "<input type='radio' id='radio_". $nome_form_field. "_". $id_subitem. "'name='". $nome_form_field. "'value='". htmlspecialchars($valor). "'>";
                                        echo "<label for='radio_". $nome_form_field. "_". $id_subitem. "'>". htmlspecialchars($valor). "</label><br>";
                                    }
                                    echo"</div>";
                                }elseif ($tipo_form_field == 'checkbox'){
                                    echo"<span id='red'><strong> * </strong></span>";
                                    echo "<div class='radio-group'>";
                                    foreach($valores_enum as $valor){
                                        echo "<input type='checkbox' id='checkbox_". $nome_form_field. "_". $id_subitem. "' name='". $nome_form_field. "[]' value='". htmlspecialchars($valor). "'>";
                                        echo "<label for='checkbox_". $nome_form_field. "_". $id_subitem. "'>". htmlspecialchars($valor). "</label><br>";
                                    }
                                    echo"</div>";
                                }
                            }
                            break;
                    }
                }
            }
            echo"<br>";
            echo'<button type="submit" id="InsertOrSimilar">Submeter</button>';
            echo'</form>';

        }
    }
    echo "<br>";
    gerar_link_voltar();
}elseif ($_REQUEST['estado'] == 'validar') {
    echo "<strong>Inserção de valores - " . htmlspecialchars($_SESSION['item_name']) . " - validar </strong><br>";
    $ERRO = [];

    $query_subitens = "
        SELECT id, name, value_type, form_field_name, form_field_type, form_field_order
        FROM subitem
        WHERE item_id = " . $_SESSION['item_id'] . " AND state = 'active'
        ORDER BY form_field_order";
    $result_subitens = $link->query($query_subitens);

    if ($result_subitens->num_rows > 0) {
        while ($subitem = $result_subitens->fetch_assoc()) {
            $nome_subitem = $subitem['name'];
            $nome_form_field = $subitem['form_field_name'];

            if (isset($_REQUEST[$nome_form_field])) {
                $campo_preenchido = trim($_REQUEST[$nome_form_field]);
            } else {
                $campo_preenchido = null;
            }

            if (empty($campo_preenchido)) {
                $ERRO[] = "O campo '$nome_subitem' é obrigatório.";
            }
        }
    }

    if (count($ERRO) > 0) {
        echo"<ul>";
        foreach ($ERRO as $erro) {
            echo "<li>$erro</li>";
        }
        echo"</ul>";
        echo"<strong>Por favor preencha os campos acima mencionados.</strong><br>";
        gerar_link_voltar();
    } else {
        echo"<ul>";

        $result_subitens = $link->query($query_subitens);
        while ($subitem = $result_subitens->fetch_assoc()) {
            $nome_subitem = $subitem['name'];
            $nome_form_field = $subitem['form_field_name'];
            $valor_campo = htmlspecialchars($_REQUEST[$nome_form_field]);

            echo "<li><strong>$nome_subitem: $valor_campo</li></strong>";
        }
        echo "</ul>";

        echo '<form method="post" action="?estado=inserir&item='. $_SESSION['item_id']. '">';
        $result_subitens = $link->query($query_subitens);
        while ($subitem = $result_subitens->fetch_assoc()) {
            $nome_form_field = $subitem['form_field_name'];
            $valor_campo = htmlspecialchars($_REQUEST[$nome_form_field]);
            echo "<input type='hidden' name='$nome_form_field' value='$valor_campo'>";
        }
        echo"<strong>Estamos prestes a inserir os dados abaixo na base de dados.</strong><br>";
        echo"<strong>Confirma que os dados estão corretos e pretende submeter os mesmos?</strong><br>";
        echo'<button type="submit" id="InsertOrSimilar">Submeter</button>';
        echo'</form>';

        echo"<br>";
        gerar_link_voltar();
    }
}elseif($_REQUEST['estado'] == 'inserir'){
    echo "<strong>Inserção de valores - " . htmlspecialchars($_SESSION['item_name']) . " - inserção </strong><br>";

    $query_subitens = "
        SELECT id, form_field_name
        FROM subitem
        WHERE item_id = " . $_SESSION['item_id'] . " AND state = 'active'";
    $result_subitens = $link->query($query_subitens);

    $user_name = wp_get_current_user()->user_login;
    $valores_inseridos = true;

    while ($subitem = $result_subitens->fetch_assoc()) {
        $id_subitem = $subitem['id'];
        $nome_form_field = $subitem['form_field_name'];
        $valor_campo = $link->real_escape_string($_REQUEST[$nome_form_field]);

        $query_inserir = "
        INSERT INTO `value` (child_id, subitem_id, value, date, time, producer)
        VALUES (". $_SESSION['child_id']. ", '$id_subitem', '$valor_campo', CURRENT_DATE(), CURRENT_TIME(), '$user_name')";

        if($link->query($query_inserir) !== TRUE){
            $valores_inseridos = false;
            echo "Erro ao inserir o valor para o campo '$nome_form_field'.<br>";
            echo "Erro: ". $link->error. "<br>";
        }
    }
    if ($valores_inseridos) {
        echo "<strong><span id='black'>Inseriu o(s) valor(es) com sucesso.</span></strong><br><br>";
        echo '<a id="InsertOrSimilar" href="?estado=procurar_crianca" >Voltar ao início</a> | ';
        echo '<a id="InsertOrSimilar" href="?estado=escolher_item&crianca='. $_SESSION['child_id']. '">Escolher item</a><br>';
    }
    echo "<br>";
    gerar_link_voltar();
}

$link->close();
?>