<?php
include "common.php";
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
if (!is_user_logged_in() || !(current_user_can('values_import'))) {
    echo "<p>Não tem autorização para aceder a esta página. </p>";
    if (!is_user_logged_in()) {
        echo "Por favor, faça login. ";
    } else {
        echo "Fale com um administrador para obter permissões para esta secção";
    }
    exit;
}
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'escolheritem') {
    $id_crianca_selecionada = $_REQUEST['crianca'];
    $queryTiposDeItens = 
    "SELECT 
        DISTINCT item.item_type_id AS id_tipos_de_itens,
        item_type.name AS nome_tipo_item
    FROM 
        item
    JOIN
        item_type ON item.item_type_id = item_type.id;";
    $resultadoTiposDeItens = mysqli_query($link, $queryTiposDeItens);
    if (!$resultadoTiposDeItens) {
        die("Erro na consulta da query para os tipos de Itens" . mysqli_error($link));
    }
    while($array_tipos_de_itens = mysqli_fetch_assoc($resultadoTiposDeItens)) {
        $guarda_id_tipo_item = $array_tipos_de_itens['id_tipos_de_itens'];
        $nome_tipo_item = $array_tipos_de_itens['nome_tipo_item'];
        echo "<h3>&bull; " . str_replace('_', ' ', ucfirst($nome_tipo_item)) . "</h3>";
        $queryNomeItens = "SELECT 
            item.name AS nome_item,
            item.id AS item_id
        FROM 
            item
        WHERE
            item.item_type_id = $guarda_id_tipo_item
        AND
            item.state = 'active';";
        $resultadoNomeItens = mysqli_query($link, $queryNomeItens);
        if (!$resultadoNomeItens) {
            die("Erro na consulta da query para os nomes dos itens" . mysqli_error($link));
        }
        if (mysqli_num_rows($resultadoNomeItens) == 0) {
            echo "<p style='margin-left: 20px;'>Não tem itens associados a este tipo de item</p>";
        } else {
            while($item = mysqli_fetch_assoc($resultadoNomeItens)) {
                echo "<p style='margin-left: 20px;'>&bull; <a href = '{$current_page}?estado=introducao&crianca={$id_crianca_selecionada}&item={$item['item_id']}'> [{$item['nome_item']}] </a></p>";
            }
        }
    }
    echo gerar_link_voltar();
}
else if(isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'introducao')
{
    $id_crianca_selecionada = $_REQUEST['crianca'];
    $id_do_item_selecionado = $_REQUEST['item'];
    
    $queryFormField = 
    "SELECT 
        subitem.form_field_name AS form_field,
        subitem.id AS id_subitem,
        subitem.form_field_order AS form_field_order
    FROM
        subitem
    LEFT JOIN 
        subitem_allowed_value ON subitem_allowed_value.subitem_id = subitem.id
    WHERE
        subitem.item_id = $id_do_item_selecionado
    ORDER BY
        subitem.form_field_order;";
    $resultadoFormField = mysqli_query($link, $queryFormField);
    if (!$resultadoFormField) 
    {
        die("Erro na consulta da query para os tipos de forms fields do Item" . mysqli_error($link));
        
    }
    if (mysqli_num_rows($resultadoFormField) == 0){
        $queryParaConsultarONomeDoItem = 
        "SELECT
            DISTINCT item.name AS nome_item
        FROM 
            item
        WHERE
            item.id = $id_do_item_selecionado;";
        $resultadoParaConsultarONomeDoItem = mysqli_query($link, $queryParaConsultarONomeDoItem);
        if (!$resultadoParaConsultarONomeDoItem) 
        {
            die("Erro na consulta da query para os nomes do Item" . mysqli_error($link));
            
        }
        $array_nome_do_item = mysqli_fetch_assoc($resultadoParaConsultarONomeDoItem);
        echo "<p>O item <strong>{$array_nome_do_item['nome_item']}</strong> não tem subitems associados </p>";
        echo gerar_link_voltar();
        die();
    }
    elseif (mysqli_num_rows($resultadoFormField) != 0)
    {  
    echo "<table class='custom-table'><tbody><tr><thead>";
    while($array_tipos_de_form_fields = mysqli_fetch_assoc($resultadoFormField))
    {
        $tipo_form_field = $array_tipos_de_form_fields['form_field'];
        echo "<th>{$tipo_form_field}</th>";
    }
    echo "</tr></thead><tbody><tr>";
    mysqli_data_seek($resultadoFormField, 0);
    while($array_tipos_de_form_fields = mysqli_fetch_assoc($resultadoFormField))
    {
        $id_subitem = $array_tipos_de_form_fields['id_subitem'];
        echo "<td>{$id_subitem}<br></td>";
    }
    echo "</tr>";
    mysqli_data_seek($resultadoFormField, 0);
    while($array_tipos_de_form_fields = mysqli_fetch_assoc($resultadoFormField))
    {
        $form_field_order = $array_tipos_de_form_fields['form_field_order'];
        $queryParaAExistenciaAllowedValues = 
        "SELECT
            subitem.value_type AS value_type,
            subitem_allowed_value.id as id_value            
        FROM
            subitem
        LEFT JOIN 
            subitem_allowed_value ON subitem_allowed_value.subitem_id = subitem.id
        WHERE
            subitem.form_field_order =  $form_field_order
        AND
            subitem.id = {$array_tipos_de_form_fields['id_subitem']};";
        $resultadoParaAExistenciaAllowedValues = mysqli_query($link, $queryParaAExistenciaAllowedValues);
        if (!$resultadoParaAExistenciaAllowedValues) 
        {
            die("Erro na consulta da query para os  " . mysqli_error($link));
            
        }
        $array_value_types = mysqli_fetch_assoc($resultadoParaAExistenciaAllowedValues);
        if($array_value_types['value_type'] == 'enum')
        {
            if($array_value_types['id_value'] == NULL)
            {
                echo "<td><br></td>";
            }
            else if($array_value_types['id_value'] != NULL)
            {
                $queryParaAExistenciaAllowedValues = 
                "SELECT    
                    subitem_allowed_value.value AS allowed_value
                FROM
                    subitem_allowed_value
                WHERE
                    subitem_allowed_value.id = {$array_value_types['id_value']};";
                $resultadoParaAExistenciaAllowedValues = mysqli_query($link, $queryParaAExistenciaAllowedValues);
                if (!$resultadoParaAExistenciaAllowedValues) 
                {
                    die("Erro na consulta da query para os  " . mysqli_error($link));
                    
                }
                $arrayAllowedValues = mysqli_fetch_assoc($resultadoParaAExistenciaAllowedValues);
                echo "<td>$arrayAllowedValues[allowed_value]<br></td>";
            }
        }
        else
        {
            echo "<td><br></td>";
        }

    }
    echo "</tr></tbody></table>";
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $diretorio_spreadsheet = '/opt/lampp/htdocs/sgbd/import_to_insert.xlsx';
    $diretorio = dirname($diretorio_spreadsheet);
    if (!file_exists($diretorio_spreadsheet)) {
        mkdir($diretorio, 0777, true);
        $writer = new Xlsx($spreadsheet);
        $writer->save($diretorio_spreadsheet);
    }
    echo "<p>Copie estas linhas para um ficheiro Excel e introduza os valores a importar. Para subitens do tipo enum, insira 0 quando o valor permitido não se aplica e 1 quando se aplica.</p>";
    echo "<form action='{$current_page}?estado=validacao' method='POST'>";
    echo "<input type='hidden' name='item' value='{$id_do_item_selecionado}'>";
    echo "<input type='submit' id='InsertOrSimilar' value='Carregar Ficheiro'>";
    echo "</form>";
    echo gerar_link_voltar();
    }
}
else if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'validacao') 
{
    $id_do_item_selecionado = $_REQUEST['item'];
    $diretorio_spreadsheet = '/opt/lampp/htdocs/sgbd/import_to_insert.xlsx';
    if (!file_exists($diretorio_spreadsheet)) 
    {
        echo "O ficheiro Excel não foi encontrado.<br>";
        echo gerar_link_voltar();
        die();
    }

    $spreadsheet = IOFactory::load($diretorio_spreadsheet);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
    $data = [];
    for ($row = 1; $row <= $highestRow; $row++) 
    {
        $rowData = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellValue = $sheet->getCellByColumnAndRow($col, $row)->getValue();
            $rowData[] = $cellValue;
        }
        $data[] = $rowData;
    }
    echo "<table class='custom-table'><thead><tr>";
    foreach ($data[0] as $header) 
    {
        echo "<th>{$header}<br></th>";
    }
    echo "</tr></thead><tbody>";
    for ($i = 1; $i < count($data); $i++) 
    {
        echo "<tr>";
        foreach ($data[$i] as $cell) 
        {
            echo "<td>{$cell}<br></td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "<p><strong>Tem a certeza que quer Inserir a seguinte tabela?</strong></p>";
    echo "<form action='{$current_page}?estado=inserir' method='POST'>";
    echo "<input type='hidden' name='item' value='{$id_do_item_selecionado}'>";
    echo "<input type='submit' id='InsertOrSimilar' value='Avançar e Inserir'>";
    echo "</form>";
    echo gerar_link_voltar();
}
else if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'inserir')
{
    $id_do_item_selecionado = $_REQUEST['item'];
    echo "<form action='$current_page' method='GET'>";
    $query_para_ver_id = 
    "SELECT 
        id AS id
    FROM 
        subitem 
    WHERE 
        subitem.item_id = $id_do_item_selecionado;";
    $resultado_para_ver_id = mysqli_query($link, $query_para_ver_id);
    if (!$resultado_para_ver_id) 
    {
        die("Erro na consulta da query para ver o ID do subitem: " . mysqli_error($link));
    }
    //Parte não completa para implementar a inserção dos valores
    while ($row = mysqli_fetch_assoc($resultado_para_ver_id)) 
    {
        $id_subitem = $row['id'];
    }
    echo "<button type='submit' id='InsertOrSimilar'>Inicio</button>";
    echo gerar_link_voltar();
    echo "</form>";
}
else if (!isset($_REQUEST['estado'])) 
{
    echo "<h3>Importação de Valores - escolher criança</h3>";
    $queryTodasAsCriancas = 
    "SELECT 
        child.name AS nome_da_crianca,
        child.id AS id_crianca,
        child.birth_date AS data_de_nascimento_crianca,
        child.tutor_name AS nome_do_ENC,
        child.tutor_phone AS telemovel_do_ENC,
        child.tutor_email AS email_ENC
    FROM
        child;";
    
    $resultadoTodasAsCriancas = mysqli_query($link, $queryTodasAsCriancas);
    
    if (!$resultadoTodasAsCriancas) 
    {
        die("Erro na consulta da query para todas as crianças: " . mysqli_error($link));
    }

    if (mysqli_num_rows($resultadoTodasAsCriancas) != 0) 
    {
        echo "<table class='custom-table'>
            <thead>
                <tr>
                    <th><b>Nome</b></th>
                    <th><b>Data de Nascimento</b></th>
                    <th><b>Enc. de Educação</b></th>
                    <th><b>Telefone do Enc.</b></th>
                    <th><b>E-mail</b></th>
                </tr>
            </thead>
            <tbody>";
        while($array_criancas = mysqli_fetch_assoc($resultadoTodasAsCriancas)) 
        {
            echo "<tr><td><a href='{$current_page}?estado=escolheritem&crianca={$array_criancas['id_crianca']}'>{$array_criancas['nome_da_crianca']}</a></td>"; //em que o nome da criança é uma ligação para o seguinte endereço: "importacao-de-valores?estado=escolheritem&crianca=c"
            echo "<td>{$array_criancas['data_de_nascimento_crianca']}</td>";
            echo "<td>{$array_criancas['nome_do_ENC']}</td>";
            echo "<td>{$array_criancas['telemovel_do_ENC']}</td>";
            echo "<td>{$array_criancas['email_ENC']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } 
    else if (mysqli_num_rows($resultadoTodasAsCriancas) == 0) 
    {
        echo "Não existe crianças";
    }
}
?>