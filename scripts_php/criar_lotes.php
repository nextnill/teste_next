<?php

@set_time_limit(0);

require 'config/System.config.php';
require 'sys/MVC.class.php';
require 'sys/Validation.class.php';
require 'sys/DB.class.php';
require 'sys/Session.class.php';
require 'sys/Util.class.php';
require 'sys/Model.class.php';

use \Sys\DB;
use \Sys\Model;

$model = new Model();

// invoices que podem virar lote
$sql = "SELECT invoice.id, invoice.client_id, client.name, client.code
FROM invoice
INNER JOIN client on client.id = invoice.client_id
WHERE invoice.id IN (    
                         253, 252, 251, 250, 249, 248, 247, 246, 245, 244,
                         242, 241, 239, 238, 236, 235, 234, 233, 232, 230
                    )
and invoice.excluido = 'N'
ORDER BY invoice.id";

/*
                         176, 175, 174, 173, 172, 171, 170, 169, 168, 167,
                         166, 165, 164, 163, 161, 158, 156, 154, 153

                         216, 214, 213, 211, 208, 205, 204, 203, 
                         202, 201, 199, 197, 196, 195, 194, 192, 191, 189,
                         187, 186, 185, 184, 183, 182, 181, 180, 179, 178

                         229, 228, 227, 226, 225, 224, 223, 222, 221, 220,
                         219, 218
                         
                         253, 252, 251, 250, 249, 248, 247, 246, 245, 244,
                         242, 241, 239, 238, 236, 235, 234, 233, 232, 230

                         
*/

$invoices = DB::query($sql);

// obtenho os itens das invoices
foreach ($invoices as $invoice_key => $invoice) {
	
    //echo "Obtendo itens da invoice " . $invoice['id'] . ' - ' . $invoice['code'] . ' - ' . $invoice['name'];
	$invoice_item_model = $model->LoadModel('InvoiceItem', true);
	$invoice['itens'] = json_decode(json_encode($invoice_item_model->get_by_invoice($invoice['id'])));

	//print_r($invoice['itens']);exit;
	$invoice['lot_number'] = 'X-INV/' . str_pad($invoice['id'], 6, "0", STR_PAD_LEFT);
	foreach ($invoice['itens'] as $item_key => $item) {
		$invoice['itens'][$item_key]->invoice_item_id = $item->id;
	}

	//echo " - OK<br>";

	// para cada invoice, criar um lote e adicionar os itens da invoice
	echo "Criando lote " . $invoice['lot_number'] . ' - ' . $invoice['code'] . ' - ' . $invoice['name'];

    $lot_transport_model = $model->LoadModel('LotTransport', true);

    $lot_transport_model->lot_number = $invoice['lot_number'];
    $lot_transport_model->client_id = (int)$invoice['client_id'];
    $lot_transport_model->client_remove = 'true';
    $lot_transport_model->items = $invoice['itens'];

    // salvo o transporte
    $ret = $lot_transport_model->save();
    $lot_transport_model->release('true');

	echo " - OK \n ";
	flush();
	//exit;

    
	// marco que os blocos foram removidos pelo cliente
	$lot_transport_model->populate($lot_transport_model->id);

    $travel_plan_item_model = $model->LoadModel('TravelPlanItem', false);

    foreach ($lot_transport_model->items as $key => $item) {
        
        $travel_plan_item_model = new TravelPlanItem_Model();
        $travel_plan_item_model->client_removed(
            $item['lot_transport_id'],
            $item['id'],
            $item['block_id'],
            $item['invoice_item_id'],
            '000000',
            0.01,
            ''
        );
    }

}
