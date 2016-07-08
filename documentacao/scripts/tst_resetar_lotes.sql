-- resetar lotes

DELETE FROM msstone_app.lot_transport_cost
where id > 0;

DELETE FROM msstone_app.travel_plan_item
where id > 0;

DELETE FROM msstone_app.travel_plan
where id > 0;

DELETE FROM msstone_app.lot_transport_item
where id > 0;

update msstone_app.block_history set current_lot_transport_id = null
where id > 0;

update msstone_app.block set current_lot_transport_id = null
where id > 0;

DELETE FROM msstone_app.lot_transport
where id > 0;

