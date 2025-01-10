<?php
include "common.php";
// Verifica se quem está a tentar acessar "Gestão de valores permitidos" têm  'Manage allowed types'
if (!is_user_logged_in() || !(current_user_can('manage_allowed_values')))
{
    echo "<p>Não tem autorização para aceder a esta página. </p>";
    if (!is_user_logged_in()) 
    {
        echo "Por favor, faça login. ";
    }
    else
    {
        echo "Fale com um administrador para obter permissões";
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['estado']))
{
    if (isset($_GET['id_subitem']) && !empty($_GET['id_subitem'])) 
    {
        if ($_GET['estado'] == "introducao") 
        {
            $_SESSION['subitem_id'] = $_GET['id_subitem'];
            echo "<h3>Gestão de valores permitidos - introdução</h3>";
            echo "<form action='{$current_page}' method='GET'>
                <p style='color: red; font-weight: bold;'>*Obrigatório</p>
                <label for='valor'>Valor: <span style='color: red;'>*</span></label>
                <input type='text' id='valor' name='valor'>
                <input type='hidden' name='estado' value='inserir'>
                <input type='hidden' name='id_subitem' value='{$_SESSION['subitem_id']}'>
                <div class='form-actions'>
                <button id='InsertOrSimilar' type='submit'>Inserir Valor Permitido</button>";
            echo gerar_link_voltar();
            echo "</div></form>";

        }
        else if ($_GET['estado'] == "inserir") 
        {
            if (!isset($_GET['valor']) || empty(test_input($_GET['valor']))) 
            {
                echo "<p><strong>O valor fornecido está vazio</strong></p>";
                echo gerar_link_voltar();
            }
            if (preg_match('/^[a-zA-Z0-9]+$/', $_GET['valor'])) 
            {
                if (strlen($_GET['valor']) > 50) 
                {
                    echo "<strong><p>O valor é muito longo, por favor insira um valor mais curto!</p></strong>";
                    echo gerar_link_voltar();
                }
                else
                {
                    $state = true;
                    $subitem_id = mysqli_real_escape_string($link, $_GET['id_subitem']);
                    $valorPermitidoObtido = mysqli_real_escape_string($link, $_GET['valor']);
                    $inserirvalorPermitido =
                    "INSERT INTO 
                        subitem_allowed_value (subitem_id, value, state)
                    VALUES 
                        ('$subitem_id', '$valorPermitidoObtido', '$state');";

                    if (mysqli_query($link, $inserirvalorPermitido))
                    {
                        echo "<h3>Gestão de valores permitidos - introdução</h3>";
                        echo "<p>Inseriu os dados de novo valor permitido com sucesso.</p>";
                        echo "<form action='$current_page' method='GET'>
                        <button type='submit' id='InsertOrSimilar'>Continuar</button>
                        </form>";
                    } 
                    else 
                    {
                        echo "<p>Erro ao inserir o valor permitido: " . mysqli_error($link) . "</p>";
                        echo gerar_link_voltar();
                    }
                }
            }
            else 
            {
                echo "<p><strong>O valor fornecido não é válido</strong></p>";
                echo gerar_link_voltar();
            }
        } 
        else 
        {
            die("<p>Ocorreu um erro ao processar esta operação.</p>");
            echo gerar_link_voltar();
        }
    } 
    else 
    {
        die("<p>O ID do subitem não foi encontrado ou está vazio.</p>");
        echo gerar_link_voltar();
    }
}
else if(!(isset($_GET['estado'])))
{
    //Query para obter todos os itens em que respeitam as caracteristicas
    $queryNomeItens = 
    "SELECT DISTINCT
        item.name AS nome_item,
        item.id AS id_item
    FROM 
        subitem,
        item
    WHERE 
        subitem.value_type = 'enum'
    AND 
        subitem.item_id = item.id   
    ORDER BY 
        item.name ASC;";    
    $resultadoNomeItens = mysqli_query($link, $queryNomeItens);
    if (!$resultadoNomeItens) 
    {
        die("Erro na consulta: " . mysqli_error($link));
    }

    if (mysqli_num_rows($resultadoNomeItens) != 0) 
    {   
        echo "<table class='custom-table'>
        <thead> 
            <tr> 
                <th><b>Item</b></th> 
                <th><b>Id</b></th> 
                <th><b>Subitem</b></th>
                <th><b>Id</b></th>
                <th><b>Valores Permitidos</b></th>
                <th><b>Estado</b></th>
                <th><b>Ação</b></th>
            </tr>
        </thead>
        <tbody>";

        while ($array_itens = mysqli_fetch_assoc($resultadoNomeItens))
        {
            $id_item = $array_itens['id_item']; 
            $nome_item = $array_itens['nome_item'];

            //Esta query serve para contar todos os subitems que têm enum e relacionado com o id atual do item
            $queryComparacao = 
            "SELECT 
                COUNT(*) AS count
            FROM
                subitem
            LEFT JOIN 
                subitem_allowed_value ON subitem.id = subitem_allowed_value.subitem_id
            WHERE 
                subitem.item_id = $id_item
            AND
                subitem.value_type = 'enum';";
            $resultadoComparacao = mysqli_query($link, $queryComparacao);
            if (!$resultadoComparacao) 
            {
                die("Erro na consulta da query para comparação: " . mysqli_error($link));
            }
            $variavelComparacao = mysqli_fetch_assoc($resultadoComparacao)['count']; // Variavel para os casos de enum 
            if($variavelComparacao != 0)
            {
                echo "<tr><td rowspan='$variavelComparacao'>{$nome_item}</td>";
            }
            //Query que retorna o nome e o id dos subitens que vão estar associados a enum e ao id do item em questão
            $querySubitemDoItem = "
            SELECT 
                subitem.name AS nome_subitem,
                subitem.id AS id_subitem
            FROM 
                subitem
            WHERE
                subitem.item_id = $id_item
            AND
                subitem.value_type = 'enum';";
            $resultadoSubitensNoItem = mysqli_query($link, $querySubitemDoItem);

            if (!$resultadoSubitensNoItem)
            {
                die("Erro na consulta da query para obter os subitens associados ao item: " . mysqli_error($link));
            }

            $first_subitem = true;

            while ($array_subitens = mysqli_fetch_assoc($resultadoSubitensNoItem))
            {
                $id_subitem = $array_subitens['id_subitem']; 
                $nome_subitem = $array_subitens['nome_subitem'];

                if (!$first_subitem)
                {
                    echo "<tr>";
                }
                //query para contar os valores allowed, para regular o rowspan
                $queryCountAllowedValues = 
                "SELECT
                    COUNT(*) AS num_allowed_values
                FROM 
                    subitem_allowed_value
                WHERE 
                    subitem_allowed_value.subitem_id = $id_subitem;";

                $resultadoCountAllowedValues = mysqli_query($link, $queryCountAllowedValues);
                if (!$resultadoCountAllowedValues)
                {
                    die("Erro na consulta da query de da contagem de allowed values: " . mysqli_error($link));
                }
                $allowed_values_count = mysqli_fetch_assoc($resultadoCountAllowedValues)['num_allowed_values'];
                if ($allowed_values_count > 0){
                    echo "<td class='id_color' rowspan='$allowed_values_count'>{$id_subitem}</td>";
                    echo "<td rowspan='$allowed_values_count'><a href='{$current_page}?estado=introducao&id_subitem={$id_subitem}'>[$nome_subitem]<a></td>";
                }
                else
                {
                    echo "<td class='id_color' >{$id_subitem}</td>";
                    echo "<td><a href='{$current_page}?estado=introducao&id_subitem={$id_subitem}'>[$nome_subitem]</a></td>";
                }
                //query para receber os valores da tabela allowed_value para posterior impressão
                $queryValoresPermitidos =
                "SELECT
                    subitem_allowed_value.id AS allowed_id,
                    subitem_allowed_value.value AS allowed_value,
                    subitem_allowed_value.state AS allowed_state,
                    subitem.id AS subitem_id,
                    subitem.name AS subitem_name
                FROM
                    subitem
                LEFT JOIN
                    subitem_allowed_value
                ON
                    subitem.id = subitem_allowed_value.subitem_id
                WHERE
                    subitem.id = $id_subitem
                AND
                    subitem.value_type = 'enum';";
                $resultadoValoresPermitidos = mysqli_query($link, $queryValoresPermitidos);
                if (!$resultadoValoresPermitidos) 
                {
                    die("Erro na consulta de valores permitidos: " . mysqli_error($link));
                }
                $first_allowed_value = true;
                while ($array_valores_permitidos = mysqli_fetch_assoc($resultadoValoresPermitidos)) 
                {
                    $allowed_id = $array_valores_permitidos['allowed_id']; 
                    $allowed_value = $array_valores_permitidos['allowed_value'];
                    $allowed_state = $array_valores_permitidos['allowed_state']; 
                    if (!$first_allowed_value) 
                    {
                        echo "<tr>";
                    }
                    if ($allowed_id === NULL && $allowed_value === NULL && $allowed_state === NULL) 
                    {
                        echo "<td id = 'NoArgumentsColor' colspan='4'>Não há valores permitidos definidos</td></tr>";
                    }
                    else
                    {
                        echo "<td class='id_color' >{$allowed_id}</td>";
                        echo "<td>{$allowed_value}</td>";
                        echo "<td>{$allowed_state}</td>";
                        $linkEditar = '<a href="' . get_site_url() . '/edicao-de-dados?estado=editar&tipo=valorespermitidos&id_sub=' . $id_subitem . '&id_item=' . $id_item .'&sub_nome=' . $nome_subitem .'&allowed_id='.$allowed_id. '">[editar]</a>';
                        if ($allowed_state == 'active')
                        {
                            $linkDesativar = '<a href="' . get_site_url() . '/edicao-de-dados?estado=desativar&tipo=valorespermitidos&id_sub=' . $id_subitem . '&id_item=' . $id_item .'&sub_nome=' . $nome_subitem .'&allowed_id='.$allowed_id. '">[desativar]</a>';
                        }
                        else
                        {
                            $linkAtivar = '<a href="' . get_site_url() . '/edicao-de-dados?estado=desativar&tipo=valorespermitidos&id_sub=' . $id_subitem . '&id_item=' . $id_item .'&sub_nome=' . $nome_subitem .'&allowed_id='.$allowed_id. '">[ativar]</a>';
                        }
                        $linkExcluir = '<a href="' . get_site_url() . '/edicao-de-dados?estado=apagar&tipo=valorespermitidos&id_sub=' . $id_subitem . '&id_item=' . $id_item .'&sub_nome=' . $nome_subitem .'&allowed_id='.$allowed_id. '">[apagar]</a>';
                        $linkHistorico = '<a href="' . get_site_url() . '/edicao-de-dados?estado=historico&tipo=valorespermitidos&id_sub=' . $id_subitem . '&id_item=' . $id_item .'&sub_nome=' . $nome_subitem .'&allowed_id='.$allowed_id. '">[historico]</a>';
                        if ($allowed_state == 'active')
                        {
                            echo "<td>
                                <div>{$linkEditar}</div>
                                <div>{$linkDesativar}</div>
                                <div>{$linkExcluir}</div>
                                <div>{$linkHistorico}</div>
                            </td>
                            </tr>";
                        }
                        else
                        {
                        echo "<td>
                                <div>{$linkEditar}</div>
                                <div>{$linkAtivar}</div>
                                <div>{$linkExcluir}</div>
                                <div>{$linkHistorico}</div>
                            </td>
                            </tr>";
                        }
                    }
                    $first_allowed_value = false;
                }
                $first_subitem = false;
            }
        }
        echo "</tbody></table>";
        echo '<strong><span id="black">Histórico de valores permitidos apagados: </span></strong>';
        echo '<span id="black"><a href="' . get_site_url() . '/edicao-de-dados?estado=historicoapagar&tipo=valorespermitidos">Valores permitidos apagados</a></span>';
    
    }
}
?>