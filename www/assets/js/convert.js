function str_block_type(int_block_type)
{
    var bt = parseInt(int_block_type, 10);

    switch (bt) {
        case BLOCK_TYPE.FINAL:
            return 'Final';
            break;
        case BLOCK_TYPE.INTERIM:
            return 'Interim';
            break;
    }
    
    return '';
}

function str_production_status(value)
{
    switch (parseInt(value, 10)) {
        case PRODUCTION_STATUS.DRAFT:
            return 'Draft'
            break;
        case PRODUCTION_STATUS.CONFIRMED:
            return 'Confirmed'
            break;
    }
}

function str_yes_no(tinyint_value, yes_value)
{
    var value = parseInt(tinyint_value, 10);

    switch (value) {
        case 0:
            return '<span class="label label-default">No</span>';
            break;
        case 1:
            return '<span class="label label-success">' + (yes_value ? yes_value : 'Yes') + '</span>';
            break;
    }
    
    return '';
}

function str_terminal_type(value)
{
    switch (parseInt(value, 10)) {
        case TERMINAL_TYPE.RAIL:
            return 'Rail';
            break;
        case TERMINAL_TYPE.PORT:
            return 'Port';
            break;
        /*
        case TERMINAL_TYPE.PORT_OF_LOADING:
            return 'Port of loading';
            break;
        case TERMINAL_TYPE.PORT_OF_DISCHARGE:
            return 'Port of discharge';
            break;
        */
    }
}

function str_travel_plan_status(value)
{
    switch (parseInt(value, 10)) {
        case TRAVEL_PLAN_STATUS.PENDING:
            return 'Pending'
            break;
        case TRAVEL_PLAN_STATUS.STARTED:
            return 'Started'
            break;
        case TRAVEL_PLAN_STATUS.COMPLETED:
            return 'Completed'
            break;
        default:
            return '';
    }
}

function str_lot_transport_status(value)
{
    switch (parseInt(value, 10)) {
        case LOT_TRANSPORT_STATUS.DRAFT:
            return 'Draft';
            break;
        case LOT_TRANSPORT_STATUS.RELEASED:
            return 'Released';
            break;
        case LOT_TRANSPORT_STATUS.TRAVEL_STARTED:
            return 'Travel Started';
            break;
        case LOT_TRANSPORT_STATUS.DELIVERED:
            return 'Delivered';
            break;
    }
}

function str_lot_travel_cost_type(value)
{
    switch (parseInt(value, 10)) {
        case LOT_TRAVEL_COST_TYPE.FIXED:
            return 'Fixed';
            break;
        case LOT_TRAVEL_COST_TYPE.VARIABLE:
            return 'Variable';
            break;
    }
}