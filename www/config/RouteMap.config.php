<?php

class RouteMap_Config extends \Sys\RouteMap
{
    function Register()
    {
        if (!LOGGED)
        {
            parent::$routes = array(
                '/' => array('Login', 'enter_action'),
                '/login/' => array('Login', 'enter_action'),
                '/login/enter/' => array('Login', 'enter_action'),
                '/login/enter/json/' => array('Login', 'enter_json'),
            );
        }
        else
        {
            parent::$routes = array(

                //'/' => array('Home', 'index_action'),
                '/' => array('Poblo', 'list_action'),

                '/logout/' => array('Login', 'logout_action'),

                '/user/list/' => array('User', 'list_action', 'user'),
                '/user/list/json/' => array('User', 'list_json'),
                '/user/detail/json/' => array('User', 'detail_json', 'user'),
                '/user/save/' => array('User', 'save_json', 'user'),
                '/user/permissions/save/' => array('User', 'save_permissions_json', 'user'),
                '/user/delete/' => array('User', 'delete_json', 'user'),

                '/quarry/list/' => array('Quarry', 'list_action', 'quarry'),
                '/quarry/list/json/' => array('Quarry', 'list_json'),
                '/quarry/detail/json/' => array('Quarry', 'detail_json'),
                '/quarry/save/' => array('Quarry', 'save_json', 'quarry'),
                '/quarry/delete/' => array('Quarry', 'delete_json', 'quarry'),
                '/quarry/nextval/final/' => array('Quarry', 'next_val_final_json'),
                '/quarry/nextval/interim/' => array('Quarry', 'next_val_interim_json'),

                '/product/list/' => array('Product', 'list_action', 'product'),
                '/product/list/json/' => array('Product', 'list_json'),
                '/product/detail/json/' => array('Product', 'detail_json'),
                '/product/save/' => array('Product', 'save_json', 'product'),
                '/product/delete/' => array('Product', 'delete_json', 'product'),

                '/quality/list/' => array('Quality', 'list_action', 'quality'),
                '/quality/list/json/' => array('Quality', 'list_json'),
                '/quality/detail/json/' => array('Quality', 'detail_json'),
                '/quality/save/' => array('Quality', 'save_json', 'quality'),
                '/quality/delete/' => array('Quality', 'delete_json', 'quality'),
                '/quality/change_order/' => array('Quality', 'change_order_json', 'quality'),

                '/defect/list/' => array('Defect', 'list_action', 'defect'),
                '/defect/list/json/' => array('Defect', 'list_json'),
                '/defect/detail/json/' => array('Defect', 'detail_json'),
                '/defect/save/' => array('Defect', 'save_json', 'defect'),
                '/defect/delete/' => array('Defect', 'delete_json', 'defect'),

                '/client/list/' => array('Client', 'list_action', 'client'),
                '/client/list/json/' => array('Client', 'list_json'),
                '/client/list_head_office/json/' => array('Client', 'list_head_office_json'),
                '/client/list_without_lot/json/' => array('Client', 'list_without_lot_json'),
                
                '/client/detail/json/' => array('Client', 'detail_json'),
                '/client/save/' => array('Client', 'save_json', 'client'),
                '/client/delete/' => array('Client', 'delete_json', 'client'),

                '/client_group/list/' => array('ClientGroup', 'list_action', 'client'),
                '/client_group/list/json/' => array('ClientGroup', 'list_json'),
                '/client_group/detail/json/' => array('ClientGroup', 'detail_json'),
                '/client_group/save/' => array('ClientGroup', 'save_json', 'client'),
                '/client_group/delete/' => array('ClientGroup', 'delete_json', 'client'),

                '/terminal/list/' => array('Terminal', 'list_action', 'terminal'),
                '/terminal/list/json/' => array('Terminal', 'list_json'),
                '/terminal/detail/json/' => array('Terminal', 'detail_json'),
                '/terminal/save/' => array('Terminal', 'save_json', 'terminal'),
                '/terminal/delete/' => array('Terminal', 'delete_json', 'terminal'),

                '/agency/list/' => array('Agency', 'list_action', 'agency'),
                '/agency/list/json/' => array('Agency', 'list_json'),
                '/agency/detail/json/' => array('Agency', 'detail_json'),
                '/agency/save/' => array('Agency', 'save_json', 'agency'),
                '/agency/delete/' => array('Agency', 'delete_json', 'agency'),

                '/po/list/' => array('ProductionOrder', 'list_action', 'production_order'),
                '/po/list/json/' => array('ProductionOrder', 'list_json'),
                '/po/detail/json/' => array('ProductionOrder', 'detail_json'),
                '/po/save/' => array('ProductionOrder', 'save_json', 'production_order'),
                '/po/delete/' => array('ProductionOrder', 'delete_json', 'production_order'),

                '/po/items/' => array('ProductionOrder', 'items_action', 'production_order'),
                '/po/items/header/json/' => array('ProductionOrderItem', 'header_json'),
                '/po/items/blocks/json/' => array('ProductionOrderItem', 'blocks_json'),
                '/po/items/save/' => array('ProductionOrderItem', 'save_json', 'production_order'),

                '/block/list/' => array('Block', 'list_action', 'block'),
                '/block/list/json/' => array('Block', 'list_json'),
                '/block/detail/json/' => array('Block', 'detail_json'),
                '/block/exists/json/' => array('Block', 'exists_json'),
                '/block/save/' => array('Block', 'save_json', 'block_change'),
                '/block/delete/' => array('Block', 'delete_json', 'block_change'),
                '/block/clients/reservations/json/' => array('Block', 'list_clients_with_reservations_json'),
                '/block/without_lot/' => array('Block', 'list_blocks_without_lot_json'),
                '/block/with_lot/' => array('Block', 'list_blocks_with_lot_json'),
                '/block/photo/' => array('BlockPhoto', 'show_photo_json'),
                '/block/photo/delete/' => array('BlockPhoto', 'delete_json', 'block_change'),
                '/block/photo/upload/' => array('BlockPhoto', 'upload_photo_json', array('block_change', 'production_order')),

                '/block/reserve/' => array('Block', 'reserve_json', 'sobracolumay'),
                '/block/reserve_selected/' => array('Block', 'reserve_selected_json', 'sobracolumay'),

                '/sobracolumay/list/' => array('Sobracolumay', 'list_action', 'sobracolumay'),
                '/sobracolumay/list/json/' => array('Sobracolumay', 'list_json', 'sobracolumay'),
                '/sobracolumay/excel/' => array('Sobracolumay', 'download_excel', 'sobracolumay'),

                '/schedule_inspection/list/' => array('ScheduleInspection', 'list_action', 'schedule_inspection'),
                '/schedule_inspection/list/json/' => array('ScheduleInspection', 'list_json'),
                '/schedule_inspection/detail/json/' => array('ScheduleInspection', 'detail_json'),
                '/schedule_inspection/save/' => array('ScheduleInspection', 'save_json', 'schedule_inspection'),
                '/schedule_inspection/delete/' => array('ScheduleInspection', 'delete_json', 'schedule_inspection'),

                
                '/blocklist/clients/' => array('BlockList', 'list_client_action'),
                '/blocklist/download/' => array('BlockList', 'download'),
                
                '/inspection/clients/' => array('Inspection', 'list_client_action', 'inspection'),
                '/inspection/blocks/' => array('Inspection', 'list_block_action'),
                '/inspection/blocks/json/' => array('Inspection', 'list_block_json'),
                '/inspection/save/' => array('Inspection', 'save_json', 'inspection'),
                '/inspection/pdf_notification/' => array('Inspection', 'pdf_notification', 'inspection'),
                '/inspection/load_email/' => array('Inspection', 'load_email_notification', 'inspection'),
                

                '/inspection/inspection_notification/' => array('Inspection', 'list_notification_action', 'inspection_notification'),
                '/inspection/inspection_notification/save/' => array('Inspection', 'save_notification_json'),

                '/reinspection/blocks/' => array('Reinspection', 'list_block_action'),
                '/reinspection/blocks/save/' => array('Reinspection', 'save_block_json'),

                '/inspection_certificate/list/' => array('Invoice', 'list_action', 'inspection'),
                '/inspection_certificate/list/json/' => array('Invoice', 'list_json'),
                '/inspection_certificate/detail/' => array('Invoice', 'detail_action'),
                '/inspection_certificate/detail/json/' => array('Invoice', 'detail_json'),
                '/inspection_certificate/detail/blocks/json/' => array('Invoice', 'blocks_json'),
                '/inspection_certificate/clients/json/' => array('Invoice', 'list_clients_json'),
                '/inspection_certificate/delete/' => array('Invoice', 'delete_json'),
                '/inspection_certificate/download/' => array('Invoice', 'download'),
                '/inspection_certificate/download_excel/' => array('Invoice', 'download_excel'),

                '/travel_cost/list/' => array('TravelCost', 'list_action', 'travel_cost'),
                '/travel_cost/list/json/' => array('TravelCost', 'list_json'),
                '/travel_cost/detail/json/' => array('TravelCost', 'detail_json'),
                '/travel_cost/save/' => array('TravelCost', 'save_json', 'travel_cost'),
                '/travel_cost/delete/' => array('TravelCost', 'delete_json', 'travel_cost'),
                
                '/travel_route/list/' => array('TravelRoute', 'list_action', 'travel_route'),
                '/travel_route/list/json/' => array('TravelRoute', 'list_json'),
                '/travel_route/detail/json/' => array('TravelRoute', 'detail_json'),
                '/travel_route/save/' => array('TravelRoute', 'save_json', 'travel_route'),
                '/travel_route/delete/' => array('TravelRoute', 'delete_json', 'travel_route'),
                '/travel_route/list/locations/json/' => array('TravelRoute', 'list_locations_json'),
                '/travel_route/list/locations/start/json/' => array('TravelRoute', 'list_start_json'),

                '/lots/list/' => array('LotTransport', 'list_action', 'lot'),
                '/lots/list/json/' => array('LotTransport', 'list_json'),
                '/lots/detail/' => array('LotTransport', 'detail_action'),
                '/lots/detail/json/' => array('LotTransport', 'detail_json'),
                //'/lots/detail/blocks/json/' => array('LotTransport', 'blocks_json'),
                '/lots/save/' => array('LotTransport', 'save_json', 'lot'),
                '/lots/release/' => array('LotTransport', 'release_json', 'lot'),
                '/lots/change_order/' => array('LotTransport', 'change_order_json', 'lot'),
                '/lots/delete/' => array('LotTransport', 'delete_json', 'lot'),
                '/lots/nextval/lot_number/json/' => array('LotTransport', 'next_val_lot_number_json'),
                '/lots/exists/lot_number/json/' => array('LotTransport', 'exists_lot_number_json'),
                '/lots/dismember/' => array('LotTransport', 'dismember_json'),

                '/lots/client_remove/' => array('LotTransport', 'client_remove_json', 'travel_plan'),
                '/lots/local_market/' => array('LotTransport', 'local_market_json', 'travel_plan'),

                '/travel_plan/template/list/' => array('TravelPlanTemplate', 'list_action', 'travel_plan'),
                '/travel_plan/template/list/json/' => array('TravelPlanTemplate', 'list_json'),
                '/travel_plan/template/detail/json/' => array('TravelPlanTemplate', 'detail_json'),
                '/travel_plan/template/save/' => array('TravelPlanTemplate', 'save_json', 'travel_plan'),
                '/travel_plan/template/delete/' => array('TravelPlanTemplate', 'delete_json', 'travel_plan'),

                '/travel_plan/list/' => array('TravelPlan', 'list_action', 'travel_plan'),
                '/travel_plan/list/json/' => array('TravelPlan', 'list_json'),
                '/travel_plan/save/' => array('TravelPlan', 'save_json', 'travel_plan'),
                '/travel_plan/delete/' => array('TravelPlan', 'delete_json', 'travel_plan'),
                '/travel_plan/import_template/' => array('TravelPlan', 'import_template_json'),

                '/travel_plan/pending/' => array('TravelPlanItem', 'list_pending_action', 'pointing_travel'),
                '/travel_plan/pending/json/' => array('TravelPlanItem', 'list_pending_json'),

                '/travel_plan/pending/client_removed/' => array('TravelPlanItem', 'client_removed_json', 'pointing_travel'),
                '/travel_plan/pending/start_shipping/' => array('TravelPlanItem', 'start_shipping_json', 'pointing_travel'),
                '/travel_plan/pending/mark_completed/' => array('TravelPlanItem', 'mark_completed_json', 'pointing_travel'),

                '/travel_plan/packing_list/save/' => array('PackingList', 'save_json', 'travel_plan'),
                '/travel_plan/packing_list/download/' => array('PackingList', 'download', 'travel_plan'),

                '/travel_plan/packing_list/save/' => array('PackingList', 'save_json', 'travel_plan'),
                '/travel_plan/packing_list/download/' => array('PackingList', 'download', 'travel_plan'),

                '/travel_plan/commercial_invoice/save/' => array('CommercialInvoice', 'save_json', 'travel_plan'),
                '/travel_plan/commercial_invoice/download/' => array('CommercialInvoice', 'download', 'travel_plan'),
                '/travel_plan/commercial_invoice/products/json/' => array('CommercialInvoice', 'list_json', 'travel_plan'),
                '/travel_plan/draft/save/' => array('Draft', 'upload_file_json', 'travel_plan'),
                '/travel_plan/draft/download/' => array('Draft', 'download_file_json', 'travel_plan'),

                
                '/travel_plan/history/' => array('TravelPlanItem', 'list_history_action'),
                '/travel_plan/history/json/' => array('TravelPlanItem', 'list_history_json'),

                '/travel_plan/cost/list/json/' => array('LotTransportCost', 'lot_detail_json'),
                '/travel_plan/cost/save/' => array('LotTransportCost', 'save_json', 'travel_plan'),

                '/poblo/' => array('Poblo', 'list_action', 'poblo'),
                '/poblo/json/' => array('Poblo', 'list_json', 'poblo'),

                '/poblo/obs/json/' => array('Poblo', 'obs_json', 'poblo'),
                '/poblo/save/' => array('Poblo', 'salve_obs_json', 'poblo'),
                '/poblo/save_edit/' => array('Poblo', 'save_edit', 'poblo'),

                '/poblo_status/' => array('PobloStatus', 'list_action', 'poblo_status'),
                '/poblo_status/list/json/' => array('PobloStatus', 'list_json'),
                '/poblo_status/detail/json/' => array('PobloStatus', 'detail_json'),
                '/poblo_status/save/' => array('PobloStatus', 'save_json', 'poblo_status'),
                '/poblo_status/save_color/' => array('PobloStatus', 'save_color_json', 'poblo_status'),
                '/poblo_status/delete/' => array('PobloStatus', 'delete_json', 'poblo_status'),


                '/truck_carrier/list/' => array('Truck_Carrier', 'list_action', 'truck_carrier'),
                '/truck_carrier/list/json/' => array('Truck_Carrier', 'list_json', 'truck_carrier'),
                '/truck_carrier/detail/' => array('Truck_Carrier', 'detail_json'),
                '/truck_carrier/save/' => array('Truck_Carrier', 'save_json', 'truck_carrier'),
                '/truck_carrier/delete/' => array('Truck_Carrier', 'delete_json', 'truck_carrier'),
                '/truck_carrier/list_truck/json/' => array('Truck_Carrier', 'list_truck_json', 'truck_carrier'),
                '/truck_carrier/save_one_truck/' => array('Truck_Carrier', 'save_one_truck', 'truck_carrier'),
            );
        }
    }
}