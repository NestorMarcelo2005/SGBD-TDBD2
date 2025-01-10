<?php
function deletarSubitemUnitType($link, $id) {
    $query_historico = sprintf(
        "INSERT INTO subitem_unit_type_h (id_original, name, operacao, selotemporal) 
         SELECT id, name, 'eliminacao', NOW() 
         FROM subitem_unit_type 
         WHERE id = %d",
        $id
    );
    mysqli_query($link, $query_historico);

    $query_historico_value = sprintf(
        "INSERT INTO value_h (id_original, value, date, child_id, time, producer, operacao, selotemporal) 
         SELECT id, value, date, child_id, time, producer, 'eliminacao', NOW() 
         FROM value 
         WHERE subitem_id IN (SELECT id FROM subitem WHERE unit_type_id = %d)",
        $id
    );
    mysqli_query($link, $query_historico_value);

    $query_historico_allowed = sprintf(
        "INSERT INTO subitem_allowed_value_h (id_original, value, state, operacao, selotemporal) 
         SELECT id, value, state, 'eliminacao', NOW() 
         FROM subitem_allowed_value 
         WHERE subitem_id IN (SELECT id FROM subitem WHERE unit_type_id = %d)",
        $id
    );
    mysqli_query($link, $query_historico_allowed);

    $query_historico_subitem = sprintf(
        "INSERT INTO subitem_h (id_original, name, item_id, unit_type_id, mandatory, form_field_order, form_field_name, operacao, selotemporal, value_type, form_field_type, state) 
         SELECT id, name, item_id, unit_type_id, mandatory, form_field_order, form_field_name, 'eliminacao', NOW(), value_type, form_field_type, state 
         FROM subitem 
         WHERE unit_type_id = %d",
        $id
    );
    mysqli_query($link, $query_historico_subitem);

    $query_apagar_allowed = sprintf(
        "DELETE FROM subitem_allowed_value 
         WHERE subitem_id IN (SELECT id FROM subitem WHERE unit_type_id = %d)",
        $id
    );
    mysqli_query($link, $query_apagar_allowed);

    $query_apagar_value = sprintf(
        "DELETE FROM value 
         WHERE subitem_id IN (SELECT id FROM subitem WHERE unit_type_id = %d)",
        $id
    );
    mysqli_query($link, $query_apagar_value);

    $query_apagar_subitem = sprintf(
        "DELETE FROM subitem 
         WHERE unit_type_id = %d",
        $id
    );
    mysqli_query($link, $query_apagar_subitem);

    $query_apagar_tipo_unidade = sprintf(
        "DELETE FROM subitem_unit_type 
         WHERE id = %d",
        $id
    );
    mysqli_query($link, $query_apagar_tipo_unidade);

    mysqli_commit($link);
}
function apagarItem($link, $id_item, $nome_item, $tipo_item, $estado_item) {
    
        $query_historico_item = sprintf(
            "INSERT INTO item_h (id_original, name, item_type_id, state, operacao, selotemporal) 
             VALUES (%d, '%s', %d, '%s', 'eliminacao', NOW())",
            $id_item, $nome_item, $tipo_item, $estado_item
        );
        

        $query_historico_allowed = sprintf(
            "INSERT INTO subitem_allowed_value_h (id_original, value, state, operacao, selotemporal, subitem_id) 
             SELECT id, value, state, 'eliminacao', NOW(), subitem_id 
             FROM subitem_allowed_value 
             WHERE subitem_id IN (SELECT id FROM subitem WHERE item_id = %d)",
            $id_item
        );
        

        $query_historico_value = sprintf(
            "INSERT INTO value_h (id_original, value, date, child_id, time, producer, operacao, selotemporal, subitem_id) 
             SELECT id, value, date, child_id, time, producer, 'eliminacao', NOW(), subitem_id 
             FROM value 
             WHERE subitem_id IN (SELECT id FROM subitem WHERE item_id = %d)",
            $id_item
        );
        

        $query_historico_subitem = sprintf(
            "INSERT INTO subitem_h (id_original, name, value_type, form_field_name, form_field_type, unit_type_id, form_field_order, mandatory, state, operacao, selotemporal) 
             SELECT id, name, value_type, form_field_name, form_field_type, unit_type_id, form_field_order, mandatory, state, 'eliminacao', NOW() 
             FROM subitem 
             WHERE item_id = %d",
            $id_item
        );
        

        $query_apagar_value_item = sprintf(
            "DELETE FROM value 
             WHERE subitem_id IN (SELECT id FROM subitem WHERE item_id = %d)",
            $id_item
        );
        

        $query_apagar_allowed = sprintf(
            "DELETE FROM subitem_allowed_value 
             WHERE subitem_id IN (SELECT id FROM subitem WHERE item_id = %d)",
            $id_item
        );
        

        $query_apagar_subitem = sprintf(
            "DELETE FROM subitem 
             WHERE item_id = %d",
            $id_item
        );
        
        $query_apagar_item = sprintf(
            "DELETE FROM item 
             WHERE id = %d",
            $id_item
        );
        mysqli_query($link, $query_historico_item);
        mysqli_query($link, $query_historico_allowed);
        mysqli_query($link, $query_historico_value);
        mysqli_query($link, $query_historico_subitem);
        mysqli_query($link, $query_apagar_value_item);
        mysqli_query($link, $query_apagar_allowed);
        mysqli_query($link, $query_apagar_subitem);
        mysqli_query($link, $query_apagar_item);
    }
    function apagarSubitem($link, $id_subitem) {
        
        $query_historico = sprintf(
            "INSERT INTO subitem_h (id_original, name, item_id, unit_type_id, mandatory, form_field_order, form_field_name, operacao, selotemporal, value_type, form_field_type, state) 
                SELECT id, name, item_id, unit_type_id, mandatory, form_field_order, form_field_name, 'eliminacao', NOW(), value_type, form_field_type, state
                FROM subitem
                WHERE id = %d",
            $id_subitem
        );
        
        $query_historico_value = sprintf(
            "INSERT INTO value_h (id_original, value, date, child_id, time, producer, operacao, selotemporal) 
                SELECT id, value, date, child_id, time, producer, 'eliminacao', NOW() 
                FROM value 
                WHERE subitem_id = %d",
            $id_subitem
        );
        
        $query_historico_allowed = sprintf(
            "INSERT INTO subitem_allowed_value_h (id_original, value, state, operacao, selotemporal) 
                SELECT id, value, state, 'eliminacao', NOW() 
                FROM subitem_allowed_value 
                WHERE subitem_id = %d",
            $id_subitem
        );
        

        $query_apagar_value_item = sprintf(
            "DELETE FROM value 
                WHERE subitem_id = %d",
            $id_subitem
        );
        
        $query_apagar_allowed = sprintf(
            "DELETE FROM subitem_allowed_value 
                WHERE subitem_id = %d",
            $id_subitem
        );
        
        $query_apagar_subitem = sprintf(
            "DELETE FROM subitem 
                WHERE id = %d",
            $id_subitem
        );
        mysqli_query($link, $query_historico);
        mysqli_query($link, $query_historico_value);
        mysqli_query($link, $query_historico_allowed);
        mysqli_query($link, $query_apagar_allowed);
        mysqli_query($link, $query_apagar_value_item);
        mysqli_query($link, $query_apagar_subitem);
        
    }
    function apagarSubitemAllowedValue($link, $permitido_id) {
        
            $query_historico = sprintf(
                "INSERT INTO subitem_allowed_value_h (id_original, operacao, selotemporal, state, subitem_id, value) 
                 SELECT id, 'eliminacao', NOW(), state, subitem_id, value
                 FROM subitem_allowed_value
                 WHERE id = %d",
                $permitido_id
            );
            
            
            $query_allowed_value = sprintf("DELETE FROM subitem_allowed_value 
                 WHERE id = %d",
                $permitido_id
            );
            $query_value = sprintf("DELETE FROM value 
                 WHERE subitem_id = (SELECT subitem_id FROM subitem_allowed_value WHERE id = %d) 
                 AND value = (SELECT value FROM subitem_allowed_value WHERE id = %d)",
                $permitido_id, $permitido_id
            );
            
            mysqli_query($link, $query_value);
            mysqli_query($link, $query_historico);
            mysqli_query($link, $query_allowed_value);

        }
        function apagarChild($link, $id) {
            
                $query_historico = sprintf(
                    "INSERT INTO child_h (id_original, name, birth_date, tutor_name, tutor_phone, tutor_email, operacao, selotemporal)
                     SELECT id, name, birth_date, tutor_name, tutor_phone, tutor_email, 'eliminacao', NOW()
                     FROM child
                     WHERE id = %d",
                    $id
                );
                
        
                $query_historico_value = sprintf(
                    "INSERT INTO value_h (id_original, value, date, child_id, time, producer, operacao, selotemporal) 
                     SELECT id, value, date, child_id, time, producer, 'eliminacao', NOW() 
                     FROM value 
                     WHERE child_id = %d",
                    $id
                );
                
        
                $query_apagar_value = sprintf(
                    "DELETE FROM value
                     WHERE child_id = %d",
                    $id
                );
                
        
                $query_apagar_child = sprintf(
                    "DELETE FROM child
                     WHERE id = %d",
                    $id
                );
                mysqli_query($link, $query_historico);
                mysqli_query($link, $query_historico_value);
                mysqli_query($link, $query_apagar_value);
                mysqli_query($link, $query_apagar_child);

                
        }
?>