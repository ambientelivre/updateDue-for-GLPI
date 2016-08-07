<?php

/*
 * Atualiza data e hora de vencimento com 5 minutos a cada 5 minutos (execução no cron) 
 * dos tickets
 * Como instalar:
 * - copiar o arquivo cron.php para a pasta raiz do glpi (exemplo: /var/www/glpi)
 * - colocar o comando abaixo no agendador (Linux ou Windows) com intervalos de 5 em 5 minutos:
 * php <yourdirectory>/cron.php
 */

include ('inc/includes.php');

// Protegendo para não executar script via url
$dominio= $_SERVER['HTTP_HOST'];
if (!empty($dominio)) {
    die("Sorry. You can't access directly to this file");
}

global $DB;

// Busca chamados com status pendente (status = 4)
// Data de vencimento (id_search_option = 18)
$query = "SELECT id, due_date, ADDTIME(due_date,'00:05:00') as new_due_date FROM glpi_tickets
where status = '4' and is_deleted = '0'";

if ($result = $DB->query($query)) {
    if ($DB->numrows($result)) {
        while ($data= $DB->fetch_assoc($result)) {
            $query2 = "UPDATE glpi_tickets SET due_date = ADDTIME(due_date,'00:05:00') where id = '".$data['id']."'";
            if ($result2 = $DB->query($query2)) {
                $query3 = "SELECT max(id)+1 as maximo from glpi_logs where itemtype like 'Ticket' and items_id = '".$data['id']."'";
                if ($result3 = $DB->query($query3)) {
                    while ($data2= $DB->fetch_assoc($result3)) {
                        $query4 = "INSERT INTO glpi_logs (id, itemtype, items_id,"
                                . " itemtype_link, linked_action, user_name,"
                                . " date_mod, id_search_option, old_value, new_value)"
                                . " VALUES ('".($data2['maximo'])."', 'Ticket', '".$data['id']."', '', '0', 'cron', now(), '18', '".$data['due_date']."', '".$data['new_due_date']."')";
                        //echo "maximo: ".$data2['maximo'];
                        //echo "query4: ".$query4;
                        
                        $result4 = $DB->query($query4);
                    }
                }                
            }            
        }
    }
 }

?>

