<?php
include "common.php";

if (is_user_logged_in()) {
    if (!current_user_can("manage_records")) {
        die(" Não tem autorização para aceder a esta página ");
    }
} else {
    die(" Utilizador desconectado! ");
}


$valor_ids=[];
if (!isset($_POST['estado'])) {
    $query_crianca = "
        SELECT id, name, birth_date, tutor_name, tutor_phone, tutor_email
        FROM child
        ORDER BY name
        ";
    $result_crianca = $link->query($query_crianca);
    if ($result_crianca->num_rows > 0) {
        echo '<table class="custom-table">';
        echo "<thead>";
        echo "<tr>";
        echo "<th>Nome</th>";
        echo "<th>Data de Nascimento</th>";
        echo "<th>Enc. de educação</th>";
        echo "<th>Telefone do Enc.</th>";
        echo "<th>e-mail</th>";
        echo "<th>Ação</th>";
        echo "<th>registos</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($crianca = $result_crianca->fetch_assoc()) {
            $id_crianca = $crianca['id'];

            echo "<tr>";
            echo "<td>" . $crianca['name'] . "</td>";
            echo "<td class='id_color'>" . $crianca['birth_date'] . "</td>";
            echo "<td>" . $crianca['tutor_name'] . "</td>";
            echo "<td class='id_color'>" . $crianca['tutor_phone'] . "</td>";
            echo "<td>" . $crianca['tutor_email'] . "</td>";
            echo "<td class='id_color'><span id='blue'>";
            echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=editar&tipo=registosacao&nome=' . $crianca['name'] . '&id=' . $id_crianca . '">[Editar]</a>';
            echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=apagar&tipo=registosacao&nome=' . $crianca['name'] . '&id=' . $id_crianca . '">[Apagar]</a>';
            echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=historico&tipo=registosacao&nome=' . $crianca['name'] . '&id=' . $id_crianca . '">[Historico]</a>';
            echo "<td></span>";

            $query_item = "
                SELECT DISTINCT item.id, item.name 
                FROM item
                JOIN subitem ON subitem.item_id = item.id
                JOIN value ON value.subitem_id = subitem.id
                WHERE value.child_id = $id_crianca
                ORDER BY item.name
            ";
            $result_item = $link->query($query_item);

            if ($result_item->num_rows > 0) {
                while ($item = $result_item->fetch_assoc()) {
                    $id_item = $item['id'];
                    $nome_item = $item['name'];

                    echo "<strong>" . strtoupper($nome_item) . ":</strong><br>";

                    $query_value_varia = "
                        SELECT DISTINCT value.date, value.time, value.producer
                        FROM value
                        JOIN subitem ON subitem.id = value.subitem_id
                        WHERE subitem.item_id = $id_item AND value.child_id = $id_crianca
                        ORDER BY value.date DESC, value.time DESC
                    ";
                    $result_value_varia = $link->query($query_value_varia);

                    if ($result_value_varia->num_rows > 0) {
                        while ($value_varia = $result_value_varia->fetch_assoc()) {
                            $data = $value_varia['date'];
                            $hora = $value_varia['time'];
                            $producer = $value_varia['producer'];

                            $resto_valores = "";
                            $valor_ids = [];
                            $query_resto_value = "
                                SELECT subitem.name, value.value, value.id AS value_id
                                FROM value
                                JOIN subitem ON subitem.id = value.subitem_id
                                WHERE value.child_id = $id_crianca 
                                  AND subitem.item_id = $id_item
                                  AND value.date = '$data' 
                                  AND value.time = '$hora'
                                ORDER BY subitem.name
                            ";

                            $result_resto_value = $link->query($query_resto_value);
                            
                            if ($result_resto_value->num_rows > 0) {
                                while ($valores = $result_resto_value->fetch_assoc()) {
                                    $nome_subitem = $valores['name'];
                                    $valor = $valores['value'];
                                    if ($resto_valores != "") {
                                        $resto_valores .= "; ";
                                    }
                                    $resto_valores .= "$nome_subitem(<strong>$valor</strong>)";
                                    $valor_ids[] = $valores['value_id'];
                                }
                            }
                            $array_de_values = [];
                            foreach ($valor_ids as $indice_values => $value_id) {
                                $array_de_values[] = 'value' . ($indice_values + 1) . '=' . $value_id;
                            }

                            $valores_juntos = implode('&', $array_de_values);
                            echo '<span id="blue">';
                            echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=editar&tipo=registos&' . $valores_juntos . '">[Editar]</a> ';
                            echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=apagar&tipo=registos&' . $valores_juntos . '">[Apagar]</a>';
                            echo '<a href="' . get_site_url() . '/edicao-de-dados?estado=historico&tipo=registos&' . $valores_juntos . '">[Historico]</a>';
                            echo '</span>';
                            echo " - <strong>$data $hora</strong> ($producer) - $resto_valores<br>";
                            
                            
                        }
                    }
                }
            }

            echo "</td></tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo '<strong><span id="black">Histórico de Registos apagados: </span></strong>';
        echo '<span id="black"><a href="' . get_site_url() . '/edicao-de-dados?estado=historicoapagar&tipo=registos">Registos apagados</a></span><br>';
        echo '<strong><span id="black">Histórico de Ações de Registos apagados: </span></strong>';
        echo '<span id="black"><a href="' . get_site_url() . '/edicao-de-dados?estado=historicoapagar&tipo=registosacao">Ações de regitos apagados</a></span><br>';
    } else {
        echo "Não há crianças";
    }
    echo"<span id='black'><strong>Dados de registo - Introdução</strong></span>";
    echo"<br><br>";
    echo"<span id='red'><strong>* Obrigatório</strong></span>";
    echo"<br><br>";
    echo"<strong>Introduza os dados pessoais básicos da criança: </strong>";
    echo"<form method='post' action='$current_page'>";

    echo'<label for="nome"><strong><span id="black">Nome Completo: </strong></label></span>';
    echo"<span id='red'><strong> * </strong></span>";
    echo'<input type="text" id="nome" name="nome"><br>';

    echo'<label for="data_nascimento"><strong><span id="black">Data de nascimento (AAAA-MM-DD): </strong></label></span>';
    echo"<span id='red'><strong> * </strong></span>";
    echo'<input type="text" id="data_nascimento" name="data_nascimento">';

    echo'<label for="nome_enc_educacao"><strong><strong><span id="black">Nome completo do encarregado de educação: </strong></label></span>';
    echo"<span id='red'><strong> * </strong></span>";
    echo'<input type="text" id="nome_enc_educacao" name="nome_enc_educacao">';

    echo'<label for="telefone_enc_educacao"><strong><span id="black">Telefone do encarregado de educação(9 digitos): </strong></label></span>';
    echo"<span id='red'><strong> * </strong></span>";
    echo'<input type="text" id="telefone_enc_educacao" name="telefone_enc_educacao">';

    echo'<label for="email_tutor"><strong><strong><span id="black">Endereço de e-mail do tutor: </strong></label></span>';
    echo'<input type="text" id="email_tutor" name="email_tutor">';

    echo"<br>";
    echo'<input type="hidden" name="estado" value="validar">';
    echo'<button type="submit" id="InsertOrSimilar">Submeter</button>';
    echo'</form>';
}elseif($_POST['estado'] == 'validar'){
    echo"<strong><span id='black'>Dados de registo - validação</strong></span>";
    echo"<br><br>";
    $ERRO = [];

    $nome = test_input($_POST['nome']);
    $data_nascimento = test_input($_POST['data_nascimento']);
    $nome_enc_educacao = test_input($_POST['nome_enc_educacao']);
    $telefone_enc_educacao = test_input($_POST['telefone_enc_educacao']);
    $email_tutor = test_input($_POST['email_tutor']);

    if (empty($nome)){
        $ERRO[] = "Nome da é criança obrigatório!";
    } elseif (!preg_match("/^[A-Za-zÀ-ÿ\s]+$/", $nome)){
        $ERRO[] = "O nome da criança deve conter apenas letras e espaços!";
    }

    if (empty($data_nascimento)){
        $ERRO[] = "Data de nascimento da criança é obrigatório!";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data_nascimento)){
        $ERRO[] = "A data de nascimento deve estar no formato AAAA-MM-DD!";
    }

    if (empty($nome_enc_educacao)){
        $ERRO[] = "Nome do Encarregado de educação é obrigatório!";
    } elseif (!preg_match("/^[A-Za-zÀ-ÿ\s]+$/", $nome_enc_educacao)){
        $ERRO[] = "O nome do encarregado de educação deve conter apenas letras e espaços!";
    }

    if (empty($telefone_enc_educacao)){
        $ERRO[] = "Telefone do Encarregado de educação é obrigatório!";
    } elseif (!preg_match("/^\d{9}$/", $telefone_enc_educacao)){
        $ERRO[] = "O telefone do Encarregado de Educação deve conter 9 dígitos!";
    }

    if (!empty($email_tutor) && !filter_var($email_tutor, FILTER_VALIDATE_EMAIL)) {
        $ERRO[] = "O endereço de e-mail do tutor é inválido!";
    }


    if(!empty($ERRO)){
        foreach($ERRO as $erro){
            echo"<strong>$erro</strong><br>";
        }

        echo"<form method='post' action='$current_page'>";
        echo'<input type="hidden" name="estado" value="validar">';
        echo'</form>';
        gerar_link_voltar();
    }else{
        echo"Estamos prestes a inserir os dados abaixo na base de dados.";
        echo"<br><br>";
        echo"Confirma que os dados estão corretos e pretende submeter os mesmos?";
        echo"<br><br>";
        echo"<strong><span id='black'> Nome: </span></strong>". test_input($nome). "";
        echo"<strong><span id='black'> Data de nascimento: </span></strong>". test_input($data_nascimento). "";
        echo"<strong><span id='black'> Enc. de educação: </span></strong>". test_input($nome_enc_educacao). "";
        echo"<strong><span id='black'> Telefone do Enc: </span></strong>". test_input($telefone_enc_educacao). "";
        echo"<strong><span id='black'> E-mail: </span></strong>". test_input($email_tutor). "";
        echo"<br>";

        echo"<form method='post' action='$current_page'>";
        echo'<input type="hidden" name="estado" value="inserir">';
        echo'<input type="hidden" name="nome" value="'. test_input($nome). '">';
        echo'<input type="hidden" name="data_nascimento" value="'. test_input($data_nascimento). '">';
        echo'<input type="hidden" name="nome_enc_educacao" value="'. test_input($nome_enc_educacao). '">';
        echo'<input type="hidden" name="telefone_enc_educacao" value="'. test_input($telefone_enc_educacao). '">';
        echo'<input type="hidden" name="email_tutor" value="'. test_input($email_tutor). '">';
        echo'<button type="submit" id="InsertOrSimilar">Submeter</button>';
        echo'</form>';
        echo"<br><br>";
        gerar_link_voltar();
    }
}elseif($_POST['estado'] == 'inserir'){

    $nome = $link->real_escape_string($_POST['nome']);
    $data_nascimento = $link->real_escape_string($_POST['data_nascimento']);
    $nome_enc_educacao = $link->real_escape_string($_POST['nome_enc_educacao']);
    $telefone_enc_educacao = $link->real_escape_string($_POST['telefone_enc_educacao']);
    $email_tutor = $link->real_escape_string($_POST['email_tutor']);

    $ERRO = [];

    if (empty($nome)) {
        $ERRO[] = "Nome da criança é obrigatório!";
    } elseif (!preg_match("/^[A-Za-zÀ-ÿ\s]+$/", $nome)) {
        $ERRO[] = "O nome da criança deve conter apenas letras e espaços!";
    }

    if (empty($data_nascimento)) {
        $ERRO[] = "Data de nascimento da criança é obrigatório!";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data_nascimento)) {
        $ERRO[] = "A data de nascimento deve estar no formato AAAA-MM-DD!";
    }

    if (empty($nome_enc_educacao)) {
        $ERRO[] = "Nome do Encarregado de educação é obrigatório!";
    } elseif (!preg_match("/^[A-Za-zÀ-ÿ\s]+$/", $nome_enc_educacao)) {
        $ERRO[] = "O nome do encarregado de educação deve conter apenas letras e espaços!";
    }

    if (empty($telefone_enc_educacao)) {
        $ERRO[] = "Telefone do Encarregado de educação é obrigatório!";
    } elseif (!preg_match("/^\d{9}$/", $telefone_enc_educacao)) {
        $ERRO[] = "O telefone do Encarregado de Educação deve conter 9 dígitos!";
    }

    if (!empty($email_tutor) && !filter_var($email_tutor, FILTER_VALIDATE_EMAIL)) {
        $ERRO[] = "O endereço de e-mail do tutor é inválido!";
    }

    if (!empty($ERRO)) {
        foreach ($ERRO as $erro) {
            echo "<strong>$erro</strong><br>";
        }
        gerar_link_voltar();
    } else {
        $query_inserir_crianca = "
        INSERT INTO child (name, birth_date, tutor_name, tutor_phone, tutor_email)
        VALUES ('$nome', '$data_nascimento', '$nome_enc_educacao', '$telefone_enc_educacao', '$email_tutor')
        ";
        if($link->query($query_inserir_crianca) === TRUE){
            echo"<strong><span id='black'>Dados de registo - inserção</strong></span>";
            echo"<br><br>";
            echo"<strong><span id='black'>Inseriu os dados: </strong></span>";
            echo"<br><br>";
            echo"Nome: ". test_input($_POST['nome']). "<br>";
            echo"Data de Nascimento: ". test_input($_POST['data_nascimento']). "<br>";
            echo"Enc. de Educação: ". test_input($_POST['nome_enc_educacao']). "<br>";
            echo"Telefone do Enc.: ". test_input($_POST['telefone_enc_educacao']). "<br>";
            echo"E-mail: ". test_input($_POST['email_tutor']). "<br>";
            echo"<strong><span id='black'>Inseriu os dados de registo com sucesso. </strong></span><br>";
            echo"<strong><span id='black'>Clique em continuar para avançar. </strong></span><br>";
            echo"<form method='get' action='$current_page'>";
            echo'<button type="submit" id="InsertOrSimilar"> Continuar</button>';
            echo'</form>';
        }
    }
}
$link->close();
?>