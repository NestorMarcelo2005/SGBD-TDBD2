<?php
include "common.php";
// Verifica se quem está a tentar acessar "Gestão de Unidades" têm  'Manage unit types' definido previamente
if (!is_user_logged_in() || !(current_user_can('manage_unit_types'))) 
{
    echo "<p>Não tem autorização para aceder a esta página. </p>";
    if (!is_user_logged_in()) 
    {
        echo "Por favor, faça login. ";
    }
    else
    {
        echo "Fale com um administrador para obter permissões para esta secção";
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['estado'])) 
{
    if ($_POST['estado'] == "inserir")
    {
        $nome = test_input($_POST['nome']);
        echo "<h3>Gestão de unidades - introdução</h3>";
        if (preg_match('/^[a-zA-Z0-9]+$/', $nome)) 
        {
            if (strlen($nome) > 50) 
            {
                echo "<strong><p>O nome é muito longo, por favor insira um nome mais curto!</p></strong>";
                gerar_link_voltar();
            } 
            else 
            {
                $nomeAdicionar = mysqli_real_escape_string($link, $nome);
                //Query para inserir o nome que submetido no formulario
                $inserirNome = 
                "INSERT INTO 
                    subitem_unit_type (name) 
                VALUES 
                    ('$nomeAdicionar');";
                $resultadoInserirNomes = mysqli_query($link, $inserirNome);
                if ($resultadoInserirNomes) 
                {
                    echo "<strong><p>Inseriu a unidade: {$nome}</p></strong>";
                    echo "<strong><p>Inseriu os dados de um novo tipo de unidades com sucesso.</p></strong>";
                    echo "<form action='$current_page' method='GET'>
                    <button type='submit' id='InsertOrSimilar'>Continuar</button>
                    </form>";
                } 
                else    
                {
                    echo "<p>Erro ao introduzir dados: " . mysqli_error($link) . "</p>";
                    gerar_link_voltar();
                }
            }
        }
        else if (empty($nome)) 
            {
                echo "<strong><p>O nome está vazio, por favor insira um válido!</p></strong>";
                gerar_link_voltar();
            } 
        else
        {
            echo "<strong><p>O nome não é válido, por favor insira um válido!</p></strong>";
            gerar_link_voltar();
        }
        
    }
    else
    {
        die("Ocorreu um erro ao processar esta operação");
    }
    
}
else if (!isset($_POST['estado']))
{
    //Query para buscar os tipos de Unidades para as tabelas e ordenar caso não estejam
    $queryTiposUnidade = 
    "SELECT
        subitem_unit_type.id AS id_unidade,
        subitem_unit_type.name AS unidade
    FROM
        subitem_unit_type
    ORDER BY
        subitem_unit_type.id;";

    $resultadoTiposUnidade = mysqli_query($link, $queryTiposUnidade);
    if (!$resultadoTiposUnidade)
    {
        die("Erro na consulta da query das Unidades: " . mysqli_error($link));
    }
    else if (mysqli_num_rows($resultadoTiposUnidade) == 0) {
        die("Não há tipos de unidades.");
    }
    $resultadosubitem = [];
    $resultadoItensDesteSubitem = [];
    if (mysqli_num_rows($resultadoTiposUnidade) != 0)
    {
        //Imprime a nossa base para a tabela
        echo "<table class='custom-table'>
        <thead>
            <tr>
                <th><b>Id</b></th>
                <th><b>Unidade</b></th>
                <th><b>Subitens (Items)</b></th>
                <th><b>Ação</b></th>
            </tr>
        </thead>
        <tbody>";
        
        while ($array_de_unidades = mysqli_fetch_assoc($resultadoTiposUnidade))
        {
            //variaveis para utilizar em chamadas para cada while
            $id_unidade = $array_de_unidades['id_unidade'];
            $unidade = $array_de_unidades['unidade'];
            //query que busca as subitens desta unidade em expecifico
            $query_subitem_desta_unidade =
            "SELECT 
                subitem.name AS nome_unidade_subitem,
                subitem.item_id AS id_item_no_subitem
            FROM
                subitem
            WHERE
                subitem.unit_type_id = $id_unidade;";
            $resultadosubitem = mysqli_query($link, $query_subitem_desta_unidade);
            if (!$resultadosubitem)
            {
                die("Erro na consulta da query dos subitens: " . mysqli_error($link));
            }
            echo "<tr><td class='id_color'>{$id_unidade}</td>";
            echo "<td>{$unidade}</td><td class='id_color'>";
            $subitem_count = 0; //counter para que as virgulas não estejam mal posicionadas
            while($array_de_subitens = mysqli_fetch_assoc($resultadosubitem))
            {
                $nome_unidade_subitem = $array_de_subitens['nome_unidade_subitem'];
                $id_item_no_subitem = $array_de_subitens['id_item_no_subitem'];
                //query que vê qual o item que está relacionado com o subitem
                $queryItensDesteSubitem =  
                "SELECT 
                    item.name AS nome_do_item 
                FROM 
                    item 
                WHERE 
                    item.id = $id_item_no_subitem;";
                $resultadoItensDesteSubitem = mysqli_query($link, $queryItensDesteSubitem);
                if (!$resultadoItensDesteSubitem) 
                {
                    die("Erro na consulta da query do item dentro do subitem: " . mysqli_error($link));
                }
                $infoSubitem = mysqli_fetch_assoc($resultadoItensDesteSubitem);
                $nome_do_item = $infoSubitem['nome_do_item'];
                if ($subitem_count != 0)
                {
                    echo ", {$nome_unidade_subitem}({$nome_do_item})";
                }
                else
                {
                    echo "{$nome_unidade_subitem}({$nome_do_item})";
                }
                $subitem_count++;
            }
            echo "</td>";
            $linkEditar = '<a href="' . get_site_url() . '/edicao-de-dados?estado=editar&tipo=unidades&nome=' . $unidade . '&id=' . $id_unidade . '">[editar]</a>';
            $linkApagar = '<a href="' . get_site_url() . '/edicao-de-dados?estado=apagar&tipo=unidades&nome=' . $unidade . '&id=' . $id_unidade . '">[apagar]</a>';
            $linkHistorico = '<a href="' . get_site_url() . '/edicao-de-dados?estado=historico&tipo=unidades&nome=' . $unidade . '&id=' . $id_unidade . '">[historico]</a>';
            echo "<td>{$linkEditar}{$linkApagar}{$linkHistorico}</td></tr>";
        }
        echo "</tbody></table>";
        echo '<strong><span id="black">Histórico de unidades apagadas: </span></strong>';
        echo '<span id="black"><a href="' . get_site_url() . '/edicao-de-dados?estado=historicoapagar&tipo=unidades">Unidades apagadas</a></span>';
        echo "<h3>Gestão de unidades - introdução</h3>";
        echo "<form action='$current_page' method='POST'>
            <label for='nome'>Nome:</label>
            <input type='text' id='nome' name='nome'>
            <input type='hidden' name='estado' value='inserir'>
            <button type='submit' id='InsertOrSimilar'>Submeter</button>
        </form>";
    }
}
else
{
    die("Metodo não é compatível com esta pagina");
}
mysqli_close($link);
?>
