<?php
include 'common.php';
include 'edicao-de-dados_aux.php';

if($_GET['tipo']=='itens'){
    if ($_GET['estado'] == 'historico') {
        $id_original = intval($_GET['id']);
        $query_original = sprintf(
            "SELECT * FROM item WHERE id = %d",
            $id_original
        );
        $resultado_original = mysqli_query($link, $query_original);
        if (!$resultado_original || mysqli_num_rows($resultado_original) === 0) {
            echo "Erro: Nenhum registro encontrado para o ID especificado.<br>";
            exit;
        }
        $original = mysqli_fetch_assoc($resultado_original);

        $query_historico = sprintf("SELECT * 
                                    FROM item_h 
                                    WHERE id_original = %d 
                                    ORDER BY selotemporal ASC",
            $id_original
        );
        $resultado_historico = mysqli_query($link, $query_historico);

        echo "<h3>Histórico do item ".$original['name']."</h3>";
        echo "<table class='custom-table'>";
        echo "<tr>";
        echo "<thead><th>ID Original</th>
            <th>Operação</th>
            <th>TimeStamp</th>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Estado</th></thead>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>" . test_input($original['id']) . "</td>";
        echo "<td class='id_color'></td><td></td>";
        echo "<td class='id_color'>" . test_input($original['name'] ?? '') . "</td>";
        echo "<td>" . test_input($original['item_type_id'] ?? '') . "</td>";
        echo "<td class='id_color'>" . test_input($original['state'] ?? '') . "</td>";
        echo "</tr>";

        if ($resultado_historico) {
            while ($historico = mysqli_fetch_assoc($resultado_historico)) {
                echo "<tr>";
                echo "<td>" . test_input($historico['id_original'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['operacao'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['selotemporal'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['name'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['item_type_id'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['state'] ?? '') . "</td>";
                echo "</tr>";
            }
        }
    
        echo "</table>";
        gerar_link_voltar();
        exit;
    }
    if ($_GET['estado'] == 'historicoapagar') {
        $query_historico_apagados = "SELECT * 
                                    FROM item_h 
                                    WHERE operacao = 'eliminacao' 
                                    ORDER BY selotemporal ASC";
        $resultado_historico_apagados = mysqli_query($link, $query_historico_apagados);
        if (!$resultado_historico_apagados) {
            echo "Erro: " . mysqli_error($link);
            exit;
        }
        echo "<h3>Histórico de itens apagados</h3>";
        echo "<table class='custom-table'>";
        echo "<tr>";
        echo "<thead><th>ID Original</th>
            <th>Operação</th>
            <th>TimeStamp</th>
            <th>Nome</th>
            <th>ID do Tipo</th>
            <th>Estado</th></thead>";
        echo "</tr>";
        while ($historico_apagados = mysqli_fetch_assoc($resultado_historico_apagados)) {
            echo "<tr>";
            echo "<td>" . test_input($historico_apagados['id_original'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['operacao'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['selotemporal'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['name'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['item_type_id'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['state'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        gerar_link_voltar();
    }
    if ($_GET['estado'] == 'editar') {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']);
        $id = intval($_GET['id']);
        if (!isset($_POST['item_atualizado'])) {
            $query_buscar_itens = "SELECT item.name AS nome_item, item.state AS estado, item.id AS id_item, item_type.name AS type_name
                                FROM item
                                JOIN item_type ON item.item_type_id = item_type.id
                                WHERE item.id = $id";
            $itens = mysqli_query($link, $query_buscar_itens);

            echo "<form method='POST' action=''>";
            echo "<table class='custom-table'>";
            echo "<thead><tr>
                    <th>id</th>
                    <th>Nome do Item</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                </tr></thead>";

            while ($item = mysqli_fetch_assoc($itens)) {
                echo "<tr>";
                echo "<td>" . $item['id_item'] . "</td>";
                echo '<td class="id_color"><input type="text"  name="nome_item" value="' . htmlspecialchars(test_input($item['nome_item'])) . '" ></td>';
                echo '<td>';
                

                $query_tipo_options = "SELECT DISTINCT name 
                                        FROM item_type";
                $resultado_tipo_options = mysqli_query($link, $query_tipo_options);
                
                echo '<select name="tipo">';
                while ($tipo_de_item = mysqli_fetch_assoc($resultado_tipo_options)) {
                    if($tipo_de_item['name']==$item['type_name']){
                        echo "<option value='" . test_input($tipo_de_item['name']) . "'selected>" . test_input($tipo_de_item['name']) . "</option>";
                        }else{
                        echo "<option value='" . test_input($tipo_de_item['name']) . "'>" . test_input($tipo_de_item['name']) . "</option>";
                    }
                }
                echo '</select></td>';
                echo "<td class='id_color'>" . test_input($item['estado']) . "</td></tr>";
            }

            echo "</table>";
            echo '<input type="hidden" name="item_atualizado" value="">';
            echo '<button type="submit" id="InsertOrSimilar" name="editar_item">Submeter</button><br>';
            gerar_link_voltar();
            echo "</form>";
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_item'])) {
        $id = intval($_GET['id']);
        $nome_item = test_input($_POST['nome_item']);
        $tipo = test_input($_POST['tipo']);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            if (empty($nome_item)) {
                echo "Erro: Nome do item não pode estar vazio.<br>";
                gerar_link_voltar(); 
                exit;
            }
            
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $nome_item)) {
                echo "Erro: Nome do item contém caracteres inválidos.<br>";
                gerar_link_voltar();
                exit; 
            }
            $query_tipo_id = "SELECT id 
                                FROM item_type 
                                WHERE name = '$tipo'";
            $resultado_tipo_id = mysqli_query($link, $query_tipo_id);

            if ($id_tipo_item = mysqli_fetch_assoc($resultado_tipo_id)) {
                $tipo_id = intval($id_tipo_item['id']);
            } else {
                echo "Erro: Tipo de item inválido.<br>";
                gerar_link_voltar();
                exit;
            }
            $query_dados_itens_antigos = "SELECT name, item_type_id
                                    FROM item 
                                    WHERE id = $id";
            $resultado_dados_itens_antigos = mysqli_query($link, $query_dados_itens_antigos);
            $dados_antigos = mysqli_fetch_assoc($resultado_dados_itens_antigos);
            $nome_antigo = $dados_antigos['name'];
            $id_tipo = $dados_antigos['item_type_id'];
            if ($nome_antigo != $nome_item) {
                $nome_historico = "'" . mysqli_real_escape_string($link, $nome_antigo) . "'";
            } else {
                $nome_historico = "NULL";
            }

            if ($id_tipo != $tipo_id) {
                $tipo_historico = "'" . mysqli_real_escape_string($link, $id_tipo) . "'";
            } else {
                $tipo_historico = "NULL";
            }
            if ($nome_antigo != $nome_item || $id_tipo != $tipo_id) {
                $query_historico = "INSERT INTO item_h (id_original, name, item_type_id, operacao, selotemporal) 
                                    VALUES ($id, $nome_historico , $tipo_historico, 'edicao', NOW())";
                mysqli_query($link, $query_historico);
            }
            $query_update = sprintf("UPDATE item 
                            SET name =  '%s' , item_type_id =  '%s' 
                            WHERE id =  '%s'",
                            mysqli_real_escape_string($link,$nome_item),
                            mysqli_real_escape_string($link,$tipo_id),
                            mysqli_real_escape_string($link,$id));

            if (!mysqli_query($link, $query_update)) {
                echo "Erro ao atualizar o item: " . mysqli_error($link)."<br>";
                gerar_link_voltar();
                exit;
            }
            
            mysqli_commit($link);
            if(mysqli_commit($link)){
                echo "<h3>Atualizações realizadas com sucesso!</h3>";
                echo "<a id='InsertOrSimilar' href='" . get_site_url() . "/gestao-de-itens/'>Continuar</a><br>";
                exit;
            }
        } catch (Exception $exception) {
            mysqli_rollback($link);
            throw $exception;
        }

        exit;
    }

    if (($_GET['estado']=='ativar'||$_GET['estado']=='desativar')) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_estado'])) {
            $id_item = intval($_POST['id']);
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
        try {
            mysqli_begin_transaction($link);
                $query_buscar_item = "SELECT * FROM item WHERE id = $id_item";
                $resultado_item = mysqli_query($link, $query_buscar_item);
                $item_original = mysqli_fetch_assoc($resultado_item);
                if ($_POST['estado'] == 'ativar') {
                    $query_historico_ativar = sprintf(
                        "INSERT INTO item_h (id_original, name, item_type_id, operacao, selotemporal,state)
                        VALUES (%d, NULL, NULL, 'ativacao', NOW(),'inactive')",
                        $id_item
                    );
                    mysqli_query($link, $query_historico_ativar);
                    $novo_estado = 'active';
                } else {
                    $query_historico_desativar = sprintf(
                        "INSERT INTO item_h (id_original, name, item_type_id, operacao, selotemporal,state)
                        VALUES (%d, NULL, NULL, 'desativacao', NOW(),'active')",
                        $id_item
                    );
                    mysqli_query($link, $query_historico_desativar);
                    $novo_estado = 'inactive';
                } 
            
                $query_atualizar_estado_ativar = sprintf(
                    "UPDATE item 
                    SET state = '%s' 
                    WHERE id = %d",
                    $novo_estado,
                    $id_item
                );
            
                if (mysqli_query($link, $query_atualizar_estado_ativar)) {
                    mysqli_commit($link);
                    echo "<h3>Atualização realizada com sucesso!</h3><br>";
                    echo "<a id='InsertOrSimilar' href='". get_site_url() ."/gestao-de-itens/'>Continuar</a>";
                } else {
                    throw new Exception("Erro ao atualizar o estado: " . mysqli_error($link));
                }
            } catch (Exception $e) {
                mysqli_rollback($link); 
                echo "Erro: " . $e->getMessage();
            }
            exit;

        }
        $estado = $_GET['estado'];
        $tipo = htmlspecialchars($_GET['tipo']); 
        $id = intval($_GET['id']);
        $query_buscar_itens="SELECT item.name AS nome_item, item.state AS estado, item.id AS id_item, item_type.name AS type_name
                            FROM item
                            JOIN item_type ON item.item_type_id = item_type.id
                            WHERE item.id = " . $id;
        $itens = mysqli_query($link, $query_buscar_itens);
        if($_GET['estado'] == 'ativar'){
            echo "<strong>Pretende ativar o item?</strong>";
        }else{
            echo "<strong>Pretende desativar o item?</strong>";
        }
        
        echo "<table class='custom-table'>";
        echo "<thead><tr>
                    <th>id</th>
                    <th>name</th>
                    <th>name</th>
                    <th>state</th>
                </tr></thead>";
        while($item=mysqli_fetch_assoc($itens)){
        echo "<tr>
                <td>".$item['id_item']."</td>
                <td class='id_color'>".$item['nome_item']."</td>
                <td>".$item['type_name']."</td>
                <td class='id_color'>".$item['estado']."</td>
            </tr>";
        }
        echo"</table>";
        echo '<form method="post" action="">
                <input type="hidden" name="id" value="'.$id.'">
                <input type="hidden" name="estado" value="'.$estado.'">
                <button type="submit" id="InsertOrSimilar" name="alterar_estado">Submeter</button>
            </form>';
        gerar_link_voltar();
    }


    if ($_GET['estado'] == "apagar" ) {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']); 
        $id = intval($_GET['id']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_estado'])) {
            $id_item = intval($_POST['id']);
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                mysqli_begin_transaction($link);
                $query_buscar_item = sprintf("SELECT name, item_type_id, state FROM item WHERE id = %d", $id_item);
                $resultado_item = mysqli_query($link, $query_buscar_item);
                $item = mysqli_fetch_assoc($resultado_item);

                if (!$item) {
                    echo "Erro: O item não existe.";
                    exit;
                }

                $nome_item = mysqli_real_escape_string($link, $item['name']);
                $tipo_item = $item['item_type_id'];
                $estado_item = $item['state'];
                
                apagarItem($link, $id_item, $nome_item, $tipo_item, $estado_item);

                mysqli_commit($link);
                echo "<h3><strong><span id='black'>Item apagado com sucesso!</span></strong></h3>";
                echo "<a id='InsertOrSimilar' href='". get_site_url() ."/gestao-de-itens/'>Continuar</a>";
                exit;

            } catch (Exception $exception) {
                mysqli_rollback($link);
                throw $exception;
            }
            echo "<a href='". get_site_url() ."/gestao-de-itens/'>";
            exit;
        }
            echo "<strong>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</strong><br>";
            echo "<table class = 'custom-table'>";
            echo "<thead><tr>
                    <th>id</th>
                    <th>name</th>
                    <th>name</th>
                    <th>state</th>
                </tr></thead>";

            $query_buscar_itens = "SELECT item.name AS nome_item, item.state AS estado, item.id AS id_item, item_type.name AS type_name
                                FROM item
                                JOIN item_type ON item.item_type_id = item_type.id
                                WHERE item.id = $id";
            $itens = mysqli_query($link, $query_buscar_itens);

            while ($item = mysqli_fetch_assoc($itens)) {
                echo "<tr><td>".$item['id_item']."</td>
                    <td class='id_color'>".$item['nome_item']."</td>
                    <td>".$item['type_name']."</td>
                    <td class='id_color'>".$item['estado']."</td></tr>";
            }
            echo "</table>";

            echo '<form method="post" action="">
                    <input type="hidden" name="id" value="'.$id.'">
                    <input type="hidden" name="estado" value="'.$estado.'">
                    <button type="submit" id="InsertOrSimilar" name="alterar_estado">Submeter</button>
                </form>';

            gerar_link_voltar();
        
    } else {
        echo "<a href='". get_site_url() ."/gestao-de-itens/'>";
        exit;
    }
}else if ($_GET['tipo']=='subitens'){
    if ($_GET['estado'] == 'historico') {
        $id_original = intval($_GET['id']);

        $query_original = sprintf(
            "SELECT * FROM subitem WHERE id = %d",
            $id_original
        );

        $resultado_original = mysqli_query($link, $query_original);
        if (!$resultado_original || mysqli_num_rows($resultado_original) === 0) {
            echo "Erro: Nenhum registro encontrado para o ID especificado.<br>";
            exit;
        }

        $original = mysqli_fetch_assoc($resultado_original);

        $query_historico = sprintf(
            "SELECT * FROM subitem_h WHERE id_original = %d ORDER BY selotemporal ASC",
            $id_original
        );
        $resultado_historico = mysqli_query($link, $query_historico);


        echo "<h3>Histórico do subitem ". $original['name']."</h3>";
        echo "<table class='custom-table'>";
        echo "<tr><thead>";
        echo "<th>ID Original</th>
                <th>Operação</th>
                <th>Timestamp</th>
                <th>Nome</th>
                <th>Item ID</th>
                <th>Value Type</th>
                <th>Form Field Name</th>
                <th>Form Field Type</th>
                <th>Unit Type ID</th>
                <th>Form Field Order</th>
                <th>Mandatory</th>
                <th>State</th>";
        echo "</thead></tr>";

        echo "<tr>";
        echo "<td>" . test_input($original['id']) . "</td>";
        echo "<td class='id_color'></td><td></td>"; 
        echo "<td class='id_color'>" . test_input($original['name'] ?? '') . "</td>";
        echo "<td>" . test_input($original['item_id'] ?? '') . "</td>";
        echo "<td class='id_color'>" . test_input($original['value_type'] ?? '') . "</td>";
        echo "<td>" . test_input($original['form_field_name'] ?? '') . "</td>";
        echo "<td class='id_color'>" . test_input($original['form_field_type'] ?? '') . "</td>";
        echo "<td>" . test_input($original['unit_type_id'] ?? '') . "</td>";
        echo "<td class='id_color'>" . test_input($original['form_field_order'] ?? '') . "</td>";
        echo "<td>" . test_input($original['mandatory'] ?? '') . "</td>";
        echo "<td class='id_color'>" . test_input($original['state'] ?? '') . "</td>";
        echo "</tr>";


        if ($resultado_historico) {
            while ($historico = mysqli_fetch_assoc($resultado_historico)) {
                echo "<tr>";
                echo "<td>" . test_input($historico['id_original'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['operacao'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['selotemporal'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['name'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['item_id'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['value_type'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['form_field_name'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['form_field_type'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['unit_type_id'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['form_field_order'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['mandatory'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['state'] ?? '') . "</td>";
                echo "</tr>";
            }
        }

        echo "</table>";
        gerar_link_voltar();
        exit;
    }
    if($_GET['estado']=='historicoapagar'){
        $query_historico_apagados = "SELECT * 
                                    FROM subitem_h 
                                    WHERE operacao = 'eliminacao' 
                                    ORDER BY selotemporal ASC";
        $resultado_historico_apagados = mysqli_query($link, $query_historico_apagados);
        if (!$resultado_historico_apagados) {
            echo "Erro: " . mysqli_error($link);
            exit;
        }
        echo "<h3>Histórico de subitens apagados</h3>";
        echo "<table class='custom-table'>";
        echo "<tr>";
        echo "<thead><th>ID Original</th>
            <th>Operação</th>
            <th>TimeStamp</th>
            <th>Nome</th>
            <th>Item ID</th>
            <th>Value Type</th>
            <th>Form Field Name</th>
            <th>Form Field Type</th>
            <th>Unit Type ID</th>
            <th>Form Field Order</th>
            <th>Mandatory</th>
            <th>State</th></thead>";
        echo "</tr>";
        while ($historico_apagados = mysqli_fetch_assoc($resultado_historico_apagados)) {
            echo "<tr>";
            echo "<td>" . test_input($historico_apagados['id_original'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['operacao'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['selotemporal'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['name'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['item_id'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['value_type'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['form_field_name'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['form_field_type'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['unit_type_id'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['form_field_order'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['mandatory'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['state'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        gerar_link_voltar();
    }
    if (($_GET['estado']=='ativar'||$_GET['estado']=='desativar')) {
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_estado'])) {
            $id = intval($_POST['id']);

            if ($_POST['estado'] == 'ativar') {
                $novo_estado = 'active';
            } else {
                $novo_estado = 'inactive';
            }
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            mysqli_begin_transaction( $link, MYSQLI_TRANS_START_READ_WRITE );
            try {
                if($novo_estado=='inactive'){
                    $operacao='ativacao';
                }else{
                    $operacao='desativacao';
                }
                $query_historico = sprintf(
                    "INSERT INTO subitem_h (state, operacao, selotemporal, id_original) VALUES ('%s', '%s', NOW(), %d)",
                    mysqli_real_escape_string($link, $novo_estado),
                    mysqli_real_escape_string($link, $operacao),  
                    $id
                );
                mysqli_query($link, $query_historico);
            
                $query_update_subitem = sprintf("UPDATE subitem 
                                    SET state = '%s'  
                                    WHERE id = %d", 
                    mysqli_real_escape_string($link, $novo_estado),
                    mysqli_real_escape_string($link, $id)
                );
                mysqli_query($link, $query_update_subitem);
                mysqli_commit($link);
                if (mysqli_commit($link)) {
                    echo "<h3>Atualização realizada com sucesso!</h3><br>";
                    echo "<a id='InsertOrSimilar' href='". get_site_url() ."/gestao-de-subitens/'>Continuar</a>";
                } else {
                    echo "<strong>Erro ao atualizar: " . mysqli_error($link) . "</strong>";
                }
            } catch (Exception $exception) {
                mysqli_rollback($link);
                throw $exception;
            }
    
            exit;

        }
        $estado = $_GET['estado'];
        $tipo = htmlspecialchars($_GET['tipo']); 
        $id = intval($_GET['id']);
        if($estado == 'ativar'){
            echo "<strong>Pretende ativar o item?</strong>";
        }else{
            echo "<strong>Pretende desativar o item?</strong>";
        }
        
        $query_subitens = "SELECT subitem.state AS estado, subitem.form_field_order AS ordemsbitens,subitem.form_field_type AS tipo_do_campo,subitem.form_field_name AS nome_do_campo,subitem.value_type AS tipo_valor,subitem.name AS nome_sb,subitem.id AS id_subitem,subitem.mandatory AS obrigação, item_id, unit_type_id
                                FROM subitem
                                WHERE subitem.id = " . $id;
        $subitens = mysqli_query($link, $query_subitens);
        if (!$subitens) {
            echo "Erro na consulta SQL: " . mysqli_error($link);
        }
        echo "<table class='custom-table'>";
        echo "<thead><tr>
                    <th>id</th>
                    <th>name</th>
                    <th>item_id</th>
                    <th>form_field_name</th>
                    <th>form_field_type</th>
                    <th>unit_type_id</th>
                    <th>form_field_order</th>
                    <th>mandatory</th>
                    <th>state</th>
                </tr></thead>";
        while ($sbitens = mysqli_fetch_assoc($subitens)){
            echo "<tr>
                <td>" . $sbitens['id_subitem'] . "</td>
                <td class='id_color'>" . $sbitens['nome_sb'] . "</td>
                <td>" . $sbitens['item_id'] . "</td>
                <td class='id_color'>" . $sbitens['nome_do_campo'] . "</td>
                <td>" . $sbitens['tipo_do_campo'] . "</td>
                <td class='id_color'>" . $sbitens['unit_type_id'] . "</td>
                <td>" . $sbitens['ordemsbitens'] . "</td>
                <td class='id_color'>" . $sbitens['obrigação'] . "</td>
                <td>" . $sbitens['estado'] . "</td>
            </tr>";
        }
        echo "</table>";
        
        
        echo '<form method="post" action="">
                <input type="hidden" name="id" value="'.$id.'">
                <input type="hidden" name="estado" value="'.$estado.'">
                <button type="submit" id="InsertOrSimilar" name="alterar_estado">Submeter</button>
            </form>';
        gerar_link_voltar();
    }
    

    if ($_GET['estado'] == 'editar') {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']);
        $id = intval($_GET['id']);
        
        if (!isset($_POST['subitem_atualizado'])) {
            $query_buscar_subitens = "SELECT subitem.state AS estado, subitem.form_field_order AS ordemsbitens,subitem.form_field_type AS tipo_do_campo,subitem.form_field_name AS nome_do_campo,subitem.value_type AS tipo_valor,subitem.name AS nome_sb,subitem.id AS id_subitem,subitem.mandatory AS obrigação, item_id, unit_type_id, value_type,item.name
                                FROM subitem
                                JOIN item ON subitem.item_id = item.id
                                WHERE subitem.id = " . $id;
            $subitens = mysqli_query($link, $query_buscar_subitens);
            
            echo "<form method='POST' action=''>";
            echo "<table class='custom-table'>";
            echo "<thead><tr>
                    <th>id</th>
                    <th>nome do subitem</th>
                    <th>nome do item</th>
                    <th>value_type</th>
                    <th>form_field_name</th>
                    <th>form_field_type</th>
                    <th>tipo de unidade</th>
                    <th>form_field_order</th>
                    <th>mandatory</th>
                    <th>state</th>
                </tr></thead>";

            while ($subitem = mysqli_fetch_assoc($subitens)) {

                echo "<tr>";
                echo "<td>" . $subitem['id_subitem'] . "</td>";
                echo '<td class="id_color"><input type="text" name="nome_subitem" value="' . test_input($subitem['nome_sb']) . '" ></td>';
                echo '<td>';
                

                $query_item_id = "SELECT name 
                                    FROM item
                                    ORDER BY name";
                $resultado_item_nome = mysqli_query($link, $query_item_id);
                $item_nomes = [];
                while ($item = mysqli_fetch_assoc($resultado_item_nome)) {
                    $item_nomes[] = $item['name'];
                }
                echo '<select name="item_name">';
                foreach ($item_nomes as $item_nome) {
                    if (test_input($item_nome) == test_input($subitem['name'])) {
                        echo "<option value='$item_nome' selected>$item_nome</option>";
                    }else{
                        echo "<option value='$item_nome'>$item_nome</option>";
                    }
                }
                echo '</select></td>';
                echo "<td class='id_color'>" . $subitem['value_type'] . "</td>";
                echo "<td>" . $subitem['nome_do_campo'] . "</td>";
                echo "<td class='id_color'>" . $subitem['tipo_do_campo'] . "</td>";
                echo '<td>';
                $query_unit_name = "SELECT DISTINCT name,id 
                                    FROM subitem_unit_type";
                $resultado_unit_name = mysqli_query($link, $query_unit_name);
                echo '<select name="unit_name">';
                if ($subitem["unit_type_id"] == NULL) {
                    echo '<option value="" selected>Selecione a opção</option>';
                } else {
                    echo '<option value="">Selecione a opção</option>';
                }

                while ($nome_unidade = mysqli_fetch_assoc($resultado_unit_name)) {
                    $name = $nome_unidade['name'];

                    if ($nome_unidade['id'] == $subitem["unit_type_id"]) {
                        echo '<option value="' . test_input($name) . '" selected>' . test_input($name) . '</option>';
                    } else {
                        echo '<option value="' . test_input($name) . '">' . test_input($name) . '</option>';
                    }
                }

                echo '</select>';
                echo '<td class="id_color"><input type="text" name="form_field_order" value="' . test_input($subitem['ordemsbitens']) . '" ></td>';
                echo '<td>';
                $query_madatory="SELECT mandatory 
                                    FROM subitem 
                                    WHERE subitem.id =".$id;
                $obrig = mysqli_query($link, $query_madatory);
                $resultado= mysqli_fetch_assoc($obrig);
                $obrigatorio = $resultado['mandatory'];
                echo '<select name="obrigatorio">';
                if ($obrigatorio == 0) {
                    echo "<option value='Não'selected>Não</option>";
                    echo "<option value='Sim'>Sim</option>";
                    
                } else {
                    echo "<option value='Sim'selected>Sim</option>";
                    echo "<option value='Não'>Não</option>";  
                }
                echo '</select>';
                echo "<td class='id_color'>" . $subitem['estado'] . "</td>";
                echo "</tr>";
            }

            echo "</table>";
            echo '<input type="hidden" name="subitem_atualizado" value="">';
            echo '<button type="submit" id="InsertOrSimilar" name="editar_subitem">Submeter</button><br>';
            gerar_link_voltar();
            echo "</form>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_subitem'])) {
        $nome_subitem = test_input($_POST['nome_subitem']);
        $item_nome = test_input($_POST['item_name']);
        $obrigatorio = test_input($_POST['obrigatorio']);
        $form_field_order=test_input($_POST['form_field_order']);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction( $link, MYSQLI_TRANS_START_READ_WRITE );
        try {
            
            if (empty($nome_subitem)) {
                echo "<strong>Erro: Nome do subitem não pode estar vazio.</strong><br>";
                gerar_link_voltar(); 
                exit;
            }
            
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $nome_subitem)) {
                echo "<strong>Erro: Nome do subitem contém caracteres inválidos.</strong><br>";
                gerar_link_voltar();
                exit; 
            }
            if (empty($form_field_order)) {
                echo "<strong>Erro: Ordem do campo não pode estar vazio.</strong><br>";
                gerar_link_voltar(); 
                exit;
            }
            
            if (!preg_match('/^\d+$/', $form_field_order)) {
                echo "<strong>Erro: Ordem do campo pode conter apenas números</strong><br>";
                gerar_link_voltar();
                exit; 
            }
            if ($obrigatorio == 'Sim') {
                $obrigatorio_valor = 1;
            } else {
                $obrigatorio_valor = 0;
            }
            $id = intval($_GET['id']);
            $query_item= sprintf("SELECT item.id 
                            FROM item
                            WHERE item.name = '" . $item_nome . "'");

            $resultado_item = mysqli_query($link, $query_item);

            $nome_do_camp = '';
            $inicio_campo = substr($item_nome, 0, 3); 

            while ($item = mysqli_fetch_assoc($resultado_item)) {
                $id_do_item = $item['id'];
                $nome_do_camp = $inicio_campo . '-' . $id_do_item . '-' . $nome_subitem;
            }
            $query_subantigos = "SELECT subitem.id AS id_subitem, subitem.name AS nome_subitem, subitem.item_id AS id_item, subitem.unit_type_id AS id_unidade, subitem.mandatory AS obrigatorio, subitem.form_field_order AS ordem_campo, subitem.form_field_name AS nome_campo
                                FROM subitem
                                WHERE subitem.id = $id";
            $resultado_subantigos = mysqli_query($link, $query_subantigos);
            $dados_antigos = mysqli_fetch_assoc($resultado_subantigos);

            $nome_antigo = $dados_antigos['nome_subitem'];
            $id_item_antigo = $dados_antigos['id_item'];
            $id_unidade_antigo = $dados_antigos['id_unidade'];
            $obrigatorio_antigo = $dados_antigos['obrigatorio'];
            $ordem_campo_antigo = $dados_antigos['ordem_campo'];
            $nome_campo_antigo = $dados_antigos['nome_campo'];

            if ($nome_antigo != $nome_subitem) {
                $nome_historico = "'" . mysqli_real_escape_string($link, $nome_antigo) . "'";
            } else {
                $nome_historico = "NULL";
            }

            if ($id_item_antigo != $id_do_item) {
                $item_historico = "'" . mysqli_real_escape_string($link, $id_item_antigo) . "'";
            } else {
                $item_historico = "NULL";
            }
            
            if ($obrigatorio_antigo != $obrigatorio_valor) {
                $obrigatorio_historico = "'" . mysqli_real_escape_string($link, $obrigatorio_antigo) . "'";
            } else {
                $obrigatorio_historico = "NULL";
            }

            if ($ordem_campo_antigo != $form_field_order) {
                $ordem_historico = "'" . mysqli_real_escape_string($link, $ordem_campo_antigo) . "'";
            } else {
                $ordem_historico = "NULL";
            }

            if ($nome_campo_antigo != $nome_do_camp) {
                $campo_historico = "'" . mysqli_real_escape_string($link, $nome_campo_antigo) . "'";
            } else {
                $campo_historico = "NULL";
            }

            if (!isset($_POST['unit_name']) || $_POST['unit_name'] === "Selecione a opção" || trim($_POST['unit_name']) === "") {
                $unit_name = "Selecione a opção"; 
            } else {
                $unit_name =  $_POST['unit_name'];
            }
            $query_unidade= sprintf("SELECT name,id 
                            FROM subitem_unit_type
                            WHERE subitem_unit_type.name = '" . $unit_name . "'");
            $pesquisa_id_unidade = mysqli_query($link, $query_unidade);  
            $unidade=mysqli_fetch_assoc($pesquisa_id_unidade);
            if($unidade!=NULL){
                $id_da_unidade = $unidade['id'];
            }else{
                $id_da_unidade = NULL;
            }
            if ($id_unidade_antigo != $id_da_unidade) {
                $unidade_historico = "'" . mysqli_real_escape_string($link, $id_unidade_antigo) . "'";
            } else {
                $id_da_unidade = "NULL";
                $unidade_historico = "NULL";
            }
            
            if ($nome_antigo != $nome_subitem || $id_item_antigo != $id_do_item || $id_unidade_antigo != $id_da_unidade || $obrigatorio_antigo != $obrigatorio_valor || $ordem_campo_antigo != $form_field_order || $nome_campo_antigo != $nome_do_camp) {
                $query_historico = sprintf(
                    "INSERT INTO subitem_h (id_original, name, item_id, unit_type_id, mandatory, form_field_order, form_field_name, operacao, selotemporal, value_type, form_field_type) 
                    VALUES (%d, %s, %s, %s, %s, %s, %s, 'edicao', NOW(),NULL,NULL)",
                    $id,
                    $nome_historico,
                    $item_historico,
                    $unidade_historico,
                    $obrigatorio_historico,
                    $ordem_historico,
                    $campo_historico
                );
                mysqli_query($link, $query_historico);
            }

            $query_de_update = sprintf(
                "UPDATE subitem 
                SET name = '%s', item_id = '%s', unit_type_id = %s, mandatory = '%s', form_field_order = '%s', form_field_name = '%s'
                WHERE id = %d", 
                mysqli_real_escape_string($link,$nome_subitem), 
                mysqli_real_escape_string($link,$id_do_item), 
                mysqli_real_escape_string($link,$id_da_unidade), 
                mysqli_real_escape_string($link,$obrigatorio_valor), 
                mysqli_real_escape_string($link,$form_field_order), 
                mysqli_real_escape_string($link,$nome_do_camp), 
                mysqli_real_escape_string($link,$id));
            if (!mysqli_query($link, $query_de_update)) {
                echo "Erro ao atualizar o subitem: " . mysqli_error($link)."<br>";
                gerar_link_voltar();
                exit;
            }
            
        
            mysqli_commit($link);
            if(mysqli_commit($link)){
                echo "<h3>Atualizações realizadas com sucesso!</h3>";
                echo "<a id='InsertOrSimilar' href='" . get_site_url() . "/gestao-de-subitens/'>Continuar</a><br>";

                exit;
            }
        } catch (Exception $exception) {
            mysqli_rollback($link);
            throw $exception;
        }

        exit;
    }

    if ($_GET['estado'] == "apagar" ) {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']); 
        $id = intval($_GET['id']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_estado'])) {
            $id_subitem = intval($_POST['id']);
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                mysqli_begin_transaction($link);
                apagarSubitem($link, $id_subitem);
                mysqli_commit($link);
                echo "<h3><strong><span id='black'>Subitem apagado com sucesso!</span></strong></h3>";
                echo "<a id='InsertOrSimilar' href='". get_site_url() ."/gestao-de-subitens/'>Continuar</a>";
                

            } catch (Exception $exception) {
                mysqli_rollback($link);
                throw $exception;
            }
            echo "<a href='". get_site_url() ."/gestao-de-subitens/'>";
            exit;
        }
            echo "<strong>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</strong><br>";
            echo "<table class = 'custom-table'>";
            echo "<thead><tr>
                    <th>id</th>
                    <th>name</th>
                    <th>item_id</th>
                    <th>value_type</th>
                    <th>form_field_name</th>
                    <th>form_field_type</th>
                    <th>unit_type_id</th>
                    <th>form_field_order</th>
                    <th>mandatory</th>
                    <th>state</th>
                </tr></thead>";

            $query_buscar_subitens = "SELECT subitem.state AS estado, subitem.form_field_order AS ordemsbitens,subitem.form_field_type AS tipo_do_campo,subitem.form_field_name AS nome_do_campo,subitem.value_type AS tipo_valor,subitem.name AS nome_sb,subitem.id AS id_subitem,subitem.mandatory AS obrigação, item_id, unit_type_id, value_type
                                FROM subitem
                                WHERE subitem.id = " . $id;
            $subitens = mysqli_query($link, $query_buscar_subitens);

            while ($sbitens = mysqli_fetch_assoc($subitens)){
                if($sbitens['obrigação']==1){
                    $sbitens['obrigação']='Sim';
                }else{
                    $sbitens['obrigação']='Não';
                }
                echo "<tr>
                    <td>" . $sbitens['id_subitem'] . "</td>
                    <td class='id_color'>" . $sbitens['nome_sb'] . "</td>
                    <td>" . $sbitens['item_id'] . "</td>
                    <td class='id_color'>" . $sbitens['value_type'] . "</td>
                    <td>" . $sbitens['nome_do_campo'] . "</td>
                    <td class='id_color'>" . $sbitens['tipo_do_campo'] . "</td>
                    <td>" . $sbitens['unit_type_id'] . "</td>
                    <td class='id_color'>" . $sbitens['ordemsbitens'] . "</td>
                    <td>" . $sbitens['obrigação'] . "</td>
                    <td class='id_color'>" . $sbitens['estado'] . "</td>
                </tr>";
            }
            echo "</table>";

            echo '<form method="post" action="">
                    <input type="hidden" name="id" value="'.$id.'">
                    <input type="hidden" name="estado" value="'.$estado.'">
                    <button type="submit" id="InsertOrSimilar" name="alterar_estado">Submeter</button>
                </form>';

            gerar_link_voltar();
        
    } else {
        echo "<a href='". get_site_url() ."/gestao-de-subitens/'>";
        exit;
    }
}else if($_GET['tipo']=='unidades'){
    if($_GET['estado']=='historicoapagar'){
        $query_historico_apagados = "SELECT * 
                                    FROM subitem_unit_type_h 
                                    WHERE operacao = 'eliminacao' 
                                    ORDER BY selotemporal ASC";
        $resultado_historico_apagados = mysqli_query($link, $query_historico_apagados);
        if (!$resultado_historico_apagados) {
            echo "Erro: " . mysqli_error($link);
            exit;
        }
        echo "<h3>Histórico de unidades apagadas</h3>";
        echo "<table class='custom-table'>";
        echo "<tr>";
        echo "<thead><th>ID Original</th>
            <th>Operação</th>
            <th>TimeStamp</th>
            <th>Nome</th></thead>";
        echo "</tr>";
        while ($historico_apagados = mysqli_fetch_assoc($resultado_historico_apagados)) {
            echo "<tr>";
            echo "<td>" . test_input($historico_apagados['id_original'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['operacao'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['selotemporal'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['name'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        gerar_link_voltar();
    }
    if (isset($_GET['estado']) && $_GET['estado'] === 'historico') {
        $id_original = intval($_GET['id']);
        
        if (!$id_original) {
            echo "Erro: ID inválido.<br>";
            exit;
        }
    
        $query_original = sprintf("SELECT * FROM subitem_unit_type 
            WHERE id = %d",
            $id_original
        );
        
        $resultado_original = mysqli_query($link, $query_original);
        if (!$resultado_original || mysqli_num_rows($resultado_original) === 0) {
            echo "Erro: Nenhum registro encontrado para o ID especificado.<br>";
            exit;
        }
        $original = mysqli_fetch_assoc($resultado_original);
    
        $query_historico = sprintf("SELECT * FROM subitem_unit_type_h 
                                    WHERE id_original = %d 
                                    ORDER BY selotemporal ASC",
            $id_original
        );
        $resultado_historico = mysqli_query($link, $query_historico);
    
        echo "<h3>Histórico da unidade ".$original['name']."</h3>";
        echo "<table class='custom-table'>";
        echo "<tr><thead>";
        echo "<th>ID Original</th>
                <th>Operação</th>
                <th>Timestamp</th>
                <th>Nome</th>";
        echo "</thead></tr>";
        echo "<tr>";
        echo "<td>" . test_input($original['id']) . "</td>";
        echo "<td class='id_color'></td><td></td>";
        echo "<td class='id_color'>" . test_input($original['name'] ?? '') . "</td>";
        echo "</tr>";
    
        if ($resultado_historico) {
            while ($historico = mysqli_fetch_assoc($resultado_historico)) {
                echo "<tr>";
                echo "<td>" . test_input($historico['id_original'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['operacao'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['selotemporal'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['name'] ?? '') . "</td>";
                echo "</tr>";
            }
        }
    
        echo "</table>";
        gerar_link_voltar();
        exit;
    }
    if ($_GET['estado'] == 'editar') {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']);
        $id = intval($_GET['id']);
        $nome = test_input($_GET['nome']); 
        if (!isset($_POST['unidade_atualizada'])) {
            echo "<form method='POST' action=''>";
            echo "<table class='custom-table'>";
            echo "<thead><tr>
                    <th>id</th>
                    <th>Nome da unidade</th>
                </tr></thead>";
            echo "<tr><td>" . $id . "</td>";
            echo '<td class="id_color"><input type="text" name="nome_unidade" value="' . test_input($nome) . '" ></td></tr>';
            
            echo "</table>";
            echo '<input type="hidden" name="unidade_atualizada" value="">';
            echo '<button type="submit" id="InsertOrSimilar" name="editar_unidade">Submeter</button><br>';
            gerar_link_voltar();
            echo "</form>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_unidade'])) {
        $id = intval($_GET['id']);
        $nome_unidade = test_input($_POST['nome_unidade']);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            if (empty($nome_unidade)) {
                echo "Erro: Nome da unidade não pode estar vazio.<br>";
                gerar_link_voltar(); 
                exit;
            }
            
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $nome_unidade)) {
                echo "Erro: Nome da unidade contém caracteres inválidos.<br>";
                gerar_link_voltar();
                exit; 
            }
            $queryunidades=sprintf("SELECT name
                                    FROM subitem_unit_type
                                    WHERE id=".$id);
            $resultado_unidade=mysqli_query($link,$queryunidades);
            $unidade=mysqli_fetch_assoc($resultado_unidade);
            if($nome_unidade!=$unidade['name']){
                $query_historico = sprintf(
                    "INSERT INTO subitem_unit_type_h (id_original, name, operacao, selotemporal) 
                    VALUES (%d, '%s', 'edicao', NOW())",
                    $id,
                    mysqli_real_escape_string($link, $nome_unidade)
                );
                mysqli_query($link, $query_historico);
            }
            $query_update = sprintf("UPDATE subitem_unit_type 
                            SET name = '%s' 
                            WHERE id = %d", 
                            mysqli_real_escape_string($link,$nome_unidade), $id);
            if (!mysqli_query($link, $query_update)) {
                echo "Erro ao atualizar o item: " . mysqli_error($link) . "<br>";
                gerar_link_voltar();
                exit;
            }
            
            mysqli_commit($link);
            if(mysqli_commit($link)){
                echo "<h3>Atualizações realizadas com sucesso!</h3>";
                echo "<a id='InsertOrSimilar' href='" . get_site_url() . "/gestao-de-unidades/'>Continuar</a><br>";
                exit;
            }
        } catch (Exception $exception) {
            mysqli_rollback($link);
            throw $exception;
        }

        exit;
    }
    if ($_GET['estado'] == "apagar" ) {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']); 
        $id = intval($_GET['id']);
        $nome = test_input($_GET['nome']); 
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_estado'])) {
            $id_item = intval($_POST['id']);
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                mysqli_begin_transaction($link);
                deletarSubitemUnitType($link, $id_item);
                mysqli_commit($link);
                echo "<h3><strong><span id='black'>Item apagado com sucesso!</span></strong></h3>";
                echo "<a id='InsertOrSimilar' href='". get_site_url() ."/gestao-de-unidades/'>Continuar</a>";
                

            } catch (Exception $exception) {
                mysqli_rollback($link);
                throw $exception;
            }
            echo "<a href='". get_site_url() ."/gestao-de-unidades/'>";
            exit;
        }
            echo "<strong>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?<br>";
            echo "<table class = 'custom-table'>";
            echo "<thead><tr>
                    <th>id</th>
                    <th>nome da unidade</th>
                </tr></thead>";
            echo "<tr>
                <td>".$id."</td>
                <td class='id_color'>".$nome."</td>
            </tr>";
            
            echo "</table>";

            echo '<form method="post" action="">
                    <input type="hidden" name="id" value="'.$id.'">
                    <input type="hidden" name="estado" value="'.$estado.'">
                    <button type="submit" id="InsertOrSimilar" name="alterar_estado">Submeter</button>
                </form>';

            gerar_link_voltar();
        
    } else {
        echo "<a href='". get_site_url() ."/gestao-de-unidades/'>";
        exit;
    }

    
}else if($_GET['tipo']=='valorespermitidos'){
    if($_GET['estado']=='historicoapagar'){
        $query_historico_apagados = "SELECT * 
                                    FROM subitem_allowed_value_h 
                                    WHERE operacao = 'eliminacao' 
                                    ORDER BY selotemporal ASC";
        $resultado_historico_apagados = mysqli_query($link, $query_historico_apagados);
        if (!$resultado_historico_apagados) {
            echo "Erro: " . mysqli_error($link);
            exit;
        }
        echo "<h3>Histórico de valores permitidos apagados</h3>";
        echo "<table class='custom-table'>";
        echo "<tr>";
        echo "<thead><th>ID Original</th>
            <th>Operação</th>
            <th>TimeStamp</th>
            <th>Subitem ID</th>
            <th>Value</th>
            <th>State</th></thead>";
        echo "</tr>";
        while ($historico_apagados = mysqli_fetch_assoc($resultado_historico_apagados)) {
            echo "<tr>";
            echo "<td>" . test_input($historico_apagados['id_original'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['operacao'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['selotemporal'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['subitem_id'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['value'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['state'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        gerar_link_voltar();
    }
    if ($_GET['estado'] == 'historico'){
        $id_original = intval($_GET['allowed_id']);
    $query_original = sprintf("SELECT * FROM subitem_allowed_value  
        WHERE id = %d",
        $id_original
    );

    $resultado_original = mysqli_query($link, $query_original);
    if (!$resultado_original || mysqli_num_rows($resultado_original) === 0) {
        echo "Erro: Nenhum registro encontrado para o ID especificado.<br>";
        exit;
    }
    $original = mysqli_fetch_assoc($resultado_original);

    $query_historico = sprintf("SELECT * 
                                FROM subitem_allowed_value_h 
                                WHERE id_original = %d 
                                ORDER BY selotemporal ASC",
        $id_original
    );
    $resultado_historico = mysqli_query($link, $query_historico);

    echo "<h3>Histórico do valor permitido ".$original['value']."</h3>";
    echo "<table class='custom-table'>";
    echo "<tr><thead>";
    echo "<th>ID Original</th>
            <th>Operação</th>
            <th>Timestamp</th>
            <th>Subitem ID</th>
            <th>Value</th>
            <th>State</th>";
    echo "</thead></tr>";

    echo "<tr>";
    echo "<td>" . test_input($original['id']) . "</td>";
    echo "<td class='id_color'></td><td></td>";
    echo "<td class='id_color'>" . test_input($original['subitem_id'] ?? '') . "</td>";
    echo "<td>" . test_input($original['value'] ?? '') . "</td>";
    echo "<td class='id_color'>" . test_input($original['state'] ?? '') . "</td>"; 
    echo "</tr>";

    if ($resultado_historico) {
        while ($historico = mysqli_fetch_assoc($resultado_historico)) {
            echo "<tr>";
            echo "<td>" . test_input($historico['id_original'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico['operacao'] ?? '') . "</td>";
            echo "<td>" . test_input($historico['selotemporal'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico['subitem_id'] ?? '') . "</td>";
            echo "<td>" . test_input($historico['value'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico['state'] ?? '') . "</td>";
            echo "</tr>";
        }
    }

    echo "</table>";
    gerar_link_voltar();
    exit;
    }
    if ($_GET['estado'] == 'editar') {
        $estado = $_GET['estado'];
        $id_item = intval($_GET['id_item']);
        $id_sub = intval($_GET['id_sub']);
        $nome_sb = test_input($_GET['sub_nome']);
        $permitido_id=test_input($_GET['allowed_id']);
        if (!isset($_POST['valorperm_atualizado'])) {
            $query_buscar_valores_permitidos = "SELECT item.name AS nome_item,  subitem.name AS nome_subitem, subitem_allowed_value.id AS id_valores_perm, subitem_allowed_value.value AS valor_perm, subitem_allowed_value.state AS estado_perm
                                FROM item, subitem,subitem_allowed_value
                                WHERE item.id = $id_item
                                AND subitem.id=$id_sub
                                AND subitem_allowed_value.id=$permitido_id";
            $valores_permitidos = mysqli_query($link, $query_buscar_valores_permitidos);

            echo "<form method='POST' action=''>";
            echo "<table class='custom-table'>";
            echo "<thead><tr>
                    <th>id nome</th>
                    <th>nome do Item</th>
                    <th>id subitem</th>
                    <th>nome subitem</th>
                    <th>valor</th>
                    <th>estado</th>
                </tr></thead>";
            $query_nome_subitens="SELECT DISTINCT name
                                FROM subitem";
            $nome_subitens = mysqli_query($link, $query_nome_subitens);                    
            while ($valores = mysqli_fetch_assoc($valores_permitidos)) {
                echo "<tr>";
                echo "<td>".$id_item."</td>";
                echo "<td class='id_color'>".$valores['nome_item'] . "</td>";
                echo "<td>".$id_sub."</td>";
                echo "<td class='id_color'><select name='nome_subitem'>";
                echo '<option value="' . $nome_sb . '" selected>' . $nome_sb . '</option>';
                while ($nome_sbitem = mysqli_fetch_assoc($nome_subitens)) {
                    if($nome_sbitem['name']!=$nome_sb){
                        echo '<option value="' . $nome_sbitem['name'] . '">' . $nome_sbitem['name'] . '</option>';
                    }
                }
                
                echo"</select></td>";
                echo '<td><input type="text" name="valor_perm" value="' . $valores['valor_perm'] . '" ></td>';
                echo "<td class='id_color'>".$valores['estado_perm'] . "</td>";
                

            }

            echo "</table>";
            echo '<input type="hidden" name="valorperm_atualizado" value="">';
            echo '<button type="submit" id="InsertOrSimilar" name="valorperm_atualizado">Submeter</button><br>';
            gerar_link_voltar();
            echo "</form>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['valorperm_atualizado'])) {
        $nome_subitems = test_input($_POST['nome_subitem']);
        $valor_perm = test_input($_POST['valor_perm']);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            if (empty($valor_perm)) {
                echo "Erro: Valor não pode estar vazio.<br>";
                gerar_link_voltar(); 
                exit;
            }
            
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $valor_perm)) {
                echo "Erro: Valor contém caracteres inválidos.<br>";
                gerar_link_voltar();
                exit; 
            }

            if (empty($nome_subitems)) {
                echo "Erro: Nome do subitem não pode estar vazio.<br>";
                gerar_link_voltar(); 
                exit;
            }
            
            $query_valores_antigos = "SELECT subitem_allowed_value.value, subitem.name,subitem.id AS sb_id,subitem_allowed_value.id AS v_id
                                        FROM subitem
                                        JOIN subitem_allowed_value ON subitem_allowed_value.subitem_id = subitem.id
                                        WHERE subitem_allowed_value.id = " . intval($permitido_id);
            $valores_antigos=mysqli_query($link,$query_valores_antigos);
            $valores_ant=mysqli_fetch_assoc($valores_antigos);
            if($nome_subitems==$valores_ant['name']){
                $nomesb_novo='NULL';
            }else{
                $nomesb_novo=$valores_ant['sb_id'];
                
            }
            if($valor_perm==$valores_ant['value']){
                $valor_perm_novo='NULL';
            }else{
                $valor_perm_novo = "'" . mysqli_real_escape_string($link, $valores_ant['value']) . "'";
            }
            if($nome_subitems!=$valores_ant['name']||$valor_perm!=$valores_ant['value']){
                $query_historico = sprintf(
                    "INSERT INTO subitem_allowed_value_h (id_original, subitem_id, value, operacao, selotemporal, state) 
                    VALUES (%d, %s, %s, 'edicao', NOW(), NULL)",
                    $permitido_id,
                    $nomesb_novo,
                    $valor_perm_novo);
                mysqli_query($link, $query_historico);
            }
            $permitido_id = intval($_GET['allowed_id']);
            $query_sb_id="SELECT id
                            FROM subitem
                            WHERE subitem.name='$nome_subitems'";
            $sub_id=mysqli_query($link, $query_sb_id);
            $sb=mysqli_fetch_assoc($sub_id);
            $query_update = sprintf("UPDATE subitem_allowed_value 
                SET subitem_allowed_value.value = '%s', subitem_allowed_value.subitem_id = %d
                WHERE subitem_allowed_value.id = %d",
                mysqli_real_escape_string($link, $valor_perm), 
                $sb['id'],   
                $permitido_id);
            mysqli_query($link, $query_update);
            mysqli_commit($link);
            if(mysqli_commit($link)){
                echo "<h3>Atualizações realizadas com sucesso!</h3>";
                echo "<a id='InsertOrSimilar' href='" . get_site_url() . "/gestao-de-valores-permitidos/'>Continuar</a><br>";
                
                exit;
            }
        } catch (Exception $exception) {
            mysqli_rollback($link);
            throw $exception;
        }

        exit;
    }
    if (($_GET['estado']=='ativar'||$_GET['estado']=='desativar')) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_estado'])) {
            $permitido_id = test_input($_GET['allowed_id']);
            if ($_POST['estado'] == 'ativar') {
                $novo_estado = 'active';
                $estado = 'desativacao';
            } else {
                $novo_estado = 'inactive';
                $estado = 'ativacao';
            }
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                mysqli_begin_transaction($link);
                $query_historico = sprintf("INSERT INTO subitem_allowed_value_h (id_original, operacao, selotemporal, state) 
                VALUES (%d, '%s', NOW(), '%s')",
                $permitido_id,
                mysqli_real_escape_string($link, $estado),
                mysqli_real_escape_string($link, $novo_estado)
            );
            mysqli_query($link, $query_historico);

            $query_atualizar_estado_ativar = sprintf("UPDATE `subitem_allowed_value` 
                SET `state` = '%s' 
                WHERE `id` = '%s'",
                mysqli_real_escape_string($link, $novo_estado),
                mysqli_real_escape_string($link, $permitido_id));

            if (mysqli_query($link, $query_atualizar_estado_ativar)) {
                mysqli_commit($link);
                echo "<h3>Atualização realizada com sucesso!</h3><br>";
                echo "<a id='InsertOrSimilar' href='" . get_site_url() . "/gestao-de-valores-permitidos/'>Continuar</a>";
            } else {
                throw new Exception("Erro ao atualizar: " . mysqli_error($link));
            }
            } catch (Exception $e) {
                mysqli_rollback($link);
                echo "Erro: " . $e->getMessage();
            }
            exit;
        }
        $id_item = intval($_GET['id_item']);
        $id_sub = intval($_GET['id_sub']);
        $nome_sb = test_input($_GET['sub_nome']);
        $permitido_id=test_input($_GET['allowed_id']);
        $estado = $_GET['estado'];
        $query_buscar_valores_permitidos = "SELECT item.name AS nome_item,  subitem.name AS nome_subitem, subitem_allowed_value.id AS id_valores_perm, subitem_allowed_value.value AS valor_perm, subitem_allowed_value.state AS estado_perm
                                FROM item, subitem,subitem_allowed_value
                                WHERE item.id = $id_item
                                AND subitem.id=$id_sub
                                AND subitem_allowed_value.id=$permitido_id";
        $valores_permitidos = mysqli_query($link, $query_buscar_valores_permitidos);
        if($_GET['estado'] == 'ativar'){
            echo "<strong>Pretende ativar o item?</strong>";
        }else{
            echo "<strong>Pretende desativar o item?</strong>";
        }
        
        echo "<table class='custom-table'>";
        echo "<thead><tr>
                    <th>id nome</th>
                    <th>nome do Item</th>
                    <th>id subitem</th>
                    <th>nome subitem</th>
                    <th>valor</th>
                    <th>estado</th>
                </tr></thead>";
        
        while ($valores_perm=mysqli_fetch_assoc($valores_permitidos)){
            echo "<td>".$id_item."</td>";
            echo "<td class='id_color'>".$valores_perm['nome_item']."</td>";
            echo "<td>".$id_sub."</td>";
            echo "<td class='id_color'>".$valores_perm['nome_subitem']."</td>";
            echo "<td>".$valores_perm['valor_perm']."</td>";
            echo "<td class='id_color'>".$valores_perm['estado_perm']."</td>";
        }

                
        echo"</table>";
        echo '<form method="post" action="">
                <input type="hidden" name="id" value="'.$permitido_id.'">
                <input type="hidden" name="estado" value="'.$estado.'">
                <button type="submit" id="InsertOrSimilar" name="alterar_estado">Submeter</button>
            </form>';
        gerar_link_voltar();
    }
    if ($_GET['estado'] == "apagar" ) {
        $estado = $_GET['estado'];
        $id_item = intval($_GET['id_item']);
        $id_sub = intval($_GET['id_sub']);
        $nome_sb = test_input($_GET['sub_nome']);
        $permitido_id=test_input($_GET['allowed_id']); 
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_estado'])) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                mysqli_begin_transaction($link);

                apagarSubitemAllowedValue($link, $permitido_id);
                mysqli_commit($link);
                echo "<p><h3><span id='black'>Valor permitido apagado com sucesso!</span></h3></p>";
                echo "<a id='InsertOrSimilar' href='". get_site_url() ."/gestao-de-valores-permitidos/'>Continuar</a>";
                

            } catch (Exception $exception) {
                mysqli_rollback($link);
                throw $exception;
            }
            echo "<a href='". get_site_url() ."/gestao-de-valores-permitidos/'>";
            exit;
        }
            echo "<strong>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</strong><br>";
            echo "<table class='custom-table'>";
            echo "<thead><tr>
                    <th>id nome</th>
                    <th>nome do Item</th>
                    <th>id subitem</th>
                    <th>nome subitem</th>
                    <th>valor</th>
                    <th>estado</th>
                </tr></thead>";
            $query_buscar_valores_permitidos = "SELECT item.name AS nome_item,  subitem.name AS nome_subitem, subitem_allowed_value.id AS id_valores_perm, subitem_allowed_value.value AS valor_perm, subitem_allowed_value.state AS estado_perm
                FROM item, subitem,subitem_allowed_value
                WHERE item.id = $id_item
                AND subitem.id=$id_sub
                AND subitem_allowed_value.id=$permitido_id";
            $valores_permitidos = mysqli_query($link, $query_buscar_valores_permitidos);
            while ($valores_perm=mysqli_fetch_assoc($valores_permitidos)){
                echo "<td>".$id_item."</td>";
                echo "<td class='id_color'>".$valores_perm['nome_item']."</td>";
                echo "<td>".$id_sub."</td>";
                echo "<td class='id_color'>".$valores_perm['nome_subitem']."</td>";
                echo "<td>".$valores_perm['valor_perm']."</td>";
                echo "<td class='id_color'>".$valores_perm['estado_perm']."</td>";
            }
            
            echo "</table>";

            echo '<form method="post" action="">
                    <input type="hidden" name="id" value="'.$permitido_id.'">
                    <input type="hidden" name="estado" value="'.$estado.'">
                    <button type="submit" id="InsertOrSimilar" name="alterar_estado">Submeter</button>
                </form>';

            gerar_link_voltar();
        
    } else {
        echo "<a href='". get_site_url() ."/gestao-de-valores-permitidos/'>";
        exit;
    }
}else if($_GET['tipo']=='registosacao'){
    if($_GET ['estado']=='historicoapagar'){
        $query_historico_apagados = "SELECT * 
                                    FROM child_h 
                                    WHERE operacao = 'eliminacao' 
                                    ORDER BY selotemporal ASC";
        $resultado_historico_apagados = mysqli_query($link, $query_historico_apagados);
        if (!$resultado_historico_apagados) {
            echo "Erro: " . mysqli_error($link);
            exit;
        }
        echo "<h3>Histórico de registos de ação apagados</h3>";
        echo "<table class='custom-table'>";
        echo "<tr>";
        echo "<thead><th>ID Original</th>
            <th>Operação</th>
            <th>TimeStamp</th>
            <th>Birth date</th>
            <th>Tutor name</th>
            <th>Tutor phone</th>
            <th>Tutor email</th></thead>";
        echo "</tr>";
        while ($historico_apagados = mysqli_fetch_assoc($resultado_historico_apagados)) {
            echo "<tr>";
            echo "<td>" . test_input($historico_apagados['id_original'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['operacao'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['selotemporal'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['birth_date'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['tutor_name'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['tutor_phone'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['tutor_email'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        gerar_link_voltar();
    }
    if ($_GET['estado'] == 'historico') {
        $id_original = intval($_GET['id']);

        $query_original = sprintf("SELECT * 
                                FROM child 
                                WHERE id = %d",
            $id_original
        );
        $resultado_original = mysqli_query($link, $query_original);
        if (!$resultado_original || mysqli_num_rows($resultado_original) === 0) {
            echo "Erro: Nenhum registro encontrado para o ID especificado.<br>";
            exit;
        }
        $original = mysqli_fetch_assoc($resultado_original);

        $query_historico = sprintf("SELECT * FROM child_h 
                                    WHERE id_original = %d 
                                    ORDER BY selotemporal ASC",
            $id_original
        );
        $resultado_historico = mysqli_query($link, $query_historico);

        echo "<h3>Histórico da criança ".$original['name']."</h3>";
        echo "<table class='custom-table'>";
        echo "<tr><thead>";
        echo "<th>ID Original</th>
                <th>Operação</th>
                <th>Timestamp</th>
                <th>Nome</th>
                <th>Data de Nascimento</th>
                <th>Nome do Tutor</th>
                <th>Telefone do Tutor</th>
                <th>Email do Tutor</th>";
        echo "</thead></tr>";

        echo "<tr>";
        echo "<td>" . test_input($original['id']) . "</td>";
        echo "<td class='id_color'></td><td></td>"; 
        echo "<td class='id_color'>" . test_input($original['name'] ?? '') . "</td>";
        echo "<td>" . test_input($original['birth_date'] ?? '') . "</td>";
        echo "<td class='id_color'>" . test_input($original['tutor_name'] ?? '') . "</td>";
        echo "<td>" . test_input($original['tutor_phone'] ?? '') . "</td>";
        echo "<td class='id_color'>" . test_input($original['tutor_email'] ?? '') . "</td>";
        echo "</tr>";

        if ($resultado_historico) {
            while ($historico = mysqli_fetch_assoc($resultado_historico)) {
                echo "<tr>";
                echo "<td>" . test_input($historico['id_original'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['operacao'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['selotemporal'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['name'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['birth_date'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['tutor_name'] ?? '') . "</td>";
                echo "<td>" . test_input($historico['tutor_phone'] ?? '') . "</td>";
                echo "<td class='id_color'>" . test_input($historico['tutor_email'] ?? '') . "</td>";
                echo "</tr>";
            }
        }

        echo "</table>";
        gerar_link_voltar();
        exit;
    }
    if ($_GET['estado'] == 'editar') {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']);
        $id = intval($_GET['id']);
        if (!isset($_POST['registo_atualizado'])) {
            $query_buscar_value = "SELECT child.id AS id_crianca, name,birth_date,tutor_name,tutor_phone, tutor_email,  name
                                FROM child
                                WHERE child.id=$id";
            $valor = mysqli_query($link, $query_buscar_value);

            echo "<form method='POST' action=''>";
            echo "<table class = 'custom-table'>";
            echo "<thead><tr>
                    <th>id</th>
                    <th>name</th>
                    <th>birth date</th>
                    <th>tutor name</th>
                    <th>tutor phone</th>
                    <th>tutor email</th>
                </tr></thead>";

            while ($value = mysqli_fetch_assoc($valor)) {
                echo "<tr>";
                echo "<td>" . $value['id_crianca'] . "</td>";
                echo '<td class="id_color"><input type="text" name="name_crianca" value="' . test_input($value['name']) . '" ></td>';
                echo '<td><input type="text" name="birth_date" value="' . test_input($value['birth_date']) . '" ></td>';
                echo '<td class="id_color"><input type="text" name="nome_tutor" value="' . test_input($value['tutor_name']) . '" ></td>';
                echo '<td><input type="text" name="tutor_phone" value="' . test_input($value['tutor_phone']) . '" ></td>';
                echo '<td class="id_color"><input type="text" name="tutor_email" value="' . test_input($value['tutor_email']) . '" ></td>';
            }
            echo "</table>";
            echo '<input type="hidden" name="registo_atualizado" value="">';
            echo '<button type="submit" id="InsertOrSimilar" name="editar_registo">Submeter</button><br>';
            gerar_link_voltar();
            echo "</form>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_registo'])) {
        $id = intval($_GET['id']);
        $name_crianca = test_input($_POST['name_crianca']);
        $birth_date = test_input($_POST['birth_date']);
        $nome_tutor = test_input($_POST['nome_tutor']);
        $tutor_phone = test_input($_POST['tutor_phone']);
        $tutor_email = test_input($_POST['tutor_email']);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            if (empty($name_crianca)) {
                echo "Erro: Nome da criança não pode estar vazio.<br>";
                gerar_link_voltar(); 
                exit;
            }
            
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $name_crianca)) {
                echo "Erro: Nome da criança contém caracteres inválidos.<br>";
                gerar_link_voltar();
                exit; 
            }
            if (empty($birth_date)) {
                echo "Erro: Data de aniversario não pode estar vazio.<br>";
                gerar_link_voltar(); 
                exit;
            }
            
            if (!preg_match("/^(19|20)\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/", $birth_date)) {
                echo "Erro: Data de aniversario contém caracteres inválidos.<br>";
                gerar_link_voltar();
                exit; 
            }
            if (empty($nome_tutor)) {
                echo "Erro: Nome do tutor não pode estar vazio.<br>";
                gerar_link_voltar(); 
                exit;
            }
            
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $nome_tutor)) {
                echo "Erro: Nome do tutor  contém caracteres inválidos.<br>";
                gerar_link_voltar();
                exit; 
            }
            if (empty($tutor_phone)) {
                echo "Erro: Nº de telefone não pode estar vazio.<br>";
                gerar_link_voltar(); 
                exit;
            }
            
            if (!preg_match("/^\+?[0-9]{1,3}?\s?[0-9]{3,14}$/", $tutor_phone)) {
                echo "Erro: Nº de telefone contém caracteres inválidos.<br>";
                gerar_link_voltar();
                exit; 
            }else if(!preg_match("/^\d{9}$/",$tutor_phone)){
                echo "Erro: Nº de telefone deve conter exatamente 9 dígitos.<br>";
                gerar_link_voltar();
                exit;
            }
            if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $tutor_email)&&!empty($tutor_email)) {
                echo "Erro: Email não contém o formato certo.<br>";
                gerar_link_voltar();
                exit; 
            }
            $query_child_id = "SELECT *
                                FROM child 
                                WHERE id = '$id'";


            $resultado_dados_child_antigos = mysqli_query($link, $query_child_id);
            $dados_antigos = mysqli_fetch_assoc($resultado_dados_child_antigos);
            $dados_antigo_data = $dados_antigos['birth_date'];
            $dados_antigo_nome = $dados_antigos['name'];
            $dados_antigo_tutor_name = $dados_antigos['tutor_name'];
            $dados_antigo_tutor_phone = $dados_antigos['tutor_phone'];
            $dados_antigo_tutor_email = $dados_antigos['tutor_email'];
            if ($dados_antigo_data != $birth_date) {
                $dados_novos_data = "'" . mysqli_real_escape_string($link, $birth_date) . "'";
            } else {
                $dados_novos_data = "NULL";
            }

            if ($dados_antigo_nome != $name_crianca) {
                $dados_novos_nome = "'" . mysqli_real_escape_string($link, $name_crianca) . "'";
            } else {
                $dados_novos_nome = "NULL";
            }

            if ($dados_antigo_tutor_name != $nome_tutor) {
                $dados_novo_tutor_name = "'" . mysqli_real_escape_string($link, $nome_tutor) . "'";
            } else {
                $dados_novo_tutor_name = "NULL";
            }
            if ($dados_antigo_tutor_phone != $tutor_phone) {
                $dados_novos_tutor_phone = "'" . mysqli_real_escape_string($link, $tutor_phone) . "'";
            } else {
                $dados_novos_tutor_phone = "NULL";
            }
            if ($dados_antigo_tutor_email != $tutor_email) {
                $dados_novos_tutor_email = "'" . mysqli_real_escape_string($link, $tutor_email) . "'";
            } else {
                $dados_novos_tutor_email = "NULL";
            }

            if ($dados_antigo_data != $birth_date || $dados_antigo_nome != $name_crianca || $dados_antigo_tutor_name != $nome_tutor || $dados_antigo_tutor_phone != $tutor_phone || $dados_antigo_tutor_email != $tutor_email) {
                $query_historico = sprintf(
                    "INSERT INTO child_h (id_original, name, birth_date, tutor_name, tutor_phone, tutor_email, operacao, selotemporal) 
                    VALUES (%d, %s, %s, %s, %s, %s, 'edicao', NOW())",
                    $id,
                    $dados_novos_nome,
                    $dados_novos_data,
                    $dados_novo_tutor_name,
                    $dados_novos_tutor_phone,
                    $dados_novos_tutor_email
                );
                mysqli_query($link, $query_historico);
            }
            $query_update = sprintf("UPDATE child 
                SET name = '%s', birth_date = '%s', tutor_name = '%s', tutor_phone = '%s', tutor_email = '%s' 
                WHERE id = '%s'",
                mysqli_real_escape_string($link, $name_crianca),
                mysqli_real_escape_string($link, $birth_date),
                mysqli_real_escape_string($link, $nome_tutor),
                mysqli_real_escape_string($link, $tutor_phone),
                mysqli_real_escape_string($link, $tutor_email),
                mysqli_real_escape_string($link, $id)
            );

            if (!mysqli_query($link, $query_update)) {
                echo "Erro ao atualizar o registo: " . mysqli_error($link)."<br>";
                gerar_link_voltar();
                exit;
            }
            
            mysqli_commit($link);
            if(mysqli_commit($link)){
                echo "<h3>Atualizações realizadas com sucesso!</h3>";
                echo "<a id='InsertOrSimilar' href='" . get_site_url() . "/gestao-de-registos/'>Continuar</a><br>";
                gerar_link_voltar();
                exit;
            }
        } catch (Exception $exception) {
            mysqli_rollback($link);
            throw $exception;
        }

        exit;
    }
    if ($_GET['estado'] == "apagar" ) {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']); 
        $id = intval($_GET['id']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_estado'])) {
            $id = intval($_POST['id']);
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                mysqli_begin_transaction($link);
                apagarChild($link, $id);
                mysqli_commit($link);
                echo "<p><h3><span id='black'>Registo apagado com sucesso!</span></h3></p>";
                echo "<a id='InsertOrSimilar' href='". get_site_url() ."/gestao-de-registos/'>Continuar</a>";
                

            } catch (Exception $exception) {
                mysqli_rollback($link);
                throw $exception;
            }
            echo "<a href='". get_site_url() ."/gestao-de-registos/'>";
            exit;
        }
            echo "<strong>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?<br>";
            echo "<table class='custom-table'>";
            if (!isset($_POST['registo_atualizado'])) {
                $query_buscar_value = "SELECT child.id AS id_crianca, name,birth_date,tutor_name,tutor_phone, tutor_email,  name
                                    FROM child
                                    WHERE child.id=$id";
                $valor = mysqli_query($link, $query_buscar_value);
    
   
                echo "<thead><tr>
                        <th>id</th>
                        <th>name</th>
                        <th>birth date</th>
                        <th>tutor name</th>
                        <th>tutor phone</th>
                        <th>tutor email</th>
                    </tr></thead>";
    
                while ($value = mysqli_fetch_assoc($valor)) {
                    echo "<tr>";
                    echo "<td>" . $value['id_crianca'] . "</td>";
                    echo '<td class="id_color">' . test_input($value['name']) . '</td>';
                    echo '<td>' . test_input($value['birth_date']) . '</td>';
                    echo '<td class="id_color">' . test_input($value['tutor_name']) . '</td>';
                    echo '<td>' . test_input($value['tutor_phone']) . '</td>';
                    echo '<td class="id_color">' . test_input($value['tutor_email']) . '</td>';
                    echo "</tr>";
                }
                
            }
            echo "</table>";

            echo '<form method="post" action="">
                    <input type="hidden" name="id" value="'.$id.'">
                    <input type="hidden" name="estado" value="'.$estado.'">
                    <button type="submit" id="InsertOrSimilar" name="alterar_estado">Submeter</button>
                </form>';

            gerar_link_voltar();
        
    } else {
        echo "<a href='". get_site_url() ."/gestao-de-registos/'>";
        exit;
    }

}else if($_GET['tipo']=='registos'){
    if($_GET['estado']=='historicoapagar'){
        $query_historico_apagados = "SELECT * 
                                    FROM value_h 
                                    WHERE operacao = 'eliminacao' 
                                    ORDER BY selotemporal ASC";
        $resultado_historico_apagados = mysqli_query($link, $query_historico_apagados);
        if (!$resultado_historico_apagados) {
            echo "Erro: " . mysqli_error($link);
            exit;
        }
        echo "<h3>Histórico de registos apagados</h3>";
        echo "<table class='custom-table'>";
        echo "<tr>";
        echo "<thead><th>ID Original</th>
            <th>Operação</th>
            <th>Timestamp</th>
            <th>value</th>
            <th>subitem id</th>
            <th>child id</th>
            <th>date</th>
            <th>time</th>
            <th>producer</th></thead>";
        echo "</tr>";
        while ($historico_apagados = mysqli_fetch_assoc($resultado_historico_apagados)) {
            echo "<tr>";
            echo "<td>" . test_input($historico_apagados['id_original'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['operacao'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['selotemporal'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['value'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['subitem_id'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['child_id'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['date'] ?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_apagados['time'] ?? '') . "</td>";
            echo "<td>" . test_input($historico_apagados['producer'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        gerar_link_voltar();
    }
    if($_GET['estado'] == 'historico'){
        $valor_ids = [];
        for ($i = 1; isset($_GET['value' . $i]); $i++) {
            $valor_ids[] = $_GET['value' . $i];  
        }
        echo "<h3>Histórico de Registos</h3>";
        echo "<table class='custom-table'>";
        echo "<thead><tr>
                <th>ID Original</th>
                <th>Operação</th>
                <th>Timestamp</th>
                <th>value</th>
                <th>subitem id</th>
                <th>child id</th>
                <th>date</th>
                <th>time</th>
                <th>producer</th>
        </tr></thead>";
        foreach($valor_ids as $index){
            $query_historico = sprintf("SELECT * 
                        FROM value_h 
                        WHERE value_h.id_original=%s
                        ORDER BY selotemporal ASC", 
                            $index);   
        $historico = mysqli_query($link, $query_historico);
        if (!$historico) {
            echo "Erro ao executar a consulta: " . mysqli_error($link);
            exit;
        }

        $query_original = "SELECT * 
                            FROM value 
                            WHERE id =".$index;
        $resultado_original = mysqli_query($link, $query_original);
        if ($resultado_original && mysqli_num_rows($resultado_original) > 0) {
            $original = mysqli_fetch_assoc($resultado_original);
            echo "<tr>";
            echo "<td>" . test_input($original['id']) . "</td>";
            echo "<td class='id_color'></td><td></td>"; 
            echo "<td class='id_color'>" . test_input($original['value']) . "</td>";
            echo "<td>" . test_input($original['subitem_id']) . "</td>";
            echo "<td class='id_color'>" . test_input($original['child_id']) . "</td>";
            echo "<td>" . test_input($original['date']) . "</td>";
            echo "<td class='id_color'>" . test_input($original['time']) . "</td>";
            echo "<td>" . test_input($original['producer']) . "</td>";
            echo "</tr>";
        }
        while ($historico_registos = mysqli_fetch_assoc($historico)) {
            echo "<tr>";
            echo "<td>" . test_input($historico_registos['id_original']?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_registos['operacao']?? '') . "</td>";
            echo "<td>" . test_input($historico_registos['selotemporal']?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_registos['value']?? '') . "</td>";
            echo "<td>" . test_input($historico_registos['subitem_id']?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_registos['child_id']?? '') . "</td>";
            echo "<td>" . test_input($historico_registos['date']?? '') . "</td>";
            echo "<td class='id_color'>" . test_input($historico_registos['time']?? '') . "</td>";
            echo "<td>" . test_input($historico_registos['producer']?? '') . "</td>";
            echo "</tr>";
        }
    }
        echo "</table>";

        gerar_link_voltar();
    
        exit;
    }
    

    if ($_GET['estado'] == 'editar') {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']);
        $contador=1;
        if (!isset($_POST['registo_atualizado'])) {
            echo "<form method='POST' action=''>";
            echo "<table class='custom-table'>";
            echo "<thead><tr>
                <th>id valor</th>
                <th>valor</th>
                <th>subitem id</th>
                <th>nome subitem</th>
                <th>form_field_type</th>
                <th>value type</th>
                <th>date</th>
                <th>time</th>
                <th>producer</th>
            </tr></thead>";
        while (isset($_GET['value' . $contador])) {
            $value_param = $_GET['value' . $contador];
            $contador++;
            $query_buscar_value = "SELECT value.value AS value_value, subitem.id AS subitem_id, subitem.name AS subitem_name, value.date AS value_date, value.time AS value_time, value.producer AS value_producer,subitem.form_field_type AS form_field_type, subitem.value_type AS vtype
                        FROM value
                        JOIN subitem ON value.subitem_id = subitem.id
                        WHERE value.id = ".$value_param;
            $valor = mysqli_query($link, $query_buscar_value);
            while ($value = mysqli_fetch_assoc($valor)) {
                echo "<tr>";
                echo "<td>" . $value_param . "</td>";
                $query_sub = "SELECT value 
                                    FROM subitem_allowed_value 
                                    WHERE subitem_id = " . $value['subitem_id'];
                        $subitem = mysqli_query($link, $query_sub);
                        $allowed_values = [];
                        while ($sb = mysqli_fetch_assoc($subitem)) {
                            $allowed_values[] = test_input($sb['value']);
                        }
                        
                        if ($value['form_field_type'] == 'radio') {
                            echo "<td class='id_color'>";
                            $radio_nome = "value_type" . $value['subitem_id'];
                            foreach ($allowed_values as $allowed_value) {
                                if (test_input($allowed_value) == test_input($value['value_value'])) {
                                    echo "<input type='radio' name='$radio_nome' value='$allowed_value' checked> $allowed_value<br>";
                                }else{
                                    echo "<input type='radio' name='$radio_nome' value='$allowed_value' > $allowed_value<br>";
                                }
                            }
                            echo "</td>";
                        } elseif ($value['form_field_type'] == 'text') {
                            echo '<td class="id_color"><input type="text" name="value_text_' . $value_param . '" value="' . test_input($value['value_value']) . '"></td>';
                        } elseif ($value['form_field_type'] == 'selectbox') {
                            echo '<td class="id_color"><select name="value_select_' . $value_param . '">';
                            foreach ($allowed_values as $allowed_value) {
                                if (test_input($allowed_value) == test_input($value['value_value'])) {
                                    echo "<option value='$allowed_value' selected>$allowed_value</option>";
                                }else{
                                    echo "<option value='$allowed_value'>$allowed_value</option>";
                                }
                            }
                            echo "</select></td>";
                        } elseif ($value['form_field_type'] == 'checkbox') {
                            echo "<td class='id_color'>";
                            $checkbox_nome = "value_checkbox_" . $value['subitem_id'] . "[]";
                            $selected_values = explode(', ', test_input($value['value_value']));
                            foreach ($allowed_values as $allowed_value) {
                                $limpar_array = test_input($allowed_value);
                                if (in_array($limpar_array, $selected_values)) {
                                    echo "<input type='checkbox' name='$checkbox_nome' value='$limpar_array' checked> $limpar_array<br>";
                                } else {
                                    echo "<input type='checkbox' name='$checkbox_nome' value='$limpar_array'> $limpar_array<br>";
                                }
                            }
                            echo "</td>";
                        }
                echo "<td>" . $value['subitem_id'] . "</td>";
                echo "<td class='id_color'>" . $value['subitem_name'] . "</td>";
                echo "<td>" . $value['form_field_type'] . "</td>";
                echo "<td class='id_color'>" . $value['vtype'] . "</td>";
                echo "<td>" . $value['value_date'] . "</td>";
                echo "<td class='id_color'>" . $value['value_time'] . "</td>";
                echo "<td>" . $value['value_producer'] . "</td>";
                echo "</tr>";
                }
            }
            echo "</table>";
            echo '<input type="hidden" name="registo_atualizado" value="">';
            echo '<button type="submit" id="InsertOrSimilar" name="editar_registo">Submeter</button><br>';
            gerar_link_voltar();
            echo "</form>";
        }
    
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_registo'])) {
        $contador = 1;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($link, MYSQLI_TRANS_START_READ_WRITE);
        try {
            while (isset($_GET['value' . $contador])) {
                $value_param = $_GET['value' . $contador];
                $contador++;
                $query_buscar_value = "SELECT subitem.id AS subitem_id, subitem.name AS nome_sb, subitem.form_field_type AS form_field_type,subitem.value_type AS vtype,value.value AS value_value
                                    FROM value
                                    JOIN subitem ON value.subitem_id = subitem.id
                                    WHERE value.id = " . $value_param;
                $valor = mysqli_query($link, $query_buscar_value);
                $value = mysqli_fetch_assoc($valor);
                $radio_nome = "value_type" . $value['subitem_id'];
                switch ($value['form_field_type']) {
                    case 'radio':
                        $novo_valor = test_input($_POST[$radio_nome]);
                        break;
    
                    case 'text':
                        $input_value = test_input($_POST['value_text_' . $value_param]);
    
                        if (empty($input_value)) {
                            echo "Erro: O campo ".$value['nome_sb']." não pode estar vazio.<br>";
                            gerar_link_voltar();
                            exit;
                        }
    
                        if ($value['vtype'] == 'int' && !preg_match("/^-?\d+$/", $input_value)) {
                            echo "Erro: O campo ".$value['nome_sb']." deve conter apenas números inteiros.<br>";
                            gerar_link_voltar();
                            exit;
                        }

                        if ($value['vtype'] == 'text' && !preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $input_value)) {
                            echo"Erro: O campo ".$value['nome_sb']." contém caracteres inválidos.<br>";
                            gerar_link_voltar();
                            exit;
                        }
    
                        $novo_valor = $input_value;
                        break;
    
                    case 'selectbox':
                        $novo_valor = test_input($_POST['value_select_' . $value_param]);
                        break;

                    case 'checkbox':
                        if (isset($_POST['value_checkbox_' . $value['subitem_id']])) {
                            $novo_valor = implode(', ', $_POST['value_checkbox_' . $value['subitem_id']]);
                        } 
                        break;
    
                    default:
                        throw new Exception("Erro: Tipo de campo inválido.");
                } 
                
                if ($novo_valor != $value['value_value']) {
                    $query_historico = sprintf(
                        "INSERT INTO value_h (child_id, id_original,  value, operacao, selotemporal) 
                        VALUES (NULL, %d,  '%s', 'edicao', NOW())",
                        $value_param,
                        mysqli_real_escape_string($link, $value['value_value'])
                    );
                    mysqli_query($link, $query_historico);
                }
                

                $query_update = sprintf("UPDATE value 
                                        SET value = '%s' 
                                        WHERE id = '%s'",
                    mysqli_real_escape_string($link, $novo_valor),
                    mysqli_real_escape_string($link, $value_param)
                );
                if (!mysqli_query($link, $query_update)) {
                    echo "Erro ao atualizar o valor: " . mysqli_error($link) . "<br>";
                    gerar_link_voltar();
                    exit;
                }
            }

            mysqli_commit($link);
            echo "<h3>Atualizações realizadas com sucesso!</h3>";
            echo "<a id='InsertOrSimilar' href='" . get_site_url() . "/gestao-de-registos/'>Continuar</a><br>";
            gerar_link_voltar();
            exit;
        } catch (Exception $exception) {
            mysqli_rollback($link);
            throw $exception;
        }
    }   
    if ($_GET['estado'] == 'apagar') {
        $estado = $_GET['estado'];
        $tipo = test_input($_GET['tipo']);
        $contador=1;
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_estado'])) {
            $ids_valores = [];
            while (isset($_GET['value' . $contador])) {
                $ids_valores[] = intval($_GET['value' . $contador]);
                $contador++;
            }
            if (!empty($ids_valores)) {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                try {
                    mysqli_begin_transaction($link);
                    $query_historico = sprintf("INSERT INTO value_h (child_id, id_original, value, operacao, selotemporal, producer,date,time,subitem_id)
                                                SELECT child_id, id, value, 'eliminacao', NOW(),producer,date,time,subitem_id
                                                FROM value
                                                WHERE id IN (%s)",
                        implode(', ', $ids_valores)
                    );
                    mysqli_query($link, $query_historico);
                    foreach ($ids_valores as $id) {
                        $query_apagar_value = sprintf("DELETE FROM value
                                                        WHERE id = %d", $id);
                        mysqli_query($link, $query_apagar_value);
                    }
                    mysqli_commit($link);
                    echo "<h3><strong><span id='black'>Registos apagados com sucesso!</span></strong></h3>";
                    echo "<a id='InsertOrSimilar' href='" . get_site_url() . "/gestao-de-registos/'>Continuar</a>";
                    
            } catch (Exception $exception) {
                mysqli_rollback($link);
                throw $exception;
            }
                echo "<a href='". get_site_url() ."/gestao-de-registos/'>";
                exit;
            }
        }
        if (!isset($_POST['registo_atualizado'])) {
            echo "<strong>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</strong><br>";
            echo "<form method='POST' action=''>";
            echo "<table class='custom-table'>";
            echo "<thead><tr>
                <th>id valor</th>
                <th>valor</th>
                <th>subitem id</th>
                <th>nome subitem</th>
                <th>form_field_type</th>
                <th>value type</th>
                <th>date</th>
                <th>time</th>
                <th>producer</th>
                
            </tr></thead>";
        while (isset($_GET['value' . $contador])) {
            $value_param = $_GET['value' . $contador];
            $contador++;
            $query_buscar_value = "SELECT value.value AS value_value, subitem.id AS subitem_id, subitem.name AS subitem_name, value.date AS value_date, value.time AS value_time, value.producer AS value_producer,subitem.form_field_type AS form_field_type, subitem.value_type AS vtype
                        FROM value
                        JOIN subitem ON value.subitem_id = subitem.id
                        WHERE value.id = ".$value_param;
            $valor = mysqli_query($link, $query_buscar_value);
            while ($value = mysqli_fetch_assoc($valor)) {
                echo "<tr>";
                echo "<td>" . $value_param . "</td>";
                echo "<td class='id_color'>" . $value['value_value'] . "</td>";
                echo "<td>" . $value['subitem_id'] . "</td>";
                echo "<td class='id_color'>" . $value['subitem_name'] . "</td>";
                echo "<td>" . $value['form_field_type'] . "</td>";
                echo "<td class='id_color'>" . $value['vtype'] . "</td>";
                echo "<td>" . $value['value_date'] . "</td>";
                echo "<td class='id_color'>" . $value['value_time'] . "</td>";
                echo "<td>" . $value['value_producer'] . "</td>";
                echo "</tr>";
                }
            }
            echo "</table>";
            echo '<form method="post" action="">
                    <input type="hidden" name="estado" value="'.$estado.'">
                    <button type="submit" id="InsertOrSimilar" name="alterar_estado">Submeter</button>
                </form>';

            gerar_link_voltar();
        
        } else {
            echo "<a href='". get_site_url() ."/gestao-de-itens/'>";
            exit;
        }
    }
}
mysqli_close($link);
?>